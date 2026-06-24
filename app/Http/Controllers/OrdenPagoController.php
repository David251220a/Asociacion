<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\TipoEgreso;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdenPagoController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->estado ?? 9;
        $tipo_egrego_id = $request->tipo_egrego_id ?? 0;

        $fecha_desde = $request->fecha_desde
        ? Carbon::parse($request->fecha_desde)->toDateString()
        : now()->toDateString();

        $fecha_hasta = $request->fecha_hasta
        ? Carbon::parse($request->fecha_hasta)->toDateString()
        : now()->toDateString();

        $tipo_egresos = TipoEgreso::all();

        $query = OrdenPago::whereBetween('fecha', [$fecha_desde, $fecha_hasta]);

        if($tipo_egrego_id > 0){
            $query->where('tipo_egreso_id', $tipo_egrego_id);
        }

        if ($estado <> 9){
            $query->where('estado_pago', $estado);
        }

        $totalQuery = clone $query;

        $totalGeneral = $totalQuery->sum('total');

        $data = $query
        ->orderByDesc('anio')
        ->orderByDesc('numero')
        ->paginate(50)
        ->withQueryString();

        return view('orden.index', compact('fecha_desde','fecha_hasta','estado','tipo_egresos','totalGeneral','data'));
    }

    public function create()
    {
        return view('orden.create');
    }

    public function pago(OrdenPago $ordenPago)
    {
        return $ordenPago;
    }


}
