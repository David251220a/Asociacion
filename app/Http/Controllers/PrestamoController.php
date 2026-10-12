<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use App\Models\EstadoPrestamo;
use App\Models\Prestamo;
use App\Models\PrestamoPago;
use App\Models\TipoPrestamo;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrestamoController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:prestamos.index')->only('index');
        $this->middleware('permission:prestamos.show')->only('show');
        $this->middleware('permission:prestamos.contrato')->only('contrato');
    }

    public function index(Request $request)
    {
        $data = Prestamo::query()
        ->with(['persona','tipoPrestamo','estadoPrestamo','solicitud',])
        ->when(
            $request->filled('documento'),
            function ($query) use ($request) {
                $query->whereHas('persona',
                    function ($persona) use ($request) {
                        $persona->where('documento','like','%' . trim($request->documento) . '%');
                    }
                );
            }
        )
        ->when(
            $request->filled('numero'),
            function ($query) use ($request) {
                $query->where('numero_prestamo', (int) $request->numero);
            }
        )
        ->when(
            $request->filled('anio'),
            function ($query) use ($request) {
                $query->where('anio',(int) $request->anio);
            }
        )
        ->when(
            $request->filled('tipo_prestamo_id'),
            function ($query) use ($request) {
                $query->where('tipo_prestamo_id',$request->tipo_prestamo_id);
            }
        )
        ->when(
            $request->filled('estado_prestamo_id'),
            function ($query) use ($request) {
                $query->where('estado_prestamo_id',$request->estado_prestamo_id);
            }
        )
        ->where('estado_id', 1)
        ->orderByDesc('anio')
        ->orderByDesc('numero_prestamo')
        ->paginate(25)
        ->withQueryString();

        $tiposPrestamos = TipoPrestamo::orderBy('descripcion')
        ->get();

        $estadosPrestamos = EstadoPrestamo::orderBy('id')
        ->get();

        return view('prestamo.index',compact('data','tiposPrestamos','estadosPrestamos'));
    }

    public function show(Prestamo $prestamo)
    {
        return view('prestamo.show',$this->prepararEstadoCuenta($prestamo));
    }

    public function imprimir(Prestamo $prestamo)
    {
        $datos = $this->prepararEstadoCuenta($prestamo);

        /*
        |--------------------------------------------------------------------------
        | ENTIDAD
        |--------------------------------------------------------------------------
        */
        $datos['entidad'] = Entidad::find(1);

        $numeroPrestamo = $prestamo->numero_prestamo ?? $prestamo->numero;
        $nombreArchivo ='estado-cuenta-prestamo-'. str_pad($numeroPrestamo,5,'0',STR_PAD_LEFT). '-'. $prestamo->anio. '.pdf';
        $pdf = Pdf::loadView('prestamo.imprimir',$datos)->setPaper('a4', 'landscape');
        return $pdf->stream($nombreArchivo);
    }

    public function contrato(Prestamo $prestamo)
    {
        $prestamo->load([
            'persona',
            'tipoPrestamo',
            'estadoPrestamo',
            'solicitud',
            'detalles' => function ($query) {
                $query->where('estado_id', 1)
                ->orderBy('numero_cuota');
            },
        ]);

        $persona = $prestamo->persona ?? optional($prestamo->solicitud)->persona;

        if (!$persona) {
            return redirect()->route('prestamos.show', $prestamo->id)->with('error','No se encontraron los datos de la persona.');
        }

        $entidad = Entidad::find(1);
        $numeroPrestamo = $prestamo->numero_prestamo ?? $prestamo->numero;
        $fechaContrato = $prestamo->fecha_desembolso ?? optional($prestamo->solicitud)->fecha_aprobacion_rechazo ?? now()->toDateString();
        $fechaContrato = Carbon::parse($fechaContrato);
        $montoCapital = (int) $prestamo->monto_capital;
        $montoInteres = (int) $prestamo->monto_interes;
        $montoIva = (int) ($prestamo->monto_iva ?? $prestamo->iva ?? 0);
        $montoTotal = (int) $prestamo->monto_total;
        $cantidadCuotas = $prestamo->detalles->count();
        $tasaAplicada = optional($prestamo->solicitud)->tasa_aplicada ?? $prestamo->tasa_interes ?? 0;
        $montoLetras = $this->numeroEnLetras($montoCapital);
        $resolucion = $prestamo->resolucion ?? optional($prestamo->solicitud)->resolucion ?? 'N/A';
        $nombreArchivo = 'contrato-prestamo-'. str_pad($numeroPrestamo,5,'0',STR_PAD_LEFT). '-'. $prestamo->anio. '.pdf';

        $pdf = Pdf::loadView('prestamo.contrato',
            compact(
                'prestamo',
                'persona',
                'entidad',
                'numeroPrestamo',
                'fechaContrato',
                'montoCapital',
                'montoInteres',
                'montoIva',
                'montoTotal',
                'cantidadCuotas',
                'tasaAplicada',
                'montoLetras',
                'resolucion'
            )
        )->setPaper('a4', 'portrait');

        return $pdf->stream($nombreArchivo);
    }

    private function numeroEnLetras(int $numero): string
    {
        if (class_exists(\NumberFormatter::class)) {
            $formateador = new \NumberFormatter('es',\NumberFormatter::SPELLOUT);
            $resultado = $formateador->format($numero);
            if ($resultado !== false) {
                return mb_strtoupper($resultado . ' GUARANÍES','UTF-8');
            }
        }

        return number_format($numero,0, ',', '.') . ' GUARANÍES';
    }

    private function prepararEstadoCuenta(Prestamo $prestamo): array
    {
        /*
        |--------------------------------------------------------------------------
        | DATOS DEL PRÉSTAMO
        |--------------------------------------------------------------------------
        */
        $prestamo->load([
            'persona',
            'tipoPrestamo',
            'estadoPrestamo',
            'solicitud',
            'detalles' => function ($query) {
                $query->where('estado_id', 1)
                ->orderBy('numero_cuota')
                ->orderBy('id');
            },
        ]);

        /*
        |--------------------------------------------------------------------------
        | GENERACIONES DE PLANILLA POR CUOTA
        |--------------------------------------------------------------------------
        */
        $detalleIds = $prestamo->detalles->pluck('id');

        $planillasPorCuota = DB::table('planilla_prestamos as pp')
        ->join('planilla_detalles as pd','pd.id','=','pp.planilla_detalle_id')
        ->join('planillas as pl','pl.id','=','pd.planilla_id')
        ->where('pp.prestamo_id', $prestamo->id)
        ->whereIn('pp.prestamo_detalle_id', $detalleIds)
        ->where('pp.estado_id', 1)
        ->where('pd.estado_id', 1)
        ->where('pl.estado_id', 1)
        ->select([
            'pp.id as planilla_prestamo_id',
            'pp.prestamo_detalle_id',
            'pp.numero_cuota',
            'pp.monto_total',
            'pp.monto_pagado',
            'pp.saldo',
            'pp.fecha_pago',
            'pp.estado_pago_id',

            'pl.id as planilla_id',
            'pl.planilla_numero',
            'pl.planilla_anio',
            'pl.mes',
            'pl.anio',
            'pl.fecha as fecha_planilla',
            'pl.pagado as planilla_pagada',
            'pl.fecha_pagado as fecha_planilla_pagada',
        ])
        ->orderByDesc('pl.anio')
        ->orderByDesc('pl.mes')
        ->orderByDesc('pp.id')
        ->get()
        ->groupBy('prestamo_detalle_id');

        /*
        |--------------------------------------------------------------------------
        | SITUACIÓN DE CADA CUOTA
        |--------------------------------------------------------------------------
        */
        foreach ($prestamo->detalles as $cuota) {
            $generaciones = $planillasPorCuota->get($cuota->id,collect());
            $saldoTotal = (int) $cuota->saldo_total;
            $estadoPagoId = (int) $cuota->estado_pago_id;
            $generacionActual = $generaciones->first(
                function ($generacion) {
                    return (int) $generacion->estado_pago_id === 5;
                }
            );

            $tuvoGeneracionConSaldo = $generaciones->contains(
                function ($generacion) {
                    return in_array((int) $generacion->estado_pago_id,[3, 6],true);
                }
            );

            if ($saldoTotal <= 0 || $estadoPagoId === 2) {
                $situacion = 'PAGADA';
                $claseSituacion = 'pagada';
            } elseif ($estadoPagoId === 4) {
                $situacion = 'ANULADA';
                $claseSituacion = 'anulada';
            } elseif ($generacionActual) {
                if ($tuvoGeneracionConSaldo) {
                    $situacion = 'GENERADA / ATRASADA';
                    $claseSituacion = 'atrasada';
                } else {
                    $situacion = 'GENERADA';
                    $claseSituacion = 'generada';
                }
            } elseif ($estadoPagoId === 3 || $estadoPagoId === 6 || $tuvoGeneracionConSaldo) {
                $situacion = 'ATRASADA';
                $claseSituacion = 'atrasada';
            } else {
                $situacion = 'PENDIENTE';
                $claseSituacion = 'pendiente';
            }

            $cuota->situacion_calculada = $situacion;
            $cuota->clase_situacion = $claseSituacion;
            $cuota->ultima_generacion = $generaciones->first();
            $cuota->generaciones_planilla = $generaciones;
        }

        /*
        |--------------------------------------------------------------------------
        | MOVIMIENTOS
        |--------------------------------------------------------------------------
        */
        $movimientos = PrestamoPago::where('prestamo_id', $prestamo->id)
        ->where('estado_id', 1)
        ->orderByDesc('fecha_generacion')
        ->orderByDesc('id')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | MORA PENDIENTE
        |--------------------------------------------------------------------------
        */
        $moraPendiente = (int) $prestamo->detalles->sum('monto_mora');
        $moraIvaPendiente = (int) $prestamo->detalles->sum('monto_mora_iva');

        return compact('prestamo','movimientos','moraPendiente','moraIvaPendiente');
    }
}
