<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\OrdenPago;
use App\Models\Recibo;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResumenMensualController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:resumen.index')->only('index');
    }

    public function index(Request $request)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $hoy = Carbon::now();

        $anio = $request->anio ?? $hoy->year;
        $mes  = $request->mes ?? $hoy->month;

        $resumenMes = ResumenMensual::select(
            DB::raw('SUM(total_ingreso) as total_ingreso'),
            DB::raw('SUM(total_egreso) as total_egreso')
        )
        ->where('anio', $anio)
        ->where('mes', $mes)
        ->first();

        $totalIngreso = $resumenMes->total_ingreso ?? 0;
        $totalEgreso  = $resumenMes->total_egreso ?? 0;
        $resultadoMes = $totalIngreso - $totalEgreso;

        $periodoActual = ($anio * 100) + $mes;

        $saldoAnterior = ResumenMensual::select(
                DB::raw('SUM(total_ingreso) - SUM(total_egreso) as saldo')
            )
            ->whereRaw('(anio * 100 + mes) < ?', [$periodoActual])
            ->value('saldo') ?? 0;

        $saldoActual = $saldoAnterior + $resultadoMes;

        $fechaInicio = \Carbon\Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
        $fechaFin    = \Carbon\Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

        $facturas = Factura::select(
            'fecha_factura as fecha',
            DB::raw("'INGRESO' as tipo"),
            DB::raw("'FACTURA' as documento"),
            DB::raw("CONCAT(factura_sucursal,'-',factura_general,'-',LPAD(factura_numero,7,'0')) as numero"),
            'concepto',
            'monto_total as ingreso',
            DB::raw('0 as egreso')
        )
        ->whereBetween('fecha_factura', [$fechaInicio, $fechaFin])
        ->where('estado_id', '<>', 2);

        $recibos = Recibo::select(
            'fecha',
            DB::raw("'INGRESO' as tipo"),
            DB::raw("'RECIBO' as documento"),
            DB::raw("CONCAT(sucursal,'-',general,'-',LPAD(numero,7,'0')) as numero"),
            'concepto',
            'monto_total as ingreso',
            DB::raw('0 as egreso')
        )
        ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->where('estado_id', '<>', 2);

        $egresos = OrdenPago::select(
            'fecha',
            DB::raw("'EGRESO' as tipo"),
            DB::raw("'ORDEN PAGO' as documento"),
            DB::raw("CONCAT(anio, '-', LPAD(numero, 5, '0')) as numero"),
            'concepto',
            DB::raw('0 as ingreso'),
            'total as egreso'
        )
        ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->where('estado_id', '<>', 2)
        ->where('estado_pago', 1);

        $movimientos = $facturas
        ->unionAll($recibos)
        ->unionAll($egresos);

        $detalles = DB::query()
        ->fromSub($movimientos, 'mov')
        ->orderBy('fecha')
        ->orderBy('tipo')
        ->paginate(10)
        ->appends($request->query());

        $graficoSaldoLabels = [];
        $graficoSaldoDatos = [];

        $saldo = $saldoAnterior;

        $dias = Carbon::create($anio, $mes)->daysInMonth;

        for ($dia = 1; $dia <= $dias; $dia++) {

            $fecha = Carbon::create($anio, $mes, $dia)->format('Y-m-d');

            $ingreso = Factura::whereDate('fecha_factura', $fecha)
            ->where('estado_id', '<>', 2)
            ->sum('monto_total');

            $ingreso += Recibo::whereDate('fecha', $fecha)
            ->where('estado_id', '<>', 2)
            ->sum('monto_total');

            $egreso = OrdenPago::whereDate('fecha', $fecha)
            ->where('estado_id', '<>', 2)
            ->where('estado_pago',1)
            ->sum('total');

            $saldo += ($ingreso - $egreso);

            $graficoSaldoLabels[] = $dia;
            $graficoSaldoDatos[] = $saldo;
        }

        $ingresosTipoLabels = [
            'Facturas',
            'Recibos'
        ];

        $ingresosTipoDatos = [
            Factura::whereYear('fecha_factura',$anio)
            ->whereMonth('fecha_factura',$mes)
            ->where('estado_id','<>',2)
            ->sum('monto_total'),

            Recibo::whereYear('fecha',$anio)
            ->whereMonth('fecha',$mes)
            ->where('estado_id','<>',2)
            ->sum('monto_total')
        ];

        $egresos = OrdenPago::join('tipo_egresos','tipo_egresos.id','=','orden_pagos.tipo_egreso_id')
        ->select(
            'tipo_egresos.descripcion',
            DB::raw('SUM(total) as total')
        )
        ->whereYear('fecha',$anio)
        ->whereMonth('fecha',$mes)
        ->where('orden_pagos.estado_id','<>',2)
        ->where('estado_pago',1)
        ->groupBy('tipo_egresos.descripcion')
        ->get();

        $egresosTipoLabels = $egresos->pluck('descripcion');
        $egresosTipoDatos = $egresos->pluck('total');

        return view('resumen.index', compact(
            'meses',
            'anio',
            'mes',
            'totalIngreso',
            'totalEgreso',
            'resultadoMes',
            'saldoAnterior',
            'saldoActual',
            'detalles',
            'graficoSaldoLabels',
            'graficoSaldoDatos',
            'ingresosTipoLabels',
            'ingresosTipoDatos',
            'egresosTipoLabels',
            'egresosTipoDatos'
        ));

    }

    public function recalcular()
    {
        $hoy = Carbon::now();
        $anio = $hoy->year;
        return view('resumen.recalcular', compact('anio'));
    }

    public function recalcular_post(Request $request)
    {
        $request->validate([
            'anio' => [
                'required',
                'integer',
                'min:2020',
                'max:' . now()->year,
            ],
        ]);

        $anio = (int) $request->anio;

        DB::transaction(function () use ($anio) {

            /*
            |--------------------------------------------------------------------------
            | INGRESOS
            |--------------------------------------------------------------------------
            */

            $ingresos = Recibo::query()
            ->selectRaw('
                tipo_recibo_id,
                MONTH(fecha) AS mes,
                SUM(monto_total) AS total_ingreso
            ')
            ->whereYear('fecha', $anio)
            ->where('estado_id', 1)
            ->where('anulado', 0)
            ->groupBy(
                'tipo_recibo_id',
                DB::raw('MONTH(fecha)')
            )
            ->get();

            /*
            |--------------------------------------------------------------------------
            | EGRESOS
            |--------------------------------------------------------------------------
            */

            $egresos = OrdenPago::query()
            ->selectRaw('
                tipo_egreso_id,
                MONTH(fecha_pago) AS mes,
                SUM(total) AS total_egreso
            ')
            ->whereYear('fecha_pago', $anio)
            ->where('estado_id', 1)
            ->where('estado_pago', 1)
            ->whereNull('fecha_anulado')
            ->whereNotNull('fecha_pago')
            ->groupBy(
                'tipo_egreso_id',
                DB::raw('MONTH(fecha_pago)')
            )
            ->get();

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR EL RESUMEN ANTERIOR DEL AÑO
            |--------------------------------------------------------------------------
            */

            ResumenMensual::where('anio', $anio)->delete();

            /*
            |--------------------------------------------------------------------------
            | GUARDAR INGRESOS
            |--------------------------------------------------------------------------
            */

            foreach ($ingresos as $ingreso) {
                ResumenMensual::create([
                    'tipo_movimiento'  => 'I',
                    'tipo_ingreso_id'  => $ingreso->tipo_recibo_id,
                    'tipo_egreso_id'   => null,
                    'anio'             => $anio,
                    'mes'              => $ingreso->mes,
                    'total_ingreso'    => $ingreso->total_ingreso,
                    'total_egreso'     => 0,
                    'fecha_calculo'    => now(),
                    'usuario_calculo'  => auth()->id(),
                    'observacion'      => 'Ingreso recalculado desde recibos',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | GUARDAR EGRESOS
            |--------------------------------------------------------------------------
            */

            foreach ($egresos as $egreso) {
                ResumenMensual::create([
                    'tipo_movimiento'  => 'E',
                    'tipo_ingreso_id'  => null,
                    'tipo_egreso_id'   => $egreso->tipo_egreso_id,
                    'anio'             => $anio,
                    'mes'              => $egreso->mes,
                    'total_ingreso'    => 0,
                    'total_egreso'     => $egreso->total_egreso,
                    'fecha_calculo'    => now(),
                    'usuario_calculo'  => auth()->id(),
                    'observacion'      => 'Egreso recalculado desde órdenes de pago',
                ]);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | RESUMEN ANUAL
        |--------------------------------------------------------------------------
        */

        $totalIngresoAnual = ResumenMensual::where('anio', $anio)
        ->sum('total_ingreso');

        $totalEgresoAnual = ResumenMensual::where('anio', $anio)
        ->sum('total_egreso');

        $resumenActual = ResumenAnual::where('anio', $anio)
        ->lockForUpdate()
        ->first();

        $resumenAnterior = ResumenAnual::where('anio', $anio - 1)
        ->lockForUpdate()
        ->first();

        /*
        * Si existe el año anterior, su saldo final pasa a ser
        * el saldo inicial del año solicitado.
        *
        * Si no existe, conserva el saldo inicial que ya tenía
        * el año actual. Si tampoco existe, comienza en cero.
        */
        $saldoInicial = $resumenAnterior
            ? $resumenAnterior->saldo_final
            : ($resumenActual->saldo_inicial ?? 0);

        $saldoFinal = $saldoInicial
            + $totalIngresoAnual
            - $totalEgresoAnual;

        ResumenAnual::updateOrCreate(
            [
                'anio' => $anio,
            ],
            [
                'saldo_inicial'   => $saldoInicial,
                'total_ingreso'   => $totalIngresoAnual,
                'total_egreso'    => $totalEgresoAnual,
                'saldo_final'     => $saldoFinal,
                'fecha_calculo'   => now(),
                'usuario_calculo' => auth()->user()->name,
                'observacion'     => 'Resumen anual recalculado desde los resúmenes mensuales',
            ]
        );

        return redirect()
            ->back()
            ->with(
                'message',
                'Los ingresos y egresos del año ' . $anio .
                ' fueron recalculados correctamente.'
            );
    }
}
