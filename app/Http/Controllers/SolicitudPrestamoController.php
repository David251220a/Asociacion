<?php

namespace App\Http\Controllers;

use App\Models\EstadoSolicitud;
use App\Models\Numeraciones;
use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\SolicitudConfig;
use App\Models\SolicitudPrestamo;
use App\Models\SolicitudPrestamoDetalle;
use App\Models\TipoEgreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudPrestamoController extends Controller
{
    public function prestamo_emergencia(Request $request)
    {
        $estados_solicitud = EstadoSolicitud::all();
        $menorFechaPendiente = SolicitudPrestamo::where('estado_solicitud_id', 1)
        ->min('fecha_solicitud');
        $desde = $request->desde ?? ($menorFechaPendiente ?? now()->format('Y-m-d'));
        $hasta = $request->hasta ?? now()->format('Y-m-d');
        $estado = $request->estado ?? "1";
        $data = SolicitudPrestamo::where('estado_solicitud_id', $estado)
        ->whereBetween('fecha_solicitud', [$desde, $hasta])
        ->orderBy('created_at', 'ASC')
        ->paginate(50);
        return view('solicitud.prestamo_emergencia', compact('estados_solicitud','desde','hasta','estado','data'));
    }

    public function prestamo_emergencia_show(SolicitudPrestamo $solicitudPrestamo)
    {
        $data = $solicitudPrestamo->load([
            'persona.asociado',
            'estadoSolicitud',
            'detalles' => function ($query) {
                $query->orderBy('numero_cuota');
            },
            'ordenPago',
        ]);

        $persona = $data->persona;
        return view('solicitud.prestamo_emergencia_show', compact('data', 'persona'));
    }

    public function prestamo_emergencia_aprobar(Request $request, SolicitudPrestamo $solicitudPrestamo)
    {
        $montoLimpio = str_replace('.','',(string) $request->monto_aprobado);
        $request->merge(['monto_aprobado' => $montoLimpio !== '' ? $montoLimpio : null,]);

        $datos = $request->validate([
            'monto_aprobado' => ['required','integer','min:1',],
            'observaciones' => ['nullable','string','max:500',],
        ], [
            'monto_aprobado.required' => 'Debe ingresar el monto aprobado.',
            'monto_aprobado.integer' => 'El monto aprobado debe ser un número válido.',
            'monto_aprobado.min' => 'El monto aprobado debe ser mayor a cero.',
            'observaciones.max' => 'La observación no puede superar los 500 caracteres.',
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | EL RETURN DE LA TRANSACCIÓN DEVUELVE LA ORDEN CREADA
            |--------------------------------------------------------------------------
            */
            $orden = DB::transaction(function () use ($solicitudPrestamo,$datos) {

                $usuarioId = auth()->id();
                $fechaAprobacion = now();
                $anio = $fechaAprobacion->year;

                $solicitud = SolicitudPrestamo::query()
                ->lockForUpdate()
                ->findOrFail($solicitudPrestamo->id);

                if ((int) $solicitud->estado_id !== 1) {
                    throw new \Exception('La solicitud de préstamo no se encuentra activa.');
                }

                if (!in_array((int) $solicitud->estado_solicitud_id, [1, 2],true)) {
                    throw new \Exception('La solicitud de préstamo ya fue resuelta anteriormente.');
                }

                if ($solicitud->orden_pago_id) {
                    throw new \Exception('La solicitud ya tiene una orden de pago registrada.');
                }

                if ($solicitud->prestamos_id) {
                    throw new \Exception('La solicitud ya tiene un préstamo generado.');
                }

                $montoSolicitado = (int) $solicitud->monto_solicitado;
                $montoAprobado = (int) $datos['monto_aprobado'];

                if ($montoAprobado > $montoSolicitado) {
                    throw new \Exception('El monto aprobado no puede superar el monto solicitado.');
                }

                /*
                |--------------------------------------------------------------------------
                | OBTENER CUOTAS DE LA SOLICITUD
                |--------------------------------------------------------------------------
                */
                $detallesSolicitud = SolicitudPrestamoDetalle::query()
                ->where('solicitud_prestamo_id',$solicitud->id)
                ->orderBy('numero_cuota')
                ->lockForUpdate()
                ->get();

                if ($detallesSolicitud->isEmpty()) {
                    throw new \Exception('La solicitud no tiene cuotas registradas.');
                }

                $cantidadCuotas = (int) $solicitud->cantidad_cuotas;
                if ($detallesSolicitud->count() !== $cantidadCuotas) {
                    throw new \Exception('La cantidad de cuotas de la solicitud no coincide con su detalle.');
                }
                /*
                |--------------------------------------------------------------------------
                | CALCULAR IMPORTES DEFINITIVOS
                |--------------------------------------------------------------------------
                |
                | Si el monto aprobado es menor al solicitado, los intereses
                | y el IVA se recalculan proporcionalmente.
                |
                */
                $interesSolicitado = (int) $detallesSolicitud->sum('monto_interes');
                $ivaSolicitado = (int) $detallesSolicitud->sum('iva');

                if ($montoSolicitado <= 0) {
                    throw new \Exception('El monto original de la solicitud no es válido.');
                }

                $montoInteres = (int) round(($interesSolicitado * $montoAprobado) / $montoSolicitado);
                $montoIva = (int) round(($ivaSolicitado * $montoAprobado) / $montoSolicitado);
                $montoTotal = $montoAprobado + $montoInteres + $montoIva;
                /*
                |--------------------------------------------------------------------------
                | CONFIGURACIÓN DE PRÉSTAMOS
                |--------------------------------------------------------------------------
                */
                $config = SolicitudConfig::findOrFail(2);
                /*
                |--------------------------------------------------------------------------
                | NÚMERO DEL PRÉSTAMO
                |--------------------------------------------------------------------------
                |
                | Ajustá el tipo 7 si utilizás otro identificador para préstamos.
                |
                */
                $numeroPrestamo = $this->generarNumero(7,$anio,'Préstamo');
                /*
                |--------------------------------------------------------------------------
                | CREAR CABECERA DEL PRÉSTAMO
                |--------------------------------------------------------------------------
                */
                $prestamo = Prestamo::create([
                    'anio' => $anio,
                    'numero_prestamo' => $numeroPrestamo,
                    'fecha_prestamo' => $fechaAprobacion->toDateString(),
                    'persona_id' => $solicitud->persona_id,
                    'tipo_prestamo_id' => $solicitud->tipo_prestamo_id,
                    'monto_capital' => $montoAprobado,
                    'monto_interes' => $montoInteres,
                    'monto_iva' => $montoIva,
                    'monto_total' => $montoTotal,
                    'monto_capital_pagado' => 0,
                    'monto_interes_pagado' => 0,
                    'monto_iva_pagado' => 0,
                    'monto_pagado' => 0,
                    'saldo_capital' => $montoAprobado,
                    'saldo_interes' => $montoInteres,
                    'saldo_iva' => $montoIva,
                    'saldo_total' => $montoTotal,
                    'tasa_aplicada' => $solicitud->tasa_aplicada,
                    'tasa_mora' => $config->tasa_mora,
                    'cantidad_cuotas' => $cantidadCuotas,
                    'orden_pago_id' => null,
                    'fecha_desembolso' => null,
                    'fecha_cancelacion' => null,
                    'estado_prestamo_id' => 1,
                    'observaciones' => $datos['observaciones'] ?? null,
                    'estado_id' => 1,
                    'usuario_id' => $usuarioId,
                    'usuario_modificacion' => $usuarioId,
                ]);

                /*
                |--------------------------------------------------------------------------
                | GENERAR DETALLE DEFINITIVO DEL PRÉSTAMO
                |--------------------------------------------------------------------------
                */

                $capitalBase = intdiv($montoAprobado,$cantidadCuotas);
                $interesBase = intdiv($montoInteres,$cantidadCuotas);
                $ivaBase = intdiv($montoIva,$cantidadCuotas);
                $capitalAcumulado = 0;
                $interesAcumulado = 0;
                $ivaAcumulado = 0;

                foreach ($detallesSolicitud as $detalleSolicitud) {
                    $numeroCuota = (int) $detalleSolicitud->numero_cuota;
                    $ultimaCuota = $numeroCuota === $cantidadCuotas;
                    $capital = $ultimaCuota ? $montoAprobado - $capitalAcumulado : $capitalBase;
                    $interes = $ultimaCuota ? $montoInteres - $interesAcumulado : $interesBase;
                    $iva = $ultimaCuota ? $montoIva - $ivaAcumulado : $ivaBase;
                    $montoCuota = $capital + $interes;
                    $totalCuota = $montoCuota + $iva;
                    PrestamoDetalle::create([
                        'prestamo_id' => $prestamo->id,
                        'numero_cuota' => $numeroCuota,
                        'fecha_vencimiento' => $fechaAprobacion->copy()->addMonthsNoOverflow($numeroCuota)->endOfMonth()->toDateString(),
                        'monto_capital' => $capital,
                        'monto_interes' => $interes,
                        'monto_cuota' => $montoCuota,
                        'monto_iva' => $iva,
                        'monto_total' => $totalCuota,
                        'monto_capital_pagado' => 0,
                        'monto_interes_pagado' => 0,
                        'monto_iva_pagado' => 0,
                        'monto_pagado' => 0,
                        'saldo_capital' => $capital,
                        'saldo_interes' => $interes,
                        'saldo_iva' => $iva,
                        'saldo_total' => $totalCuota,
                        'monto_mora' => 0,
                        'monto_mora_iva' => 0,
                        'fecha_pago' => null,
                        'fecha_cancelacion' => null,
                        'fecha_ultimo_calculo_mora' => null,
                        'estado_pago_id' => 1,
                        'estado_id' => 1,
                        'usuario_id' => $usuarioId,
                        'usuario_modificacion' => $usuarioId,
                    ]);
                    $capitalAcumulado += $capital;
                    $interesAcumulado += $interes;
                    $ivaAcumulado += $iva;
                }
                /*
                |--------------------------------------------------------------------------
                | NÚMERO DE ORDEN DE PAGO
                |--------------------------------------------------------------------------
                */
                $numeroOrden = $this->generarNumero(3,$anio,'Orden de Pago');
                /*
                |--------------------------------------------------------------------------
                | TIPO DE EGRESO
                |--------------------------------------------------------------------------
                */
                $tipoEgreso = TipoEgreso::findOrFail(2);
                $persona = $solicitud->persona;

                if (!$persona) {
                    throw new \Exception('No se encontró la persona vinculada a la solicitud.');
                }

                $nombreCompleto = trim($persona->nombre . ' ' . $persona->apellido);
                /*
                |--------------------------------------------------------------------------
                | CREAR ORDEN DE PAGO
                |--------------------------------------------------------------------------
                |
                | La orden representa el dinero entregado: únicamente el capital
                | aprobado. Los intereses se recuperan posteriormente en las cuotas.
                |
                */
                $orden = OrdenPago::create([
                    'anio' => $anio,
                    'numero' => $numeroOrden,
                    'fecha' => $fechaAprobacion->toDateString(),
                    'tipo_egreso_id' => $tipoEgreso->id,
                    'origen_id' => $solicitud->id,
                    'persona_id' => $solicitud->persona_id,
                    'beneficiario' => $nombreCompleto,
                    'concepto' =>'DESEMBOLSO DE PRÉSTAMO DE EMERGENCIA AL ASOCIADO: ' . mb_strtoupper($nombreCompleto,'UTF-8'),
                    'observacion' => $datos['observaciones'] ?? null,
                    'total' => $montoAprobado,
                    'estado_id' => 1,
                    'estado_pago' => 0,
                    'motivo_anulado' => null,
                    'fecha_anulado' => null,
                    'fecha_pago' => null,
                    'user_id' => $usuarioId,
                    'usuario_modificacion' => $usuarioId,
                ]);

                /*
                |--------------------------------------------------------------------------
                | DETALLE DE ORDEN DE PAGO
                |--------------------------------------------------------------------------
                */
                OrdenPagoDetalle::create([
                    'orden_pago_id' => $orden->id,
                    'descripcion' => $tipoEgreso->descripcion,
                    'cantidad' => 1,
                    'precio' => $montoAprobado,
                    'subtotal' => $montoAprobado,
                    'estado_id' => 1,
                    'user_id' => $usuarioId,
                    'usuario_modificacion' => $usuarioId,
                ]);
                /*
                |--------------------------------------------------------------------------
                | VINCULAR ORDEN AL PRÉSTAMO
                |--------------------------------------------------------------------------
                */
                $prestamo->update([
                    'orden_pago_id' => $orden->id,
                    'usuario_modificacion' => $usuarioId,
                ]);
                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR SOLICITUD
                |--------------------------------------------------------------------------
                */
                $solicitud->update([
                    'estado_solicitud_id' => 3,
                    'monto_aprobado' => $montoAprobado,
                    'orden_pago_id' => $orden->id,
                    'prestamo_id' => $prestamo->id,
                    'observaciones' => $datos['observaciones'] ?? null,
                    'fecha_aprobacion_rechazo' => $fechaAprobacion->toDateString(),
                    'usuario_aprobacion_rechazo_id' => $usuarioId,
                    'motivo_rechazo' => null,
                ]);

                return $orden;
            });

            return redirect()
            ->route(
                'orden.pago',
                $orden->id
            )
            ->with(
                'message',
                'El préstamo fue aprobado correctamente por G. '
                . number_format(
                    (int) $datos['monto_aprobado'],
                    0,
                    ',',
                    '.'
                )
                . '. La orden de pago se encuentra pendiente.'
            );

        } catch (\Throwable $e) {
            report($e);

            return redirect()
            ->back()
            ->withErrors([
                'solicitud' => $e->getMessage(),
            ])
            ->withInput();
        }
    }

    public function prestamo_emergencia_rechazar(Request $request, SolicitudPrestamo $solicitudPrestamo)
    {
        $datos = $request->validate([
            'motivo_rechazo' => ['required','string','max:1000',],
            'observaciones' => ['nullable','string','max:255',],
        ], [
            'motivo_rechazo.required' => 'Debe indicar el motivo del rechazo.',
            'motivo_rechazo.string' => 'El motivo del rechazo debe ser un texto válido.',
            'motivo_rechazo.max' => 'El motivo del rechazo no puede superar los 1.000 caracteres.',
            'observaciones.max' => 'La observación no puede superar los 255 caracteres.',
        ]);

        try {
            DB::transaction(function () use ($solicitudPrestamo,$datos) {
                $solicitud = SolicitudPrestamo::query()
                ->lockForUpdate()
                ->findOrFail($solicitudPrestamo->id);

                if ((int) $solicitud->estado_id !== 1) {
                    throw new \Exception('La solicitud de préstamo no se encuentra activa.');
                }

                if (!in_array((int) $solicitud->estado_solicitud_id,[1, 2],true)) {
                    throw new \Exception('La solicitud de préstamo ya fue resuelta anteriormente.');
                }

                if ($solicitud->orden_pago_id) {
                    throw new \Exception('La solicitud ya tiene una orden de pago generada.');
                }

                if ($solicitud->prestamos_id) {
                    throw new \Exception('La solicitud ya tiene un préstamo registrado.');
                }

                $solicitud->update([
                    'estado_solicitud_id' => 4, // RECHAZADA.
                    'monto_aprobado' => null,
                    'motivo_rechazo' => mb_strtoupper($datos['motivo_rechazo'],'UTF-8'),
                    'observaciones' => $datos['observaciones'] ?? null,
                    'fecha_aprobacion_rechazo' => now()->toDateString(),
                    'usuario_aprobacion_rechazo_id' => auth()->id(),
                ]);
            });

            return redirect()->back()->with('message','La solicitud de préstamo fue rechazada correctamente.');

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['solicitud' => $e->getMessage(),])->withInput();
        }
    }


    private function generarNumero(int $tipo,int $anio, string $descripcion): int {
        $numeracion = Numeraciones::query()
        ->where('tipo', $tipo)
        ->where('anio', $anio)
        ->lockForUpdate()
        ->first();

        if (!$numeracion) {
            Numeraciones::create([
                'tipo' => $tipo,
                'anio' => $anio,
                'descripcion' => $descripcion,
                'numero' => 2,
            ]);
            return 1;
        }
        $numero = (int) $numeracion->numero;
        $numeracion->update([
            'numero' => $numero + 1,
        ]);
        return $numero;
    }


}
