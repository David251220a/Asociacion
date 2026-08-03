<?php

namespace App\Services;

use App\Models\Numeraciones;
use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use App\Models\SolicitudAyudaSocial;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnularOrdenPagoService
{
    /**
     * Anular una orden de pago creada manualmente.
     */
    public function anularSinOrigen(OrdenPago $ordenPago, string $motivo, int $usuarioId): void
    {
        DB::transaction(function () use ($ordenPago, $motivo, $usuarioId) {
            /*
            |--------------------------------------------------------------
            | Volver a consultar y bloquear la orden
            |--------------------------------------------------------------
            */
            $orden = OrdenPago::lockForUpdate()
            ->findOrFail($ordenPago->id);

            if ((int) $orden->origen_id !== 0) {
                throw new \Exception('La orden de pago se encuentra vinculada a otro documento.');
            }

            if ((int) $orden->estado_id === 2) {
                throw new \Exception('La orden de pago ya se encuentra anulada.');
            }
            $estabaPagada = (int) $orden->estado_pago === 1;
            /*
            |--------------------------------------------------------------
            | Anular orden, detalles y pagos
            |--------------------------------------------------------------
            */
            $this->anularOrdenBase($orden,$motivo,$usuarioId);
            /*
            |--------------------------------------------------------------
            | Revertir tesorería si estaba pagada
            |--------------------------------------------------------------
            */
            if ($estabaPagada) {
                $this->revertirTesoreria($orden);
            }
        });
    }

    /**
     * Anular la orden, sus detalles y sus pagos.
     */
    private function anularOrdenBase(OrdenPago $orden, string $motivo,int $usuarioId): void
    {
        $orden->update([
            'estado_id' => 2,
            'estado_pago' => 2,
            'usuario_modificacion' => $usuarioId,
            'fecha_anulado' => now()->toDateString(),
            'motivo_anulado' => $motivo,
        ]);

        OrdenPagoDetalle::where('orden_pago_id', $orden->id)
        ->update([
            'estado_id' => 2,
            'usuario_modificacion' => $usuarioId,
            'updated_at' => now(),
        ]);

        $orden->pagos()->update([
            'estado_id' => 2,
            'updated_at' => now(),
        ]);
    }

    /**
     * Restar el egreso de los resúmenes.
     */
    private function revertirTesoreria(OrdenPago $orden): void
    {
        /*
         * La reversión debe utilizar la misma fecha con la que fue
         * registrado el pago en los resúmenes.
        */
        $fechaMovimiento = $orden->fecha;
        $fechaResumen = Carbon::parse($fechaMovimiento);
        $anio = (int) $fechaResumen->year;
        $mes = (int) $fechaResumen->month;
        $monto = (int) $orden->total;

        $resumenMensual = ResumenMensual::where('anio', $anio)
        ->where('mes', $mes)
        ->where('tipo_egreso_id', $orden->tipo_egreso_id)
        ->whereNull('tipo_ingreso_id')
        ->lockForUpdate()
        ->first();

        if (!$resumenMensual) {
            throw new \Exception('No se encontró el resumen mensual correspondiente al pago.');
        }
        $resumenMensual->total_egreso = (int) $resumenMensual->total_egreso - $monto;
        $resumenMensual->save();

        $resumenAnual = ResumenAnual::where('anio', $anio)
        ->lockForUpdate()
        ->first();

        if (!$resumenAnual) {
            throw new \Exception('No se encontró el resumen anual correspondiente al pago.');
        }
        $resumenAnual->total_egreso = (int) $resumenAnual->total_egreso - $monto;
        $resumenAnual->saldo_final = (int) $resumenAnual->saldo_inicial + (int) $resumenAnual->total_ingreso - (int) $resumenAnual->total_egreso;
        $resumenAnual->save();
    }

    public function anularAyudaSocialYReemitir(OrdenPago $ordenPago,string $motivo,int $usuarioId): OrdenPago
    {
        return DB::transaction(function () use ($ordenPago, $motivo, $usuarioId) {
            /*
            |--------------------------------------------------------------
            | BLOQUEAR ORDEN
            |--------------------------------------------------------------
            */
            $orden = OrdenPago::lockForUpdate()
            ->findOrFail($ordenPago->id);

            if ((int) $orden->estado_id === 2) {
                throw new \Exception('La orden de pago ya se encuentra anulada.');
            }

            if ((int) $orden->origen_id === 0 ||(int) $orden->tipo_egreso_id !== 7) {
                throw new \Exception('La orden no corresponde a una solicitud de ayuda social.');
            }

            /*
            |--------------------------------------------------------------
            | OBTENER SOLICITUD
            |--------------------------------------------------------------
            */
            $solicitud = SolicitudAyudaSocial::whereKey($orden->origen_id)
            ->where('orden_pago_id', $orden->id)
            ->lockForUpdate()
            ->first();

            if (!$solicitud) {
                throw new \Exception('No se encontró la solicitud vinculada a la orden.');
            }

            if ((int) $solicitud->estado_solicitud_id !== 3) {
                throw new \Exception(
                    'La solicitud no se encuentra aprobada.'
                );
            }
            /*
            |--------------------------------------------------------------
            | COPIAR DETALLES ANTES DE ANULARLOS
            |--------------------------------------------------------------
            */
            $detallesAnteriores = OrdenPagoDetalle::where('orden_pago_id',$orden->id)
            ->where('estado_id', 1)
            ->get();

            if ($detallesAnteriores->isEmpty()) {
                throw new \Exception('La orden de pago no posee detalles activos.');
            }
            $estabaPagada = (int) $orden->estado_pago === 1;
            /*
            |--------------------------------------------------------------
            | ANULAR ORDEN ANTERIOR
            |--------------------------------------------------------------
            */
            $this->anularOrdenBase($orden, $motivo, $usuarioId);
            /*
            |--------------------------------------------------------------
            | REVERTIR TESORERÍA
            |--------------------------------------------------------------
            */
            if ($estabaPagada) {
                $this->revertirTesoreria($orden);
            }
            /*
            |--------------------------------------------------------------
            | OBTENER NUEVA NUMERACIÓN
            |--------------------------------------------------------------
            */
            $anio = (int) now()->year;
            $numero = $this->obtenerNumeroOrdenPago($anio);

            $observacion = trim(($orden->observacion ? $orden->observacion.' | ': '') .'Reemitida por anulación de la orden ' .str_pad($orden->numero, 7, '0', STR_PAD_LEFT).'/'.$orden->anio);

            /*
            |--------------------------------------------------------------
            | GENERAR NUEVA ORDEN
            |--------------------------------------------------------------
            */
            $nuevaOrden = OrdenPago::create([
                'anio' => $anio,
                'numero' => $numero,
                'fecha' => now()->toDateString(),
                'tipo_egreso_id' => $orden->tipo_egreso_id,
                'origen_id' => $solicitud->id,
                'persona_id' => $orden->persona_id,
                'beneficiario' => $orden->beneficiario,
                'concepto' => $orden->concepto,
                'observacion' => mb_substr(
                    $observacion,
                    0,
                    500
                ),
                'total' => $orden->total,
                'estado_id' => 1,
                'estado_pago' => 0,
                'motivo_anulado' => null,
                'fecha_anulado' => null,
                'fecha_pago' => null,
                'user_id' => $usuarioId,
                'usuario_modificacion' => $usuarioId,
            ]);

            /*
            |--------------------------------------------------------------
            | COPIAR DETALLES
            |--------------------------------------------------------------
            */
            foreach ($detallesAnteriores as $detalle) {
                OrdenPagoDetalle::create([
                    'orden_pago_id' => $nuevaOrden->id,
                    'descripcion' => $detalle->descripcion,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    'subtotal' => $detalle->subtotal,
                    'estado_id' => 1,
                    'user_id' => $usuarioId,
                    'usuario_modificacion' => $usuarioId,
                ]);
            }

            /*
            |--------------------------------------------------------------
            | VINCULAR LA NUEVA ORDEN
            |--------------------------------------------------------------
            */
            $solicitud->update([
                'orden_pago_id' => $nuevaOrden->id,
                'estado_solicitud_id' => 3,
                'usuario_modificacion' => $usuarioId,
            ]);

            return $nuevaOrden;
        });
    }

    private function obtenerNumeroOrdenPago(int $anio): int
    {
        $numeracion = Numeraciones::where('tipo', 3)
        ->where('anio', $anio)
        ->lockForUpdate()
        ->first();

        if (!$numeracion) {
            Numeraciones::create([
                'tipo' => 3,
                'anio' => $anio,
                'descripcion' => 'Orden de Pago',
                'numero' => 2,
            ]);

            return 1;
        }

        $numero = (int) $numeracion->numero;
        $numeracion->numero = $numero + 1;
        $numeracion->save();

        return $numero;
    }

    public function anularAyudaSocialCompleta(OrdenPago $ordenPago, string $motivo, int $usuarioId): void
    {
        DB::transaction(function () use ($ordenPago, $motivo,$usuarioId) {
            /*
            |--------------------------------------------------------------
            | BLOQUEAR ORDEN
            |--------------------------------------------------------------
            */
            $orden = OrdenPago::lockForUpdate()
            ->findOrFail($ordenPago->id);

            if ((int) $orden->estado_id === 2) {
                throw new \Exception('La orden de pago ya se encuentra anulada.');
            }

            if ((int) $orden->origen_id === 0 || (int) $orden->tipo_egreso_id !== 7) {
                throw new \Exception('La orden no corresponde a una solicitud de ayuda social.');
            }
            /*
            |--------------------------------------------------------------
            | BUSCAR Y BLOQUEAR SOLICITUD
            |--------------------------------------------------------------
            */
            $solicitud = SolicitudAyudaSocial::whereKey($orden->origen_id)
            ->where('orden_pago_id', $orden->id)
            ->lockForUpdate()
            ->first();

            if (!$solicitud) {
                throw new \Exception('No se encontró la solicitud vinculada a la orden.');
            }

            if ((int) $solicitud->estado_solicitud_id !== 3) {
                throw new \Exception('La solicitud no se encuentra aprobada.');
            }
            $estabaPagada = (int) $orden->estado_pago === 1;
            /*
            |--------------------------------------------------------------
            | ANULAR ORDEN, DETALLES Y PAGOS
            |--------------------------------------------------------------
            */
            $this->anularOrdenBase($orden,$motivo,$usuarioId);
            /*
            |--------------------------------------------------------------
            | REVERTIR TESORERÍA
            |--------------------------------------------------------------
            */
            if ($estabaPagada) {
                $this->revertirTesoreria($orden);
            }

            /*
            |--------------------------------------------------------------
            | ANULAR SOLICITUD
            |--------------------------------------------------------------
            */

            $solicitud->update([
                'estado_solicitud_id' => 5, // ANULADA
                'fecha_anulacion' => now()->toDateString(),
                'motivo_anulacion' => $motivo,
                'usuario_modificacion' => $usuarioId,
            ]);
        });
    }


}
