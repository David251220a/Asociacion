<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Entidad;
use App\Models\Planilla;
use App\Models\Recibo;
use App\Models\ReciboAporte;
use App\Models\ReciboDonacion;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use App\Models\TipoRecibo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReciboController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:recibo.index')->only('index');
        $this->middleware('permission:recibo.show')->only('show');
        $this->middleware('permission:recibo.anular')->only('anular');
        $this->middleware('permission:recibo.aporte')->only('aporte');
        $this->middleware('permission:recibo.varios')->only('varios');
    }

    public function index(Request $request)
    {
        $estado = $request->estado ?? 0;
        $tipo_recibo_id = $request->tipo_recibo_id ?? 0;
        $fecha_desde = $request->fecha_desde
        ? Carbon::parse($request->fecha_desde)->toDateString()
        : now()->toDateString();

        $fecha_hasta = $request->fecha_hasta
        ? Carbon::parse($request->fecha_hasta)->toDateString()
        : now()->toDateString();

        $query = Recibo::query()
        ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
        ->when($estado != 0, fn($q) => $q->where('estado_id', $estado))
        ->when($tipo_recibo_id != 0, fn($q) => $q->where('tipo_recibo_id', $tipo_recibo_id));

        $totalQuery = clone $query;

        if ($estado == 0) {
            $totalQuery->where('estado_id', 1); // activo
        }

        $totalGeneral = $totalQuery->sum('monto_total');

        $data = $query
        ->orderByDesc('sucursal')
        ->orderByDesc('general')
        ->orderByDesc('numero')
        ->paginate(50)
        ->withQueryString();

        $tipoRecibos = TipoRecibo::where('estado_id', 1)->get();

        return view('recibo.index', compact('data','fecha_desde','fecha_hasta','estado', 'tipoRecibos','totalGeneral'));
    }

    public function show(Recibo $recibo)
    {
        $entidad = Entidad::find(1);
        $data = collect();
        if (($recibo->tipo_recibo_id == 4) || ($recibo->tipo_recibo_id == 5)) {
            $detalle = ReciboAporte::where('recibo_id', $recibo->id)->first();
            if ($detalle) {
                if ((int) $detalle->planilla === 0) {
                    $planillaId = str_pad($detalle->planilla_numero, 5, '0', STR_PAD_LEFT) . '/' . $detalle->planilla_anio;
                    $descripcion = "APORTE {$detalle->mes}/{$detalle->anio} PLANILLA N° {$planillaId}";
                } else {
                    $descripcion = "APORTE MES " . strtoupper($this->nombreMes($detalle->mes)) . "/{$detalle->anio}";
                }

                $data = collect([
                    (object)[
                        'descripcion' => $descripcion,
                        'cantidad' => 1,
                        'precio' => $recibo->monto_total,
                        'exento' => $recibo->monto_total,
                        'grabado_5' => 0,
                        'grabado_10' => 0,
                        'iva_10' => 0,
                        'iva_5' => 0,
                        'total' => $recibo->monto_total,
                    ]
                ]);
            }
        }

        if ($recibo->tipo_recibo_id == 6) {

            $detalle = ReciboDonacion::where('recibo_id', $recibo->id)->first();

            if ($detalle) {
                $data = collect([
                    (object)[
                        'descripcion' => 'DONACIÓN',
                        'cantidad' => 1,
                        'precio' => $detalle->monto,
                        'exento' => $detalle->monto,
                        'grabado_5' => 0,
                        'grabado_10' => 0,
                        'iva_10' => 0,
                        'iva_5' => 0,
                        'total' => $detalle->monto,
                    ]
                ]);
            }
        }

        return view('recibo.show', compact('recibo', 'entidad', 'data'));
    }

    public function anular(Recibo $recibo)
    {

        if ($recibo->estado_id == 2) {
            return redirect()->route('recibo.index')->with('message', 'El recibo ya está anulado.');
        }

        try {
            DB::transaction(function () use ($recibo) {
                $detalle = ReciboAporte::where('recibo_id', $recibo->id)->first();
                if ($detalle && $detalle->planilla_id) {
                    $planilla = Planilla::find($detalle->planilla_id);

                    if ($planilla) {
                        $planilla->update([
                            'pagado' => 0,
                            'monto_pagado' => 0,
                            'fecha_pagado' => null,
                            'usuario_modificacion' => auth()->id(),
                        ]);

                        $planilla->detalles()->update([
                            'pagado' => 0,
                            'saldo' => DB::raw('monto_esperado'),
                            'usuario_modificacion' => auth()->id(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                $fecha_anulado = now()->toDateString();
                $recibo->update([
                    'estado_id' => 2,
                    'usuario_anulacion' => auth()->id(),
                    'fecha_anulado' => $fecha_anulado,
                    'motivo_anulacion' => 'Recibo incorrecto',
                ]);

                ReciboAporte::where('recibo_id', $recibo->id)->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                $recibo->forma_pagos()->update([
                    'estado_id' => 2,
                    'updated_at' => now(),
                ]);

                $afectadosAporte = Aporte::where('recibo_id', $recibo->id)->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                // Si querés detectar si no encontró nada:
                if (($recibo->tipo_recibo_id == 4) || ($recibo->tipo_recibo_id == 5)){
                    if ($afectadosAporte == 0) {
                        throw new \Exception('No se encontraron aportes relacionados al recibo para anular.');
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR RESUMENES
                |--------------------------------------------------------------------------
                */
                $fechaResumen = Carbon::parse($recibo->fecha);
                $anioResumen = (int) $fechaResumen->year;
                $mesResumen  = (int) $fechaResumen->month;
                $montoIngreso = (float) $recibo->monto_total;

                $resumenMensual = ResumenMensual::where('anio', $anioResumen)
                ->where('mes', $mesResumen)
                ->where('tipo_ingreso_id', $recibo->tipo_recibo_id)
                ->whereNull('tipo_egreso_id')
                ->lockForUpdate()
                ->first();

                $resumenMensual->total_ingreso -= $montoIngreso;
                $resumenMensual->save();

                $resumenAnual = ResumenAnual::where('anio', $anioResumen)
                ->lockForUpdate()
                ->first();

                if ($resumenAnual) {
                    $resumenAnual->total_ingreso = (float) $resumenAnual->total_ingreso - $montoIngreso;
                    $resumenAnual->saldo_final   = (float) $resumenAnual->saldo_inicial
                        + (float) $resumenAnual->total_ingreso
                        - (float) $resumenAnual->total_egreso;
                    $resumenAnual->save();
                }
            });


        } catch (\Throwable $e) {
            return redirect()->route('recibo.index')->with('message',  $e->getMessage());
        }

        return redirect()->route('recibo.index')->with('message', 'Recibo anulado.');
    }

    public function aporte()
    {
        return view('recibo.aporte');
    }

    private function nombreMes($mes)
    {
        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        return $meses[(int) $mes] ?? '';
    }

    public function varios()
    {
        return view('recibo.varios');
    }


}
