<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\TipoEgreso;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdenPagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:orden.index')->only('index');
        $this->middleware('permission:orden.create')->only('create');
        $this->middleware('permission:orden.show')->only('show');
        $this->middleware('permission:orden.pago')->only('pago');
    }

    public function index(Request $request)
    {
        $estado = $request->estado ?? 9;
        $tipo_egrego_id = $request->tipo_egrego_id ?? 0;

        if($request->fecha_desde){
            $fecha_desde = $request->fecha_desde;
        }else{
            $fecha_desde = OrdenPago::where('estado_id', 1)
            ->where('estado_pago', 0)
            ->min('fecha');

            if(empty($fecha_desde)){
                $fecha_desde = now()->toDateString();
            }
        }

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

    public function show(OrdenPago $ordenPago)
    {
        $data = $ordenPago;
        return view('orden.show', compact('data'));
    }

    public function pago(OrdenPago $ordenPago)
    {
        return view('orden.pago', compact('ordenPago'));
    }


}
