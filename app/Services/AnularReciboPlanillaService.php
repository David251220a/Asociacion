<?php

namespace App\Services;

use App\Models\Aporte;
use App\Models\Planilla;
use App\Models\PlanillaAporte;
use App\Models\PlanillaDetalle;
use App\Models\PlanillaPrestamo;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\PrestamoPago;
use App\Models\Recibo;
use App\Models\ReciboAporte;
use App\Models\ReciboPago;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnularReciboPlanillaService
{
    private const ESTADO_ACTIVO = 1;
    private const ESTADO_ANULADO = 2;

    private const ESTADO_PAGO_PENDIENTE = 1;
    private const ESTADO_PAGO_PAGADO = 2;
    private const ESTADO_PAGO_PARCIAL = 3;
    private const ESTADO_PAGO_ANULADO = 4;
    private const ESTADO_PAGO_ENVIADO_PLANILLA = 5;
    private const ESTADO_PAGO_NO_PAGADO = 6;

    private const ESTADO_PRESTAMO_VIGENTE = 2;
    private const ESTADO_PRESTAMO_CANCELADO = 3;

    private const TIPO_RECIBO_PLANILLA = 4;

    private const TIPO_INGRESO_APORTE_PLANILLA = 4;
    private const TIPO_INGRESO_CAPITAL_PRESTAMO_DEFAULT = 9;
    private const TIPO_INGRESO_INTERES_PRESTAMO = 10;
    private const TIPO_INGRESO_IVA_PRESTAMO = 11;
    private const TIPO_INGRESO_PUNITORIO_PRESTAMO = 12;

    private array $tipoIngresoCapitalPorPrestamo = [];

    /**
     * Anula solamente el recibo generado por el cobro de una planilla.
     *
     * La planilla no se anula: vuelve a quedar pendiente junto con sus
     * aportes y cuotas de préstamos para poder procesarse nuevamente.
     */
    public function anular(
        Recibo $recibo,
        string $motivo,
        int $usuarioId
    ): void {
        DB::transaction(function () use (
            $recibo,
            $motivo,
            $usuarioId
        ) {
            $reciboActual = Recibo::query()
                ->whereKey($recibo->id)
                ->lockForUpdate()
                ->first();

            if (!$reciboActual) {
                throw new \Exception(
                    'No se encontró el recibo que desea anular.'
                );
            }

            if (
                (int) $reciboActual->estado_id
                    === self::ESTADO_ANULADO
                || (int) $reciboActual->anulado === 1
            ) {
                throw new \Exception(
                    'El recibo ya se encuentra anulado.'
                );
            }

            if (
                (int) $reciboActual->tipo_recibo_id
                    !== self::TIPO_RECIBO_PLANILLA
            ) {
                throw new \Exception(
                    'El recibo seleccionado no corresponde a un cobro de planilla.'
                );
            }

            $planillaId = $this->obtenerPlanillaId(
                $reciboActual
            );

            $planilla = Planilla::query()
                ->whereKey($planillaId)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->first();

            if (!$planilla) {
                throw new \Exception(
                    'No se encontró la planilla activa relacionada al recibo.'
                );
            }

            if ((int) $planilla->pagado !== 1) {
                throw new \Exception(
                    'La planilla relacionada ya se encuentra pendiente.'
                );
            }

            $detalles = PlanillaDetalle::query()
                ->where('planilla_id', $planilla->id)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->get();

            if ($detalles->isEmpty()) {
                throw new \Exception(
                    'La planilla no posee detalles activos para restaurar.'
                );
            }

            $detalleIds = $detalles->pluck('id');

            $aportesPlanilla = PlanillaAporte::query()
                ->whereIn('planilla_detalle_id', $detalleIds)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->get();

            $prestamosPlanilla = PlanillaPrestamo::query()
                ->whereIn('planilla_detalle_id', $detalleIds)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $reciboAportes = ReciboAporte::query()
                ->where('recibo_id', $reciboActual->id)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->get();

            $pagosPrestamo = PrestamoPago::query()
                ->where('recibo_id', $reciboActual->id)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $pagosPorPlanillaPrestamo =
                $this->validarPagosPrestamoVinculados(
                    $prestamosPlanilla,
                    $pagosPrestamo
                );

            $this->validarCuotasSinPlanillaPosterior(
                $prestamosPlanilla
            );

            $movimientos = $this->clasificarMovimientos(
                $reciboActual,
                $reciboAportes,
                $pagosPrestamo
            );

            $this->restaurarDetallesPlanilla(
                $detalles,
                $reciboAportes,
                $prestamosPlanilla,
                $pagosPorPlanillaPrestamo,
                $usuarioId
            );

            $this->restaurarAportesPlanilla(
                $aportesPlanilla,
                $detalles,
                $reciboAportes,
                $usuarioId
            );

            $totalesPorPrestamo = [];

            foreach ($prestamosPlanilla as $planillaPrestamo) {
                /** @var PrestamoPago $pago */
                $pago = $pagosPorPlanillaPrestamo->get(
                    (int) $planillaPrestamo->id
                );

                $aplicacion = $this->obtenerAplicacionPago(
                    $pago
                );

                $this->restaurarCuotaOriginal(
                    $planillaPrestamo,
                    $pago,
                    $aplicacion,
                    $usuarioId
                );

                $this->acumularTotalesPrestamo(
                    $totalesPorPrestamo,
                    (int) $planillaPrestamo->prestamo_id,
                    $aplicacion
                );

                $this->restaurarPlanillaPrestamo(
                    $planillaPrestamo,
                    $usuarioId
                );

                $this->anularYRecrearHistoricoPrestamo(
                    $pago,
                    $planillaPrestamo,
                    $reciboActual,
                    $motivo,
                    $usuarioId
                );
            }

            $this->restaurarPrestamosCabecera(
                $totalesPorPrestamo,
                $usuarioId
            );

            $planilla->pagado = 0;
            $planilla->monto_pagado = 0;
            $planilla->fecha_pagado = null;
            $planilla->usuario_modificacion = $usuarioId;
            $planilla->save();

            $this->anularMovimientosDelRecibo(
                $reciboActual,
                $usuarioId
            );

            $this->revertirResumenes(
                $reciboActual,
                $movimientos
            );

            $reciboActual->estado_id = self::ESTADO_ANULADO;
            $reciboActual->anulado = 1;
            $reciboActual->fecha_anulado = now()->toDateString();
            $reciboActual->motivo_anulacion = mb_strtoupper(
                trim($motivo),
                'UTF-8'
            );
            $reciboActual->usuario_anulacion = $usuarioId;
            $reciboActual->save();
        }, 3);
    }

    /**
     * El recibo puede tener aporte, préstamo o ambos. Se resuelve la planilla
     * utilizando las dos relaciones y se exige que todas apunten a una sola.
     */
    private function obtenerPlanillaId(Recibo $recibo): int
    {
        $planillaIds = ReciboAporte::query()
            ->where('recibo_id', $recibo->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->whereNotNull('planilla_id')
            ->pluck('planilla_id');

        $planillasPrestamo = DB::table('prestamo_pagos as ppg')
            ->join(
                'planilla_prestamos as pp',
                'pp.id',
                '=',
                'ppg.planilla_prestamo_id'
            )
            ->join(
                'planilla_detalles as pd',
                'pd.id',
                '=',
                'pp.planilla_detalle_id'
            )
            ->where('ppg.recibo_id', $recibo->id)
            ->where('ppg.estado_id', self::ESTADO_ACTIVO)
            ->pluck('pd.planilla_id');

        $planillaIds = $planillaIds
            ->merge($planillasPrestamo)
            ->filter(function ($id) {
                return (int) $id > 0;
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        if ($planillaIds->isEmpty()) {
            throw new \Exception(
                'No se pudo identificar la planilla relacionada al recibo.'
            );
        }

        if ($planillaIds->count() > 1) {
            throw new \Exception(
                'El recibo se encuentra relacionado con más de una planilla.'
            );
        }

        return (int) $planillaIds->first();
    }

    private function validarPagosPrestamoVinculados(
        Collection $prestamosPlanilla,
        Collection $pagosPrestamo
    ): Collection {
        $duplicados = $pagosPrestamo
            ->groupBy('planilla_prestamo_id')
            ->filter(function (Collection $items) {
                return $items->count() > 1;
            });

        if ($duplicados->isNotEmpty()) {
            throw new \Exception(
                'Existen registros duplicados de pagos de préstamos para el recibo.'
            );
        }

        $pagosPorPlanillaPrestamo = $pagosPrestamo->keyBy(
            'planilla_prestamo_id'
        );

        foreach ($prestamosPlanilla as $planillaPrestamo) {
            if (!$pagosPorPlanillaPrestamo->has(
                (int) $planillaPrestamo->id
            )) {
                throw new \Exception(
                    'La cuota N.º '
                    . $planillaPrestamo->numero_cuota
                    . ' no tiene un pago histórico vinculado al recibo.'
                );
            }
        }

        $idsPrestamosPlanilla = $prestamosPlanilla
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            });

        $pagoAjeno = $pagosPrestamo->first(
            function (PrestamoPago $pago) use (
                $idsPrestamosPlanilla
            ) {
                return !$idsPrestamosPlanilla->contains(
                    (int) $pago->planilla_prestamo_id
                );
            }
        );

        if ($pagoAjeno) {
            throw new \Exception(
                'El recibo contiene un pago de préstamo ajeno a la planilla.'
            );
        }

        return $pagosPorPlanillaPrestamo;
    }

    /**
     * Evita devolver una cuota a esta planilla si posteriormente ya fue
     * incluida en otra planilla activa.
     */
    private function validarCuotasSinPlanillaPosterior(
        Collection $prestamosPlanilla
    ): void {
        foreach ($prestamosPlanilla as $planillaPrestamo) {
            $posterior = DB::table('planilla_prestamos as pp')
                ->join(
                    'planilla_detalles as pd',
                    'pd.id',
                    '=',
                    'pp.planilla_detalle_id'
                )
                ->join(
                    'planillas as pl',
                    'pl.id',
                    '=',
                    'pd.planilla_id'
                )
                ->where(
                    'pp.prestamo_detalle_id',
                    $planillaPrestamo->prestamo_detalle_id
                )
                ->where('pp.id', '>', $planillaPrestamo->id)
                ->where('pp.estado_id', self::ESTADO_ACTIVO)
                ->where('pd.estado_id', self::ESTADO_ACTIVO)
                ->where('pl.estado_id', self::ESTADO_ACTIVO)
                ->whereNotIn('pp.estado_pago_id', [
                    self::ESTADO_PAGO_ANULADO,
                    self::ESTADO_PAGO_NO_PAGADO,
                ])
                ->exists();

            if ($posterior) {
                throw new \Exception(
                    'La cuota N.º '
                    . $planillaPrestamo->numero_cuota
                    . ' ya fue incluida en una planilla posterior. '
                    . 'Debe anular primero el movimiento posterior.'
                );
            }
        }
    }

    private function restaurarDetallesPlanilla(
        Collection $detalles,
        Collection $reciboAportes,
        Collection $prestamosPlanilla,
        Collection $pagosPorPlanillaPrestamo,
        int $usuarioId
    ): void {
        $aportesPorAsociado = $reciboAportes
            ->groupBy('asociado_id')
            ->map(function (Collection $items) {
                return (int) $items->sum('aporte');
            });

        $prestamosPorDetalle = [];

        foreach ($prestamosPlanilla as $planillaPrestamo) {
            /** @var PrestamoPago $pago */
            $pago = $pagosPorPlanillaPrestamo->get(
                (int) $planillaPrestamo->id
            );

            $detalleId = (int) $planillaPrestamo->planilla_detalle_id;

            if (!isset($prestamosPorDetalle[$detalleId])) {
                $prestamosPorDetalle[$detalleId] = 0;
            }

            $prestamosPorDetalle[$detalleId] +=
                (int) $pago->monto_pagado;
        }

        foreach ($detalles as $detalle) {
            $montoRecibo =
                (int) ($aportesPorAsociado[
                    (int) $detalle->asociado_id
                ] ?? 0)
                + (int) ($prestamosPorDetalle[
                    (int) $detalle->id
                ] ?? 0);

            $detalle->pagado = max(
                0,
                (int) $detalle->pagado - $montoRecibo
            );

            $detalle->saldo = max(
                0,
                (int) $detalle->monto_esperado
                - (int) $detalle->pagado
            );

            $detalle->usuario_modificacion = $usuarioId;
            $detalle->save();
        }
    }

    private function restaurarAportesPlanilla(
        Collection $aportesPlanilla,
        Collection $detalles,
        Collection $reciboAportes,
        int $usuarioId
    ): void {
        $detallePorId = $detalles->keyBy('id');

        $aportesPorAsociado = $reciboAportes
            ->groupBy('asociado_id')
            ->map(function (Collection $items) {
                return (int) $items->sum('aporte');
            });

        foreach ($aportesPlanilla as $aportePlanilla) {
            $detalle = $detallePorId->get(
                (int) $aportePlanilla->planilla_detalle_id
            );

            if (!$detalle) {
                throw new \Exception(
                    'No se encontró el detalle del aporte dentro de la planilla.'
                );
            }

            $montoRecibo = (int) ($aportesPorAsociado[
                (int) $detalle->asociado_id
            ] ?? 0);

            $aportePlanilla->monto_pagado = max(
                0,
                (int) $aportePlanilla->monto_pagado
                - $montoRecibo
            );

            $aportePlanilla->saldo = max(
                0,
                (int) $aportePlanilla->monto_esperado
                - (int) $aportePlanilla->monto_pagado
            );

            $aportePlanilla->estado_pago_id =
                self::ESTADO_PAGO_ENVIADO_PLANILLA;

            $aportePlanilla->usuario_modificacion = $usuarioId;
            $aportePlanilla->save();
        }
    }

    private function restaurarCuotaOriginal(
        PlanillaPrestamo $planillaPrestamo,
        PrestamoPago $pago,
        array $aplicacion,
        int $usuarioId
    ): void {
        $cuota = PrestamoDetalle::query()
            ->whereKey($planillaPrestamo->prestamo_detalle_id)
            ->where(
                'prestamo_id',
                $planillaPrestamo->prestamo_id
            )
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->lockForUpdate()
            ->first();

        if (!$cuota) {
            throw new \Exception(
                'No se encontró la cuota original del préstamo ID '
                . $planillaPrestamo->prestamo_id
                . '.'
            );
        }

        $cuota->monto_capital_pagado = max(
            0,
            (int) $cuota->monto_capital_pagado
            - $aplicacion['capital']
        );

        $cuota->monto_interes_pagado = max(
            0,
            (int) $cuota->monto_interes_pagado
            - $aplicacion['interes']
        );

        $cuota->monto_iva_pagado = max(
            0,
            (int) $cuota->monto_iva_pagado
            - $aplicacion['iva']
        );

        $cuota->monto_pagado = max(
            0,
            (int) $cuota->monto_pagado
            - $aplicacion['total']
        );

        $cuota->saldo_capital = min(
            (int) $cuota->monto_capital,
            (int) $cuota->saldo_capital
            + $aplicacion['capital']
        );

        $cuota->saldo_interes = min(
            (int) $cuota->monto_interes,
            (int) $cuota->saldo_interes
            + $aplicacion['interes']
        );

        $cuota->saldo_iva = min(
            (int) $cuota->monto_iva,
            (int) $cuota->saldo_iva
            + $aplicacion['iva']
        );

        /*
         * monto_mora y monto_mora_iva representan el saldo pendiente actual.
         * Al revertir el pago se vuelven a sumar, pero la mora generada por la
         * planilla no se elimina porque la planilla continuará activa.
         */
        $cuota->monto_mora = max(
            0,
            (int) $cuota->monto_mora
            + $aplicacion['mora']
        );

        $cuota->monto_mora_iva = max(
            0,
            (int) $cuota->monto_mora_iva
            + $aplicacion['mora_iva']
        );

        $cuota->saldo_total = min(
            (int) $cuota->monto_total,
            (int) $cuota->saldo_total
            + $aplicacion['total']
        );

        $cuota->fecha_pago = PrestamoPago::query()
            ->where('prestamo_detalle_id', $cuota->id)
            ->where('id', '<>', $pago->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->whereIn('estado_pago_id', [
                self::ESTADO_PAGO_PAGADO,
                self::ESTADO_PAGO_PARCIAL,
            ])
            ->whereNotNull('fecha_pago')
            ->max('fecha_pago');

        $cuota->estado_pago_id =
            self::ESTADO_PAGO_ENVIADO_PLANILLA;

        $cuota->usuario_modificacion = $usuarioId;
        $cuota->save();
    }

    private function restaurarPlanillaPrestamo(
        PlanillaPrestamo $planillaPrestamo,
        int $usuarioId
    ): void {
        $planillaPrestamo->monto_capital_pagado = 0;
        $planillaPrestamo->monto_interes_pagado = 0;
        $planillaPrestamo->monto_iva_pagado = 0;
        $planillaPrestamo->monto_mora_pagado = 0;
        $planillaPrestamo->monto_mora_iva_pagado = 0;
        $planillaPrestamo->monto_pagado = 0;
        $planillaPrestamo->saldo =
            (int) $planillaPrestamo->monto_total;
        $planillaPrestamo->fecha_pago = null;
        $planillaPrestamo->estado_pago_id =
            self::ESTADO_PAGO_ENVIADO_PLANILLA;
        $planillaPrestamo->usuario_modificacion = $usuarioId;
        $planillaPrestamo->save();
    }

    /**
     * Conserva el pago anulado y crea un nuevo antecedente pendiente.
     */
    private function anularYRecrearHistoricoPrestamo(
        PrestamoPago $pago,
        PlanillaPrestamo $planillaPrestamo,
        Recibo $recibo,
        string $motivo,
        int $usuarioId
    ): void {
        $observacionAnulado = trim(
            (string) $pago->observaciones
            . ' | ANULADO POR RECIBO N.º '
            . $recibo->numero
            . ': '
            . mb_strtoupper(trim($motivo), 'UTF-8')
        );

        $pago->estado_pago_id = self::ESTADO_PAGO_ANULADO;
        $pago->estado_id = self::ESTADO_ANULADO;
        $pago->observaciones = mb_substr(
            $observacionAnulado,
            0,
            500
        );
        $pago->usuario_modificacion = $usuarioId;
        $pago->save();

        PrestamoPago::create([
            'prestamo_id' => $planillaPrestamo->prestamo_id,
            'prestamo_detalle_id' =>
                $planillaPrestamo->prestamo_detalle_id,
            'planilla_prestamo_id' => $planillaPrestamo->id,
            'recibo_id' => null,
            'canal_cobro' => $pago->canal_cobro,
            'fecha_generacion' => $pago->fecha_generacion
                ?: now()->toDateString(),
            'fecha_pago' => null,
            'monto_capital' =>
                (int) $planillaPrestamo->monto_capital,
            'monto_interes' =>
                (int) $planillaPrestamo->monto_interes,
            'monto_iva' => (int) $planillaPrestamo->monto_iva,
            'monto_mora' => (int) $planillaPrestamo->monto_mora,
            'monto_mora_iva' =>
                (int) $planillaPrestamo->monto_mora_iva,
            'monto_total' => (int) $planillaPrestamo->monto_total,
            'saldo_capital' =>
                (int) $planillaPrestamo->monto_capital,
            'saldo_interes' =>
                (int) $planillaPrestamo->monto_interes,
            'saldo_iva' => (int) $planillaPrestamo->monto_iva,
            'saldo_total' => (int) $planillaPrestamo->monto_total,
            'monto_capital_pagado' => 0,
            'monto_interes_pagado' => 0,
            'monto_iva_pagado' => 0,
            'monto_mora_pagado' => 0,
            'monto_mora_iva_pagado' => 0,
            'monto_pagado' => 0,
            'estado_pago_id' =>
                self::ESTADO_PAGO_ENVIADO_PLANILLA,
            'observaciones' => mb_substr(
                'CUOTA REACTIVADA POR ANULACIÓN DEL RECIBO N.º '
                . $recibo->numero,
                0,
                500
            ),
            'estado_id' => self::ESTADO_ACTIVO,
            'usuario_id' => $usuarioId,
            'usuario_modificacion' => $usuarioId,
        ]);
    }

    private function acumularTotalesPrestamo(
        array &$totales,
        int $prestamoId,
        array $aplicacion
    ): void {
        if (!isset($totales[$prestamoId])) {
            $totales[$prestamoId] = [
                'capital' => 0,
                'interes' => 0,
                'iva' => 0,
                'mora' => 0,
                'mora_iva' => 0,
                'total' => 0,
            ];
        }

        foreach ($totales[$prestamoId] as $campo => $valor) {
            $totales[$prestamoId][$campo] +=
                (int) $aplicacion[$campo];
        }
    }

    private function restaurarPrestamosCabecera(
        array $totalesPorPrestamo,
        int $usuarioId
    ): void {
        foreach ($totalesPorPrestamo as $prestamoId => $aplicacion) {
            $prestamo = Prestamo::query()
                ->whereKey($prestamoId)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->first();

            if (!$prestamo) {
                throw new \Exception(
                    'No se encontró la cabecera del préstamo ID '
                    . $prestamoId
                    . '.'
                );
            }

            $prestamo->monto_capital_pagado = max(
                0,
                (int) $prestamo->monto_capital_pagado
                - $aplicacion['capital']
            );

            $prestamo->monto_interes_pagado = max(
                0,
                (int) $prestamo->monto_interes_pagado
                - $aplicacion['interes']
            );

            $prestamo->monto_iva_pagado = max(
                0,
                (int) $prestamo->monto_iva_pagado
                - $aplicacion['iva']
            );

            $prestamo->monto_pagado = max(
                0,
                (int) $prestamo->monto_pagado
                - $aplicacion['total']
            );

            $prestamo->saldo_capital = min(
                (int) $prestamo->monto_capital,
                (int) $prestamo->saldo_capital
                + $aplicacion['capital']
            );

            $prestamo->saldo_interes = min(
                (int) $prestamo->monto_interes,
                (int) $prestamo->saldo_interes
                + $aplicacion['interes']
            );

            $prestamo->saldo_iva = min(
                (int) $prestamo->monto_iva,
                (int) $prestamo->saldo_iva
                + $aplicacion['iva']
            );

            $prestamo->saldo_total = min(
                (int) $prestamo->monto_total,
                (int) $prestamo->saldo_total
                + $aplicacion['total']
            );

            if ((int) $prestamo->saldo_total > 0) {
                $prestamo->estado_prestamo_id =
                    self::ESTADO_PRESTAMO_VIGENTE;

                $prestamo->fecha_cancelacion = null;
            } else {
                $prestamo->estado_prestamo_id =
                    self::ESTADO_PRESTAMO_CANCELADO;
            }

            $prestamo->usuario_modificacion = $usuarioId;
            $prestamo->save();
        }
    }

    private function obtenerAplicacionPago(
        PrestamoPago $pago
    ): array {
        return [
            'capital' => (int) $pago->monto_capital_pagado,
            'interes' => (int) $pago->monto_interes_pagado,
            'iva' => (int) $pago->monto_iva_pagado,
            'mora' => (int) $pago->monto_mora_pagado,
            'mora_iva' => (int) $pago->monto_mora_iva_pagado,
            'total' => (int) $pago->monto_pagado,
        ];
    }

    private function anularMovimientosDelRecibo(
        Recibo $recibo,
        int $usuarioId
    ): void {
        Aporte::query()
            ->where('recibo_id', $recibo->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->update([
                'estado_id' => self::ESTADO_ANULADO,
                'usuario_modificacion' => $usuarioId,
                'updated_at' => now(),
            ]);

        ReciboAporte::query()
            ->where('recibo_id', $recibo->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->update([
                'estado_id' => self::ESTADO_ANULADO,
                'usuario_modificacion' => $usuarioId,
                'updated_at' => now(),
            ]);

        ReciboPago::query()
            ->where('recibo_id', $recibo->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->update([
                'estado_id' => self::ESTADO_ANULADO,
                'updated_at' => now(),
            ]);
    }

    private function clasificarMovimientos(
        Recibo $recibo,
        Collection $reciboAportes,
        Collection $pagosPrestamo
    ): array {
        $movimientos = [];

        $this->agregarMovimiento(
            $movimientos,
            self::TIPO_INGRESO_APORTE_PLANILLA,
            (int) $reciboAportes->sum('aporte')
        );

        foreach ($pagosPrestamo as $pago) {
            $aplicacion = $this->obtenerAplicacionPago($pago);

            $totalComponentes =
                $aplicacion['capital']
                + $aplicacion['interes']
                + $aplicacion['iva']
                + $aplicacion['mora']
                + $aplicacion['mora_iva'];

            if ($totalComponentes !== $aplicacion['total']) {
                throw new \Exception(
                    'El pago histórico del préstamo ID '
                    . $pago->prestamo_id
                    . ' contiene importes inconsistentes.'
                );
            }

            $this->agregarMovimiento(
                $movimientos,
                $this->obtenerTipoIngresoCapital(
                    (int) $pago->prestamo_id
                ),
                $aplicacion['capital']
            );

            $this->agregarMovimiento(
                $movimientos,
                self::TIPO_INGRESO_INTERES_PRESTAMO,
                $aplicacion['interes']
            );

            $this->agregarMovimiento(
                $movimientos,
                self::TIPO_INGRESO_IVA_PRESTAMO,
                $aplicacion['iva'] + $aplicacion['mora_iva']
            );

            $this->agregarMovimiento(
                $movimientos,
                self::TIPO_INGRESO_PUNITORIO_PRESTAMO,
                $aplicacion['mora']
            );
        }

        $totalClasificado = (int) array_sum($movimientos);
        $totalRecibo = (int) $recibo->monto_total;

        if ($totalClasificado !== $totalRecibo) {
            throw new \Exception(
                'No se puede anular porque el total clasificado no coincide '
                . 'con el recibo. Clasificado: G. '
                . number_format($totalClasificado, 0, ',', '.')
                . '. Recibo: G. '
                . number_format($totalRecibo, 0, ',', '.')
                . '.'
            );
        }

        return $movimientos;
    }

    private function agregarMovimiento(
        array &$movimientos,
        int $tipoIngresoId,
        int $monto
    ): void {
        if ($tipoIngresoId <= 0 || $monto <= 0) {
            return;
        }

        if (!isset($movimientos[$tipoIngresoId])) {
            $movimientos[$tipoIngresoId] = 0;
        }

        $movimientos[$tipoIngresoId] += $monto;
    }

    private function obtenerTipoIngresoCapital(
        int $prestamoId
    ): int {
        if (isset(
            $this->tipoIngresoCapitalPorPrestamo[$prestamoId]
        )) {
            return $this->tipoIngresoCapitalPorPrestamo[
                $prestamoId
            ];
        }

        $tipoIngresoId = DB::table('prestamos as pr')
            ->leftJoin(
                'tipo_prestamos as tp',
                'tp.id',
                '=',
                'pr.tipo_prestamo_id'
            )
            ->where('pr.id', $prestamoId)
            ->value('tp.tipo_ingreso_id');

        $tipoIngresoId = (int) $tipoIngresoId > 0
            ? (int) $tipoIngresoId
            : self::TIPO_INGRESO_CAPITAL_PRESTAMO_DEFAULT;

        $this->tipoIngresoCapitalPorPrestamo[$prestamoId] =
            $tipoIngresoId;

        return $tipoIngresoId;
    }

    private function revertirResumenes(
        Recibo $recibo,
        array $movimientos
    ): void {
        $fecha = Carbon::parse($recibo->fecha);
        $anio = (int) $fecha->year;
        $mes = (int) $fecha->month;

        foreach ($movimientos as $tipoIngresoId => $monto) {
            if ((int) $monto <= 0) {
                continue;
            }

            $resumenMensual = ResumenMensual::query()
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->where(
                    'tipo_ingreso_id',
                    (int) $tipoIngresoId
                )
                ->whereNull('tipo_egreso_id')
                ->lockForUpdate()
                ->first();

            if (!$resumenMensual) {
                throw new \Exception(
                    'No se encontró el resumen mensual del tipo de ingreso '
                    . $tipoIngresoId
                    . '.'
                );
            }

            if (
                (int) $resumenMensual->total_ingreso
                    < (int) $monto
            ) {
                throw new \Exception(
                    'El resumen mensual del tipo de ingreso '
                    . $tipoIngresoId
                    . ' no posee saldo suficiente para la reversión.'
                );
            }

            $resumenMensual->total_ingreso =
                (int) $resumenMensual->total_ingreso
                - (int) $monto;

            $resumenMensual->save();
        }

        $resumenAnual = ResumenAnual::query()
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

        if (!$resumenAnual) {
            throw new \Exception(
                'No se encontró el resumen anual correspondiente al recibo.'
            );
        }

        $totalRecibo = (int) $recibo->monto_total;

        if ((int) $resumenAnual->total_ingreso < $totalRecibo) {
            throw new \Exception(
                'El resumen anual no posee saldo suficiente para la reversión.'
            );
        }

        $resumenAnual->total_ingreso =
            (int) $resumenAnual->total_ingreso
            - $totalRecibo;

        $resumenAnual->saldo_final =
            (int) $resumenAnual->saldo_inicial
            + (int) $resumenAnual->total_ingreso
            - (int) $resumenAnual->total_egreso;

        $resumenAnual->save();
    }
}
