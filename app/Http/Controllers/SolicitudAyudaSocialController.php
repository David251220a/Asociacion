<?php

namespace App\Http\Controllers;

use App\Models\EstadoSolicitud;
use App\Models\Numeraciones;
use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\SolicitudAyudaSocial;
use App\Models\TipoEgreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudAyudaSocialController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:solicitud.index_ayuda_social')->only('index');
        $this->middleware('permission:solicitud.show_ayuda_social')->only(['show', 'aprobar_ayuda_social', 'rechazar_ayuda_social']);
    }

    public function index(Request $request)
    {
        $menorFechaPendiente = SolicitudAyudaSocial::where('estado_solicitud_id', 1)
        ->min('fecha_solicitud');

        $desde = $request->desde ?? ($menorFechaPendiente ?? now()->format('Y-m-d'));
        $hasta = $request->hasta ?? now()->format('Y-m-d');
        $estado = $request->estado ?? "1";
        $estados_solicitud = EstadoSolicitud::all();
        $data = SolicitudAyudaSocial::where('estado_solicitud_id', $estado)
        ->whereBetween('fecha_solicitud', [$desde, $hasta])
        ->orderBy('created_at', 'ASC')
        ->paginate(50);

        return view('ayuda_social.index', compact('desde', 'hasta', 'data','estados_solicitud'));
    }

    public function show(SolicitudAyudaSocial $solicitudAyuda)
    {
        $data = $solicitudAyuda;
        $persona = $solicitudAyuda->persona;
        return view('ayuda_social.show', compact('data','persona'));
    }

    public function aprobar_ayuda_social(SolicitudAyudaSocial $solicitudAyuda, Request $request)
    {
        $montoLimpio = preg_replace(
            '/[^0-9]/',
            '',
            (string) $request->monto_aprobado
        );

        $request->merge([
            'monto_aprobado' => $montoLimpio !== '' ? $montoLimpio : null,
        ]);

        $datos = $request->validate([
            'monto_aprobado' => [
                'required',
                'numeric',
                'min:1',
            ],
            'observacion' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'monto_aprobado.required' =>
                'Debe ingresar el monto aprobado.',

            'monto_aprobado.numeric' =>
                'El monto aprobado debe ser numérico.',

            'monto_aprobado.min' =>
                'El monto aprobado debe ser mayor a cero.',
        ]);

        try {

            $orden = DB::transaction(function () use ($datos,$solicitudAyuda) {
                $solicitud = SolicitudAyudaSocial::lockForUpdate()
                ->findOrFail($solicitudAyuda->id);

                if (!in_array((int) $solicitud->estado_solicitud_id,[1, 2],true)) {
                    throw new \Exception('La solicitud ya fue resuelta anteriormente.');
                }
                $solicitud->estado_solicitud_id = 3;
                $solicitud->monto_aprobado = $datos['monto_aprobado'];
                $solicitud->fecha_resolucion = now();
                $solicitud->motivo_rechazo = null;
                $solicitud->observacion = $datos['observacion'] ?? null;
                $solicitud->usuario_modificacion = auth()->id();

                /*
                |--------------------------------------------------------------------------
                | ORDEN DE PAGO - GENERAR AUTOMATICAMENTE
                |--------------------------------------------------------------------------
                */
                $anio = now()->year;
                $numeracion = Numeraciones::where('tipo', 3)
                ->where('anio', $anio)
                ->lockForUpdate()
                ->first();

                if (!$numeracion) {
                    $numero = 1;

                    Numeraciones::create([
                        'tipo' => 3,
                        'anio' => $anio,
                        'descripcion' => 'Orden de Pago',
                        'numero' => 2
                    ]);
                } else {
                    $numero = $numeracion->numero;
                    $numeracion->numero = $numero + 1;
                    $numeracion->save();
                }

                $tipo = TipoEgreso::find(7);
                $nombrePersona = trim($solicitud->persona->nombre. ' '. $solicitud->persona->apellido);

                $orden = OrdenPago::create([
                    'anio' => $anio,
                    'numero' => $numero,
                    'fecha' => now()->toDateString(),
                    'tipo_egreso_id' => $tipo->id,
                    'origen_id' => $solicitud->id,
                    'persona_id' => $solicitud->persona_id,
                    'beneficiario' => $nombrePersona,
                    'concepto' => 'PAGO POR AYUDA SOCIAL A: '. strtoupper($nombrePersona),
                    'observacion' => $datos['observacion'] ?? null,
                    'total' => $datos['monto_aprobado'],
                    'estado_id' => 1,
                    'estado_pago' => 0,
                    'motivo_anulado' => '',
                    'fecha_anulado' => null,
                    'fecha_pago' => null,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);

                OrdenPagoDetalle::create([
                    'orden_pago_id' => $orden->id,
                    'descripcion' => $tipo->descripcion,
                    'cantidad' => 1,
                    'precio' => $datos['monto_aprobado'],
                    'subtotal' => $datos['monto_aprobado'],
                    'estado_id' => 1,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);
                $solicitud->orden_pago_id = $orden->id;
                $solicitud->save();
                return $orden;
            });

            return redirect()
            ->route('orden.pago', $orden->id)
            ->with(
                'message',
                'La solicitud fue aprobada correctamente por G. '
                . number_format(
                    $datos['monto_aprobado'],
                    0,
                    ',',
                    '.'
                )
                . '. Se generó la orden de pago N.º '
                . str_pad(
                    $orden->numero,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
                . '/'
                . $orden->anio
                . ', pendiente de pago.'
            );

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['solicitud' => $e->getMessage(),]);
        }
    }

    public function rechazar_ayuda_social(SolicitudAyudaSocial $solicitudAyuda, Request $request)
    {
        $datos = $request->validate([
            'motivo_rechazo' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
        ], [
            'motivo_rechazo.required' =>
                'Debe indicar el motivo del rechazo.',

            'motivo_rechazo.min' =>
                'El motivo del rechazo debe contener al menos 10 caracteres.',
        ]);

        try {
            DB::transaction(function () use ($datos, $solicitudAyuda) {
                $solicitud = SolicitudAyudaSocial::lockForUpdate()
                ->findOrFail($solicitudAyuda->id);

                if (!in_array((int) $solicitud->estado_solicitud_id,[1, 2],true)) {
                    throw new \Exception('La solicitud ya fue resuelta anteriormente.');
                }

                $solicitud->estado_solicitud_id = 4;
                $solicitud->monto_aprobado = 0;
                $solicitud->fecha_resolucion = now();
                $solicitud->motivo_rechazo = trim($datos['motivo_rechazo']);
                $solicitud->usuario_modificacion = auth()->id();
                $solicitud->save();
            });

            return redirect()->route('solicitud.show_ayuda_social',$solicitudAyuda->id)->with('message','La solicitud de ayuda social fue rechazada correctamente.');

        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['solicitud' => $e->getMessage(),]);
        }
    }

}
