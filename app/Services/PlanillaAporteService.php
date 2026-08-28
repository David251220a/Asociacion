<?php

namespace App\Services;

use App\Models\Planilla;
use App\Models\PlanillaAporte;
use App\Models\PlanillaDetalle;
use App\Models\PlanillaPrestamo;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\PrestamoPago;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanillaAporteService
{
    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN DE PRÉSTAMOS
    |--------------------------------------------------------------------------
    |
    | calcular_iva:
    |   0 = no calcular IVA sobre la mora.
    |   1 = calcular IVA sobre la mora.
    |
    */
    private int $calcular_iva = 0;
    private int $porcentaje_iva = 10;
    private int $dias_gracia = 20;

    private const ESTADO_ACTIVO = 1;

    private const ESTADO_PAGO_PENDIENTE = 1;
    private const ESTADO_PAGO_PARCIAL = 3;
    private const ESTADO_PAGO_ENVIADO_PLANILLA = 5;

    private const ESTADO_PRESTAMO_VIGENTE = 2;
    private const CANAL_COBRO_PLANILLA = 1;

    /**
     * Genera la vista previa completa de la planilla.
     *
     * Cada elemento devuelto representa una sola fila de planilla_detalles
     * por asociado y contiene, internamente, su aporte y sus cuotas de
     * préstamo correspondientes.
    */
    public function generarDetalle(int $mes, int $anio, int $tipoAsociadoId): Collection
    {

        $this->validarSecuenciaPlanilla($mes, $anio, $tipoAsociadoId);

        $aportes = $this->generarDetalleAportes($mes, $anio, $tipoAsociadoId);
        $prestamos = $this->generarDetallePrestamos($mes, $anio,$tipoAsociadoId);

        /*
        |==========================================================================
        | AQUÍ SE UNEN APORTE Y PRÉSTAMO
        |==========================================================================
        |
        | El método agrupa ambos conceptos por asociado y suma sus saldos.
        | El resultado es el que se muestra y luego se guarda en la planilla.
        |
        */
        return $this->unificarAportesPrestamos($aportes, $prestamos);
    }

    /**
     * Guarda la cabecera de planilla, su detalle consolidado y los conceptos
     * separados en planilla_aportes y planilla_prestamos.
    */
    public function guardarPlanilla(int $mes, int $anio, int $tipoAsociadoId): Collection
    {
        return DB::transaction(function () use ($mes, $anio, $tipoAsociadoId) {
            $detalles = $this->generarDetalle($mes, $anio, $tipoAsociadoId);

            if ($detalles->isEmpty()) {
                throw new \Exception('No existen aportes ni cuotas de préstamos pendientes para generar la planilla.');
            }

            $tipoGeneracion = $tipoAsociadoId === 3 ? 'APORTANTE' : 'PASIVO';
            $loteGeneracion = $tipoGeneracion
            . '_'
            . str_pad((string) $mes, 2, '0', STR_PAD_LEFT)
            . '_'
            . $anio
            . '_'
            . $tipoAsociadoId
            . '_'
            . strtoupper(Str::random(6));

            $planillasGeneradas = collect();
            $ahora = now();
            $usuarioId = (int) auth()->id();

            if ($usuarioId <= 0) {
                throw new \Exception('No se pudo identificar al usuario que genera la planilla.');
            }

            /*
            |--------------------------------------------------------------------------
            | OBTENER LA SIGUIENTE NUMERACIÓN
            |--------------------------------------------------------------------------
            */
            $ultimoNumero = Planilla::query()
            ->where('planilla_anio', $anio)
            ->lockForUpdate()
            ->max('planilla_numero');

            $siguienteNumero = $ultimoNumero ? (int) $ultimoNumero + 1 : 1;

            /*
            |--------------------------------------------------------------------------
            | PASIVOS: UNA SOLA CABECERA
            |--------------------------------------------------------------------------
            */
            if ($tipoAsociadoId !== 3) {
                $existe = Planilla::query()
                ->whereIn('tipo_asociado_id', [1, 2])
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->where('institucion_id', 1)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->exists();

                if ($existe) {
                    throw new \Exception('Ya existe una planilla generada para pasivos en ese mes y año.');
                }

                $planilla = $this->crearCabeceraPlanilla($tipoAsociadoId, 1, $siguienteNumero, $mes, $anio, $detalles, $loteGeneracion, $ahora, $usuarioId);
                $this->guardarDetallesPlanilla($planilla, $detalles, $ahora, $usuarioId);
                $planillasGeneradas->push($planilla);

                return $planillasGeneradas;
            }

            /*
            |--------------------------------------------------------------------------
            | APORTANTES: UNA PLANILLA POR INSTITUCIÓN
            |--------------------------------------------------------------------------
            */
            $gruposPorInstitucion = $detalles->groupBy('institucion_id');

            foreach ($gruposPorInstitucion as $institucionId => $items) {
                $institucionId = (int) $institucionId;

                if ($institucionId <= 0) {
                    throw new \Exception('Existen asociados aportantes sin institución asignada.');
                }

                $existe = Planilla::query()
                ->where('tipo_asociado_id', $tipoAsociadoId)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->where('institucion_id', $institucionId)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->exists();

                if ($existe) {
                    throw new \Exception('Ya existe una planilla generada para la institución ID ' . $institucionId . ' en ese mes y año.');
                }

                $planilla = $this->crearCabeceraPlanilla($tipoAsociadoId, $institucionId, $siguienteNumero, $mes, $anio, $items, $loteGeneracion, $ahora, $usuarioId);
                $this->guardarDetallesPlanilla($planilla, $items, $ahora, $usuarioId);
                $planillasGeneradas->push($planilla);
                $siguienteNumero++;
            }

            return $planillasGeneradas;
        });
    }

    private function crearCabeceraPlanilla(
        int $tipoAsociadoId, int $institucionId,
        int $numero, int $mes, int $anio, Collection $items, string $loteGeneracion, Carbon $ahora, int $usuarioId): Planilla
    {
        return Planilla::create([
            'tipo_asociado_id' => $tipoAsociadoId,
            'institucion_id' => $institucionId,
            'planilla_numero' => $numero,
            'planilla_anio' => $anio,
            'mes' => $mes,
            'anio' => $anio,
            'fecha' => $ahora->toDateString(),
            'cantidad' => $items->count(),
            'total' => (int) $items->sum('saldo'),
            'pagado' => 0,
            'monto_pagado' => 0,
            'fecha_pagado' => null,
            'lote_generacion' => $loteGeneracion,
            'estado_id' => self::ESTADO_ACTIVO,
            'user_id' => $usuarioId,
            'usuario_modificacion' => $usuarioId,
        ]);
    }

    /**
     * Calcula los aportes pendientes del periodo.
    */
    private function generarDetalleAportes(int $mes, int $anio, int $tipoAsociadoId): Collection
    {
        $tipos = $tipoAsociadoId === 3 ? [3] : [1, 2];
        $tiposCabecera = $tipos;
        $periodo = Carbon::create($anio, $mes, 1)->startOfMonth();
        $periodoActual = sprintf('%04d%02d', $anio, $mes);

        $subAportes = DB::table('aportes as ap')
        ->join('asociados as a', 'a.id', '=', 'ap.asociado_id')
        ->select('ap.asociado_id', DB::raw('SUM(ap.aporte) as total_aportado'))
        ->where('ap.anio', $anio)
        ->where('ap.mes', $mes)
        ->where('a.estado_id', self::ESTADO_ACTIVO)
        ->where('ap.estado_id', self::ESTADO_ACTIVO)
        ->whereIn('a.tipo_asociado_id', $tipos)
        ->groupBy('ap.asociado_id');

        /*
        |--------------------------------------------------------------------------
        | PERIODOS DE APORTE YA GENERADOS
        |--------------------------------------------------------------------------
        |
        | Se une planilla_aportes para que una fila que tenga solamente préstamo
        | no sea contada erróneamente como un periodo de aporte generado.
        |
        */
        $subPlanillasHistoricas = DB::table('planilla_aportes as pa')
        ->join('planilla_detalles as pd', 'pd.id', '=', 'pa.planilla_detalle_id')
        ->join('planillas as pl', 'pl.id', '=', 'pd.planilla_id')
        ->select(
            'pd.asociado_id',
            DB::raw("COUNT(DISTINCT CONCAT(pl.anio, LPAD(pl.mes, 2, '0'))) as meses_generados"
        ))
        ->where('pa.estado_id', self::ESTADO_ACTIVO)
        ->where('pd.estado_id', self::ESTADO_ACTIVO)
        ->where('pl.estado_id', self::ESTADO_ACTIVO)
        ->whereIn('pl.tipo_asociado_id', $tiposCabecera)
        ->whereRaw("CONCAT(pl.anio, LPAD(pl.mes, 2, '0')) < ?", [$periodoActual])
        ->groupBy('pd.asociado_id');

        $rows = DB::table('asociados as a')
        ->join('personas as p','p.id', '=', 'a.persona_id')
        ->leftJoinSub($subAportes, 'ap',
            function ($join) {
                $join->on('ap.asociado_id', '=', 'a.id');
            }
        )
        ->leftJoinSub($subPlanillasHistoricas, 'ph',
            function ($join) {
                $join->on('ph.asociado_id', '=', 'a.id');
            }
        )
        ->select([
            'a.id',
            'a.tipo_asociado_id',
            'a.numero_socio',
            'a.fecha_admision',
            'a.fecha_baja',
            'a.institucion_id',
            'p.nombre',
            'p.apellido',
            DB::raw('COALESCE(ap.total_aportado, 0) as pagado'),
            DB::raw('COALESCE(ph.meses_generados, 0) as meses_generados'),
        ])
        ->whereIn('a.tipo_asociado_id', $tipos)
        ->where('a.estado_id', self::ESTADO_ACTIVO)
        ->whereDate('a.fecha_admision', '<=', $periodo->copy()->endOfMonth()->toDateString())
        ->where(function ($consulta) use ($periodo) {
            $consulta->whereNull('a.fecha_baja')
            ->orWhereDate('a.fecha_baja', '>=', $periodo->copy()->startOfMonth()->toDateString());
        })
        ->orderBy('a.institucion_id')
        ->orderBy('a.numero_socio')
        ->get();

        return collect($rows)
        ->map(function ($item) use ($periodo) {
            $fechaAdmision = Carbon::parse($item->fecha_admision)->startOfMonth();
            if ($fechaAdmision->gt($periodo)) {
                return null;
            }
            $numeroPeriodo = (int) $item->meses_generados + 1;
            $montoEsperado = $numeroPeriodo <= 5 ? 30000 : 20000;

            $pagado = (int) $item->pagado;
            $saldo = $montoEsperado - $pagado;

            if ($saldo <= 0) {
                return null;
            }

            return [
                'asociado_id' => (int) $item->id,
                'tipo_asociado_id' => (int) $item->tipo_asociado_id,
                'institucion_id' => (int) $item->institucion_id,
                'numero_socio' => $item->numero_socio,
                'nombre' => trim(($item->nombre ?? '') . ' ' . ($item->apellido ?? '')),
                'numero_periodo' => $numeroPeriodo,
                'monto_esperado' => $montoEsperado,
                'pagado' => $pagado,
                'saldo' => $saldo,
                'estado' => $pagado > 0 ? 'PARCIAL' : 'PENDIENTE',
            ];
        })
        ->filter()
        ->values();
    }

    /**
     * Obtiene las cuotas de préstamos que corresponde incluir en el periodo.
     *
     * Regla aplicada:
     * - Si existe cuota con vencimiento en el mes solicitado, se envía esa
     *   cuota y no se agregan cuotas atrasadas.
     * - Si no existe cuota del mes, se envían las cuotas anteriores pendientes
     *   y se calcula mora hasta el último día del mes solicitado.
     * - Una cuota con estado ENVIADO A PLANILLA no vuelve a incluirse mientras
     *   conserve ese estado en planilla_prestamos.
    */
    private function generarDetallePrestamos(int $mes, int $anio, int $tipoAsociadoId): Collection
    {
        $tipos = $tipoAsociadoId === 3 ? [3] : [1, 2];
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();
        /*
        |--------------------------------------------------------------------------
        | CUOTAS YA ENVIADAS A UNA PLANILLA ACTIVA
        |--------------------------------------------------------------------------
        */
        $cuotasPlanilladas = DB::table('planilla_prestamos as pp')
        ->join('planilla_detalles as pd', 'pd.id', '=', 'pp.planilla_detalle_id')
        ->join('planillas as pl', 'pl.id', '=', 'pd.planilla_id')
        ->where('pp.estado_id', self::ESTADO_ACTIVO)
        ->where('pd.estado_id', self::ESTADO_ACTIVO)
        ->where('pl.estado_id', self::ESTADO_ACTIVO)
        ->where('pp.estado_pago_id', self::ESTADO_PAGO_ENVIADO_PLANILLA)
        ->pluck('pp.prestamo_detalle_id')
        ->mapWithKeys(function ($id) {
            return [(int) $id => true];
        });

        $cuotas = DB::table('prestamo_detalles as pdt')
        ->join('prestamos as pr', 'pr.id', '=', 'pdt.prestamo_id')
        ->join('asociados as a', 'a.persona_id', '=', 'pr.persona_id')
        ->join('personas as p', 'p.id', '=', 'a.persona_id')
        ->select([
            'pdt.id as prestamo_detalle_id',
            'pdt.prestamo_id',
            'pdt.numero_cuota',
            'pdt.fecha_vencimiento',
            'pdt.saldo_capital',
            'pdt.saldo_interes',
            'pdt.saldo_iva',
            'pdt.saldo_total',
            'pdt.monto_mora',
            'pdt.monto_mora_iva',
            'pdt.fecha_pago',
            'pdt.fecha_ultimo_calculo_mora',
            'pr.tasa_mora',
            'a.id as asociado_id',
            'a.tipo_asociado_id',
            'a.numero_socio',
            'a.institucion_id',
            'p.nombre',
            'p.apellido',
        ])
        ->whereIn('a.tipo_asociado_id', $tipos)
        ->where('a.estado_id', self::ESTADO_ACTIVO)
        ->where('pr.estado_id', self::ESTADO_ACTIVO)
        ->where('pr.estado_prestamo_id', self::ESTADO_PRESTAMO_VIGENTE)
        ->where('pdt.estado_id', self::ESTADO_ACTIVO)
        ->whereIn('pdt.estado_pago_id', [
            self::ESTADO_PAGO_PENDIENTE,
            self::ESTADO_PAGO_PARCIAL,
            self::ESTADO_PAGO_ENVIADO_PLANILLA,
        ])
        ->where('pdt.saldo_total', '>', 0)
        ->whereDate('pdt.fecha_vencimiento', '<=', $fechaFin->toDateString())
        ->orderBy('a.id')
        ->orderBy('pdt.fecha_vencimiento')
        ->get();

        return $cuotas
            ->groupBy('asociado_id')
            ->flatMap(function ($items) use ($fechaInicio, $fechaFin, $cuotasPlanilladas) {
                $cuotasDelMes = $items->filter(
                    function ($cuota) use ($fechaInicio, $fechaFin) {
                        $fechaVencimiento = Carbon::parse($cuota->fecha_vencimiento);
                        return $fechaVencimiento->between($fechaInicio, $fechaFin);
                    }
                );

                if ($cuotasDelMes->isNotEmpty()) {
                    $seleccionadas = $cuotasDelMes;
                    $sonAtrasadas = false;
                } else {
                    $seleccionadas = $items->filter(
                        function ($cuota) use ($fechaInicio) {
                            return Carbon::parse($cuota->fecha_vencimiento)->lt($fechaInicio);
                        }
                    );

                    $sonAtrasadas = true;
                }

                return $seleccionadas
                ->reject(function ($cuota) use ($cuotasPlanilladas) {
                    return isset($cuotasPlanilladas[(int) $cuota->prestamo_detalle_id]);
                })
                ->map(function ($cuota) use ($sonAtrasadas,$fechaFin) {
                    $moraNueva = ['dias_mora' => 0, 'monto_mora' => 0, 'monto_mora_iva' => 0];

                    if ($sonAtrasadas) {
                        $moraNueva = $this->calcularMoraCuota($cuota, $fechaFin);
                    }

                    $capital = (int) $cuota->saldo_capital;
                    $interes = (int) $cuota->saldo_interes;
                    $iva = (int) $cuota->saldo_iva;
                    $montoMora = (int) $cuota->monto_mora + (int) $moraNueva['monto_mora'];
                    $montoMoraIva = (int) $cuota->monto_mora_iva + (int) $moraNueva['monto_mora_iva'];
                    $total = $capital + $interes + $iva + $montoMora + $montoMoraIva;

                    return [
                        'asociado_id' => (int) $cuota->asociado_id,
                        'tipo_asociado_id' => (int) $cuota->tipo_asociado_id,
                        'institucion_id' => (int) $cuota->institucion_id,
                        'numero_socio' => $cuota->numero_socio,
                        'nombre' => trim(($cuota->nombre ?? '') . ' ' . ($cuota->apellido ?? '')),
                        'prestamo_id' => (int) $cuota->prestamo_id,
                        'prestamo_detalle_id' => (int) $cuota->prestamo_detalle_id,
                        'numero_cuota' => (int) $cuota->numero_cuota,
                        'fecha_vencimiento' => $cuota->fecha_vencimiento,
                        'monto_capital' => $capital,
                        'monto_interes' => $interes,
                        'monto_iva' => $iva,
                        'monto_mora' => $montoMora,
                        'monto_mora_iva' => $montoMoraIva,
                        'monto_mora_nueva' => (int) $moraNueva['monto_mora'],
                        'monto_mora_iva_nueva' => (int) $moraNueva['monto_mora_iva'],
                        'dias_mora' => (int) $moraNueva['dias_mora'],
                        'fecha_calculo_mora' => $fechaFin->toDateString(),
                        'monto_total' => $total,
                        'monto_pagado' => 0,
                        'saldo' => $total,
                        'es_atrasada' => $sonAtrasadas,
                    ];
                });
        })
        ->values();
    }

    /**
     * Unifica aporte y préstamo en una sola fila por asociado.
    */
    private function unificarAportesPrestamos(Collection $aportes, Collection $prestamos): Collection
    {
        $filas = collect();

        foreach ($aportes as $aporte) {
            $asociadoId = (int) $aporte['asociado_id'];

            $filas->put($asociadoId, [
                'asociado_id' => $asociadoId,
                'tipo_asociado_id' => (int) $aporte['tipo_asociado_id'],
                'institucion_id' => (int) $aporte['institucion_id'],
                'numero_socio' => $aporte['numero_socio'],
                'nombre' => $aporte['nombre'],
                'monto_esperado' => (int) $aporte['monto_esperado'],
                'pagado' => (int) $aporte['pagado'],
                'saldo' => (int) $aporte['saldo'],
                'estado' => $aporte['estado'],
                'aporte' => $aporte,
                'prestamos' => [],
            ]);
        }

        foreach ($prestamos as $prestamo) {
            $asociadoId = (int) $prestamo['asociado_id'];
            $fila = $filas->get($asociadoId);

            if (!$fila) {
                $fila = [
                    'asociado_id' => $asociadoId,
                    'tipo_asociado_id' => (int) $prestamo['tipo_asociado_id'],
                    'institucion_id' => (int) $prestamo['institucion_id'],
                    'numero_socio' => $prestamo['numero_socio'],
                    'nombre' => $prestamo['nombre'],
                    'monto_esperado' => 0,
                    'pagado' => 0,
                    'saldo' => 0,
                    'estado' => 'PENDIENTE',
                    'aporte' => null,
                    'prestamos' => [],
                ];
            }

            $fila['prestamos'][] = $prestamo;
            $fila['monto_esperado'] += (int) $prestamo['monto_total'];
            $fila['pagado'] += (int) $prestamo['monto_pagado'];
            $fila['saldo'] += (int) $prestamo['saldo'];

            if ((int) $fila['pagado'] > 0) {
                $fila['estado'] = 'PARCIAL';
            }

            $filas->put($asociadoId, $fila);
        }

        return $filas
        ->sort(function ($primero, $segundo) {
            return [
                (int) $primero['institucion_id'],
                (string) $primero['numero_socio'],
            ] <=> [
                (int) $segundo['institucion_id'],
                (string) $segundo['numero_socio'],
            ];
        })
        ->values();
    }

    /**
     * Guarda una fila consolidada por asociado y después registra por separado
     * el aporte y cada cuota de préstamo que componen esa fila.
    */
    private function guardarDetallesPlanilla(Planilla $planilla, Collection $items, Carbon $ahora, int $usuarioId): void
    {
        $insertDetalles = [];

        foreach ($items as $item) {
            $insertDetalles[] = [
                'planilla_id' => $planilla->id,
                'asociado_id' => $item['asociado_id'],
                'tipo_asociado_id' => $item['tipo_asociado_id'],
                'institucion_id' => $item['institucion_id'],
                'monto_esperado' => $item['monto_esperado'],
                'pagado' => $item['pagado'],
                'saldo' => $item['saldo'],
                'estado_id' => self::ESTADO_ACTIVO,
                'user_id' => $usuarioId,
                'usuario_modificacion' => $usuarioId,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        PlanillaDetalle::insert($insertDetalles);

        $detallesGuardados = PlanillaDetalle::query()
        ->where('planilla_id', $planilla->id)
        ->whereIn('asociado_id', $items->pluck('asociado_id'))
        ->lockForUpdate()
        ->get()
        ->keyBy('asociado_id');

        $insertAportes = [];

        foreach ($items as $item) {
            $detallePlanilla = $detallesGuardados->get($item['asociado_id']);

            if (!$detallePlanilla) {
                throw new \Exception('No se pudo localizar el detalle de planilla del asociado ID ' . $item['asociado_id'] . '.');
            }

            /*
            |--------------------------------------------------------------------------
            | GUARDAR APORTE DEL ASOCIADO
            |--------------------------------------------------------------------------
            */
            if (!empty($item['aporte'])) {
                $aporte = $item['aporte'];

                $insertAportes[] = [
                    'planilla_detalle_id' => $detallePlanilla->id,
                    'monto_esperado' => (int) $aporte['monto_esperado'],
                    'monto_pagado' => (int) $aporte['pagado'],
                    'saldo' => (int) $aporte['saldo'],
                    'estado_pago_id' => self::ESTADO_PAGO_ENVIADO_PLANILLA,
                    'estado_id' => self::ESTADO_ACTIVO,
                    'user_id' => $usuarioId,
                    'usuario_modificacion' => $usuarioId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | GUARDAR CUOTAS DE PRÉSTAMO DEL ASOCIADO
            |--------------------------------------------------------------------------
            */
            foreach ($item['prestamos'] as $prestamoItem) {
                $this->guardarPrestamoPlanillado($planilla, $detallePlanilla, $prestamoItem, $ahora, $usuarioId);
            }
        }

        if (!empty($insertAportes)) {
            PlanillaAporte::insert($insertAportes);
        }
    }

    /**
     * Registra una cuota dentro de la planilla, actualiza la cuota original y
     * crea el antecedente en prestamo_pagos.
    */
    private function guardarPrestamoPlanillado(Planilla $planilla, PlanillaDetalle $detallePlanilla, array $prestamoItem, Carbon $ahora, int $usuarioId): void
    {
        $detallePrestamo = PrestamoDetalle::query()
        ->whereKey($prestamoItem['prestamo_detalle_id'])
        ->where('prestamo_id', $prestamoItem['prestamo_id'])
        ->where('estado_id', self::ESTADO_ACTIVO)
        ->lockForUpdate()
        ->first();

        if (!$detallePrestamo) {
            throw new \Exception('No se encontró la cuota de préstamo ID ' . $prestamoItem['prestamo_detalle_id'] . '.');
        }

        if (!in_array((int) $detallePrestamo->estado_pago_id,
            [
                self::ESTADO_PAGO_PENDIENTE,
                self::ESTADO_PAGO_PARCIAL,
            ],
            true
        )) {
            throw new \Exception('La cuota de préstamo N.º ' . $detallePrestamo->numero_cuota . ' ya fue enviada a otra planilla o cambió de estado.');
        }

        $prestamo = Prestamo::query()
        ->whereKey($prestamoItem['prestamo_id'])
        ->where('estado_prestamo_id', self::ESTADO_PRESTAMO_VIGENTE)
        ->where('estado_id', self::ESTADO_ACTIVO)
        ->lockForUpdate()
        ->first();

        if (!$prestamo) {
            throw new \Exception('El préstamo ID ' . $prestamoItem['prestamo_id'] . ' ya no se encuentra vigente.');
        }

        $moraNueva = (int) $prestamoItem['monto_mora_nueva'];
        $moraIvaNueva = (int) $prestamoItem['monto_mora_iva_nueva'];
        $incrementoMora = $moraNueva + $moraIvaNueva;
        $detallePrestamo->monto_mora = (int) $detallePrestamo->monto_mora + $moraNueva;

        $detallePrestamo->monto_mora_iva = (int) $detallePrestamo->monto_mora_iva + $moraIvaNueva;
        $detallePrestamo->monto_total = (int) $detallePrestamo->monto_total + $incrementoMora;
        $detallePrestamo->saldo_total = (int) $detallePrestamo->saldo_total + $incrementoMora;
        $detallePrestamo->estado_pago_id = self::ESTADO_PAGO_ENVIADO_PLANILLA;

        if ((int) $prestamoItem['dias_mora'] > 0) {
            $detallePrestamo->fecha_ultimo_calculo_mora = $prestamoItem['fecha_calculo_mora'];
        }

        $detallePrestamo->usuario_modificacion = $usuarioId;
        $detallePrestamo->save();

        if ($incrementoMora > 0) {
            $prestamo->monto_total = (int) $prestamo->monto_total + $incrementoMora;
            $prestamo->saldo_total = (int) $prestamo->saldo_total + $incrementoMora;
            $prestamo->usuario_modificacion = $usuarioId;
            $prestamo->save();
        }

        $planillaPrestamo = PlanillaPrestamo::create([
            'planilla_detalle_id' => $detallePlanilla->id,
            'prestamo_id' => $prestamo->id,
            'prestamo_detalle_id' => $detallePrestamo->id,
            'numero_cuota' => (int) $prestamoItem['numero_cuota'],
            'fecha_vencimiento' => $prestamoItem['fecha_vencimiento'],
            'monto_capital' => (int) $prestamoItem['monto_capital'],
            'monto_interes' => (int) $prestamoItem['monto_interes'],
            'monto_iva' => (int) $prestamoItem['monto_iva'],
            'monto_mora' => (int) $prestamoItem['monto_mora'],
            'monto_mora_iva' => (int) $prestamoItem['monto_mora_iva'],
            'monto_total' => (int) $prestamoItem['monto_total'],
            'monto_capital_pagado' => 0,
            'monto_interes_pagado' => 0,
            'monto_iva_pagado' => 0,
            'monto_mora_pagado' => 0,
            'monto_mora_iva_pagado' => 0,
            'monto_pagado' => 0,
            'saldo' => (int) $prestamoItem['saldo'],
            'fecha_pago' => null,
            'estado_pago_id' => self::ESTADO_PAGO_ENVIADO_PLANILLA,
            'estado_id' => self::ESTADO_ACTIVO,
            'user_id' => $usuarioId,
            'usuario_modificacion' => $usuarioId,
        ]);

        /*
        |--------------------------------------------------------------------------
        | HISTÓRICO / ANTECEDENTE DE COBRO DEL PRÉSTAMO
        |--------------------------------------------------------------------------
        |
        | No representa todavía un pago efectivo. Registra que la cuota fue
        | enviada a esta planilla y queda pendiente de su resultado.
        |
        */
        PrestamoPago::create([
            'prestamo_id' => $prestamo->id,
            'prestamo_detalle_id' => $detallePrestamo->id,
            'planilla_prestamo_id' => $planillaPrestamo->id,
            'canal_cobro' => self::CANAL_COBRO_PLANILLA,
            'fecha_generacion' => $ahora->toDateString(),
            'fecha_pago' => null,
            'monto_capital' => (int) $prestamoItem['monto_capital'],
            'monto_interes' => (int) $prestamoItem['monto_interes'],
            'monto_iva' => (int) $prestamoItem['monto_iva'],
            'monto_mora' => (int) $prestamoItem['monto_mora'],
            'monto_mora_iva' => (int) $prestamoItem['monto_mora_iva'],
            'monto_total' => (int) $prestamoItem['monto_total'],
            'saldo_capital' => (int) $prestamoItem['monto_capital'],
            'saldo_interes' => (int) $prestamoItem['monto_interes'],
            'saldo_iva' => (int) $prestamoItem['monto_iva'],
            'saldo_total' => (int) $prestamoItem['saldo'],
            'monto_capital_pagado' => 0,
            'monto_interes_pagado' => 0,
            'monto_iva_pagado' => 0,
            'monto_mora_pagado' => 0,
            'monto_mora_iva_pagado' => 0,
            'monto_pagado' => 0,
            'estado_pago_id' => self::ESTADO_PAGO_ENVIADO_PLANILLA,
            'observaciones' => 'CUOTA ENVIADA A PLANILLA N.º '
                . str_pad(
                    (string) $planilla->planilla_numero,
                    7,
                    '0',
                    STR_PAD_LEFT
                )
                . '/'
                . $planilla->planilla_anio,
            'estado_id' => self::ESTADO_ACTIVO,
            'usuario_id' => $usuarioId,
            'usuario_modificacion' => $usuarioId,
        ]);
    }

    /**
     * Calcula solamente la mora nueva todavía no incorporada a la cuota.
     */
    private function calcularMoraCuota(object $cuota, Carbon $fechaCorte): array
    {
        $saldoCapital = (int) $cuota->saldo_capital;

        if ($saldoCapital <= 0) {
            return $this->moraCero();
        }

        $fechaLimiteGracia = Carbon::parse($cuota->fecha_vencimiento)->startOfDay();

        if ($this->dias_gracia > 0) {
            $fechaLimiteGracia->addDays($this->dias_gracia);
        }

        if ($fechaCorte->lte($fechaLimiteGracia)) {
            return $this->moraCero();
        }

        $fechaBase = $fechaLimiteGracia->copy();

        if (!empty($cuota->fecha_pago)) {
            $fechaUltimoPago = Carbon::parse($cuota->fecha_pago)->startOfDay();

            if ($fechaUltimoPago->gt($fechaBase)) {
                $fechaBase = $fechaUltimoPago;
            }
        }

        if (!empty($cuota->fecha_ultimo_calculo_mora)) {
            $fechaUltimoCalculo = Carbon::parse($cuota->fecha_ultimo_calculo_mora)->startOfDay();

            if ($fechaUltimoCalculo->gt($fechaBase)) {
                $fechaBase = $fechaUltimoCalculo;
            }
        }

        if ($fechaCorte->lte($fechaBase)) {
            return $this->moraCero();
        }

        $diasMora = (int) $fechaBase->diffInDays($fechaCorte);
        $tasaMora = (float) $cuota->tasa_mora;

        if ($diasMora <= 0 || $tasaMora <= 0) {
            return $this->moraCero();
        }

        $montoMora = (int) round($saldoCapital * ($tasaMora / 100) * ($diasMora / 30));

        $montoMoraIva = $this->calcular_iva === 1 ? (int) round( $montoMora * ($this->porcentaje_iva / 100)) : 0;

        return [
            'dias_mora' => $diasMora,
            'monto_mora' => $montoMora,
            'monto_mora_iva' => $montoMoraIva,
        ];
    }

    private function moraCero(): array
    {
        return [
            'dias_mora' => 0,
            'monto_mora' => 0,
            'monto_mora_iva' => 0,
        ];
    }

    private function validarSecuenciaPlanilla(int $mes, int $anio, int $tipoAsociadoId): void {
        $tiposCabecera = $tipoAsociadoId === 3 ? [3] : [1, 2];

        $ultimaPlanilla = Planilla::query()
        ->whereIn('tipo_asociado_id', $tiposCabecera)
        ->where('estado_id', self::ESTADO_ACTIVO)
        ->orderByDesc('anio')
        ->orderByDesc('mes')
        ->first();

        if (!$ultimaPlanilla) {
            return;
        }

        $fechaUltima = Carbon::create($ultimaPlanilla->anio, $ultimaPlanilla->mes, 1);

        $fechaEsperada = $fechaUltima->copy()->addMonth();
        $fechaNueva = Carbon::create($anio, $mes, 1);

        if (!$fechaNueva->equalTo($fechaEsperada)) {
            throw new \Exception(
                'No puede generar la planilla de '
                . ucfirst($fechaNueva->locale('es')->translatedFormat('F Y'))
                . '. Debe generar primero la planilla de '
                . ucfirst($fechaEsperada->locale('es')->translatedFormat('F Y'))
                . '.'
            );
        }
    }
}
