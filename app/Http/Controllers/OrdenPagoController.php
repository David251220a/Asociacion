<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use App\Models\TipoEgreso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function anular(OrdenPago $ordenPago, Request $request)
    {
        $request->validate([
            'motivo_anulacion' => 'required'
        ]);

        if ($ordenPago->estado_id == 2) {
            return redirect()->route('orden.index')->withErrors('La Orden de Pago ya está anulado.');
        }

        try {
            DB::transaction(function () use ($ordenPago, $request) {

                $fecha_anulado = now()->toDateString();
                $restar_tesoreria = 0;
                if ($ordenPago->estado_pago == 1){
                    $restar_tesoreria = 1;
                }

                $ordenPago->update([
                    'estado_id' => 2,
                    'estado_pago' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'fecha_anulado' => $fecha_anulado,
                    'motivo_anulado' => $request->motivo_anulacion,
                ]);

                OrdenPagoDetalle::where('orden_pago_id', $ordenPago->id)->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                $ordenPago->pagos()->update([
                    'estado_id' => 2,
                    'updated_at' => now(),
                ]);

                if ($restar_tesoreria == 1){
                    /*
                    |--------------------------------------------------------------------------
                    | ACTUALIZAR RESUMENES
                    |--------------------------------------------------------------------------
                    */
                    $fechaResumen = Carbon::parse($ordenPago->fecha);
                    $anioResumen = (int) $fechaResumen->year;
                    $mesResumen  = (int) $fechaResumen->month;
                    $montoEgreso = (float) $ordenPago->total;

                    $resumenMensual = ResumenMensual::where('anio', $anioResumen)
                    ->where('mes', $mesResumen)
                    ->where('tipo_egreso_id', $ordenPago->tipo_egreso_id)
                    ->whereNull('tipo_ingreso_id')
                    ->lockForUpdate()
                    ->first();

                    $resumenMensual->total_egreso -= $montoEgreso;
                    $resumenMensual->save();

                    $resumenAnual = ResumenAnual::where('anio', $anioResumen)
                    ->lockForUpdate()
                    ->first();

                    if ($resumenAnual) {
                        $resumenAnual->total_egreso = (float) $resumenAnual->total_egreso - $montoEgreso;
                        $resumenAnual->saldo_final   = (float) $resumenAnual->saldo_inicial + (float) $resumenAnual->total_ingreso - (float) $resumenAnual->total_egreso;
                        $resumenAnual->save();
                    }
                }

            });

        } catch (\Throwable $e) {
            return redirect()->route('orden.index')->withErrors($e->getMessage());
        }

        return redirect()->route('orden.index')->with('message', 'Orden de Pago anulado.');

    }


}
