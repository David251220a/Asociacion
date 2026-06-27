<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\OrdenPago;
use App\Models\Recibo;
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
}
