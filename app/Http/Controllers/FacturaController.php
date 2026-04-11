<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use App\Models\Factura;
use App\Models\FacturaAporte;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FacturaController extends Controller
{

    public function index(Request $request)
    {
        $estado = $request->estado ?? 0;
        $fecha_desde = $request->fecha_desde 
        ? Carbon::parse($request->fecha_desde)->toDateString()
        : now()->toDateString();

        $fecha_hasta = $request->fecha_hasta 
        ? Carbon::parse($request->fecha_hasta)->toDateString()
        : now()->toDateString();

        $data = Factura::query()
        ->whereBetween('fecha_factura', [$fecha_desde, $fecha_hasta])
        ->when($estado != 0, fn($q) => $q->where('estado_id', $estado))
        ->orderByDesc('factura_sucursal')
        ->orderByDesc('factura_general')
        ->orderByDesc('factura_numero')
        ->paginate(50);

        return view('factura.index', compact('data', 'fecha_desde', 'fecha_hasta'));
    }

    public function show(Factura $factura)
    {
        $entidad = Entidad::find(1);
        $data = [];
        if($factura->tipo_factura_id == 1){
            $detalle = FacturaAporte::where('factura_id', $factura->id)->first();
            $PlanillaId = str_pad($detalle->planilla_numero, 5, '0', STR_PAD_LEFT) .'/'. $detalle->planilla_anio;
            $descripcion = "APORTE {$factura->mes}/{$factura->anio} PLANILLA N° {$PlanillaId}";

            $data = collect([
                (object)[
                    'descripcion' => $descripcion,
                    'cantidad' => 1,
                    'precio' => $factura->monto_total,
                    'exento' => $factura->monto_total,
                    'grabado_5' => 0,
                    'grabado_10' => 0,
                    'iva_10' => 0,
                    'iva_5' => 0,
                    'total' => $factura->monto_total,
                ]
            ]);
        }
        
        return view('factura.show', compact('factura', 'entidad', 'data'));
    }
}
