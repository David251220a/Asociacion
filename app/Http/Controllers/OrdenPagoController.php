<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\TipoEgreso;
use App\Services\AnularOrdenPagoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrdenPagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:orden.index')->only('index');
        $this->middleware('permission:orden.create')->only('create');
        $this->middleware('permission:orden.show')->only('show');
        $this->middleware('permission:orden.pago')->only('pago');
        $this->middleware('permission:orden.anular')->only('anular');
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

    public function anular(OrdenPago $ordenPago, Request $request, AnularOrdenPagoService $servicio)
    {
        $datos = $request->validate([
            'motivo_anulacion' => [
                'required',
                'string',
                'max:1000',
            ],
            'tipo_anulacion' => [
                'required',
                Rule::in([
                    'solo_orden',
                    'reemitir',
                    'completa',
                ]),
            ],
        ]);

        try {

            if ((int) $ordenPago->origen_id > 0 && (int) $ordenPago->tipo_egreso_id === 7 && $datos['tipo_anulacion'] === 'reemitir') {
                $nuevaOrden = $servicio->anularAyudaSocialYReemitir($ordenPago, $datos['motivo_anulacion'], $request->user()->id);
                return redirect()->route('orden.pago', $nuevaOrden->id)->with('message','La orden anterior fue anulada y se generó correctamente una nueva orden de pago.');
            }

            if ((int) $ordenPago->origen_id > 0 && (int) $ordenPago->tipo_egreso_id === 7 && $datos['tipo_anulacion'] === 'completa') {
                $servicio->anularAyudaSocialCompleta($ordenPago, $datos['motivo_anulacion'], $request->user()->id);
                return redirect()->route('orden.index')->with('message','La orden de pago y la solicitud de ayuda social fueron anuladas correctamente.');
            }

            $servicio->anularSinOrigen($ordenPago, $datos['motivo_anulacion'], auth()->user()->id);
            return redirect()->route('orden.index')->with('message','La orden de pago fue anulada correctamente.');

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['orden' => $e->getMessage(),]);
        }
    }


}
