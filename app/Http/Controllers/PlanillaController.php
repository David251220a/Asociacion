<?php

namespace App\Http\Controllers;

use App\Exports\PlanillaDetalleExport;
use App\Models\Planilla;
use App\Models\PlanillaAporte;
use App\Models\PlanillaDetalle;
use App\Models\PlanillaPrestamo;
use App\Models\PrestamoDetalle;
use App\Models\PrestamoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PlanillaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:planilla.index')->only('index');
        $this->middleware('permission:planilla.create')->only('create');
        $this->middleware('permission:planilla.exportarDetalle')->only('exportarDetalle');
        $this->middleware('permission:planilla.cobrar')->only('cobrar');
    }

    public function index(Request $request)
    {

        $tipo = $request->tipo_asociado_id;
        $mes  = $request->mes;
        $anio = $request->anio;

        $data = Planilla::with('tipoAsociado')
        ->where('estado_id', 1)
        ->when($tipo && $tipo != 0, function ($q) use ($tipo) {
            if ($tipo == 3) {
                $q->where('tipo_asociado_id', 3);
            } else {
                $q->whereIn('tipo_asociado_id', [1, 2]);
            }
        })
        ->when($mes && $mes != 0, fn($q) => $q->where('mes', $mes))
        ->when($anio, fn($q) => $q->where('anio', $anio))
        ->orderByDesc('anio')
        ->orderByDesc('mes')
        ->paginate(10);

        $ultimasPlanillas = Planilla::where('estado_id', 1)
        ->whereIn('tipo_asociado_id', [1, 3])
        ->orderBy('tipo_asociado_id')
        ->orderByDesc('anio')
        ->orderByDesc('mes')
        ->get()
        ->groupBy('tipo_asociado_id')
        ->map(function ($items) {
            return $items->first()->id;
        });

        return view('planilla.index', compact('data', 'ultimasPlanillas'));
    }

    public function create()
    {
        return view('planilla.create');
    }

    public function exportarDetalle(Planilla $planilla)
    {
        return Excel::download(
            new PlanillaDetalleExport($planilla->id),
            'planilla_detalle_' .
            $planilla->tipoAsociado->descripcion . '_' .
            $planilla->mes . '_' .
            $planilla->anio . '.xlsx'
        );
    }

    public function anular(Planilla $planilla)
    {
        try {
            DB::transaction(function () use ($planilla) {

                /*
                |--------------------------------------------------------------------------
                | OBTENER PLANILLAS A ANULAR
                |--------------------------------------------------------------------------
                */

                if ($planilla->lote_generacion) {
                    $planillas = Planilla::query()
                    ->where('lote_generacion', $planilla->lote_generacion)
                    ->where('estado_id', 1)
                    ->lockForUpdate()
                    ->get();
                } else {
                    $planillas = Planilla::query()
                    ->whereKey($planilla->id)
                    ->where('estado_id', 1)
                    ->lockForUpdate()
                    ->get();
                }

                if ($planillas->isEmpty()) {
                    throw new \Exception('La planilla ya fue anulada o no se encuentra activa.');
                }

                /*
                |--------------------------------------------------------------------------
                | VERIFICAR QUE NINGUNA PLANILLA TENGA COBROS
                |--------------------------------------------------------------------------
                */

                $planillaPagada = $planillas->contains(function ($item) {
                    return (int) $item->pagado === 1 || (int) $item->monto_pagado > 0;
                });

                if ($planillaPagada) {
                    throw new \Exception('No se puede anular el lote porque contiene una planilla con pagos registrados.');
                }

                $planillaIds = $planillas->pluck('id');
                /*
                |--------------------------------------------------------------------------
                | OBTENER DETALLES
                |--------------------------------------------------------------------------
                */

                $detalles = PlanillaDetalle::query()
                ->whereIn('planilla_id', $planillaIds)
                ->where('estado_id', 1)
                ->lockForUpdate()
                ->get();

                $detalleIds = $detalles->pluck('id');

                /*
                |--------------------------------------------------------------------------
                | COMPROBAR PAGOS EN LOS CONCEPTOS
                |--------------------------------------------------------------------------
                |
                | Estado 2: pagado.
                | Estado 3: pagado parcialmente.
                |
                */

                $aporteCobrado = PlanillaAporte::query()
                ->whereIn('planilla_detalle_id', $detalleIds)
                ->where('estado_id', 1)
                ->whereIn('estado_pago_id', [2, 3])
                ->exists();

                $prestamoCobrado = PlanillaPrestamo::query()
                ->whereIn('planilla_detalle_id', $detalleIds)
                ->where('estado_id', 1)
                ->whereIn('estado_pago_id', [2, 3])
                ->exists();

                if ($aporteCobrado || $prestamoCobrado) {
                    throw new \Exception('No se puede anular la planilla porque contiene aportes o préstamos cobrados.');
                }

                /*
                |--------------------------------------------------------------------------
                | OBTENER PRÉSTAMOS ENVIADOS A PLANILLA
                |--------------------------------------------------------------------------
                */

                $planillaPrestamos = PlanillaPrestamo::query()
                ->whereIn('planilla_detalle_id', $detalleIds)
                ->where('estado_id', 1)
                ->lockForUpdate()
                ->get();

                foreach ($planillaPrestamos as $planillaPrestamo) {

                    /*
                    |--------------------------------------------------------------------------
                    | LIBERAR LA CUOTA ORIGINAL
                    |--------------------------------------------------------------------------
                    */

                    $cuota = PrestamoDetalle::query()
                    ->whereKey($planillaPrestamo->prestamo_detalle_id)
                    ->where('estado_id', 1)
                    ->lockForUpdate()
                    ->first();

                    if ($cuota && (int) $cuota->estado_pago_id === 5) {
                        /*
                        * Si anteriormente tuvo algún pago, queda parcial.
                        * En caso contrario, vuelve a pendiente.
                        */
                        $nuevoEstadoPago = (int) $cuota->monto_pagado > 0&& (int) $cuota->saldo_total > 0 ? 3 : 1;

                        $cuota->update([
                            'estado_pago_id' => $nuevoEstadoPago,
                            'usuario_modificacion' => auth()->id(),
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ANULAR ANTECEDENTE EN PRESTAMO_PAGOS
                    |--------------------------------------------------------------------------
                    */

                    PrestamoPago::query()
                    ->where('planilla_prestamo_id',  $planillaPrestamo->id)
                    ->where('estado_id', 1)
                    ->update([
                        'estado_pago_id' => 4,
                        'estado_id' => 2,
                        'usuario_modificacion' => auth()->id(),
                        'updated_at' => now(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | ANULAR PLANILLA PRÉSTAMO
                    |--------------------------------------------------------------------------
                    */

                    $planillaPrestamo->update([
                        'estado_pago_id' => 4,
                        'estado_id' => 2,
                        'usuario_modificacion' => auth()->id(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | ANULAR PLANILLA APORTES
                |--------------------------------------------------------------------------
                */

                PlanillaAporte::query()
                ->whereIn('planilla_detalle_id', $detalleIds)
                ->where('estado_id', 1)
                ->update([
                    'estado_pago_id' => 4,
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | ANULAR DETALLES PRINCIPALES
                |--------------------------------------------------------------------------
                */

                PlanillaDetalle::query()
                ->whereIn('id', $detalleIds)
                ->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | ANULAR CABECERAS
                |--------------------------------------------------------------------------
                */

                Planilla::query()
                ->whereIn('id', $planillaIds)
                ->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);
            });

            return redirect()->route('planilla.index')->with('message','La planilla fue anulada correctamente.');

        } catch (\Throwable $th) {
            report($th);

            return redirect()->back()->withErrors(['planilla' => $th->getMessage(),]);
        }
    }

    // public function anular(Planilla $planilla)
    // {
    //     DB::transaction(function () use ($planilla) {

    //         if ($planilla->pagado == 1) {
    //             throw new \Exception('No se puede anular una planilla pagada.');
    //         }

    //         // si tiene lote, anula todo el lote
    //         if ($planilla->lote_generacion) {

    //             $planillas = Planilla::where('lote_generacion', $planilla->lote_generacion)
    //             ->where('estado_id', 1)
    //             ->get();

    //             foreach ($planillas as $item) {
    //                 if ($item->pagado == 1) {
    //                     throw new \Exception('Existe una planilla pagada en el lote.');
    //                 }
    //             }

    //             foreach ($planillas as $item) {
    //                 $item->update([
    //                     'estado_id' => 2,
    //                     'usuario_modificacion' => auth()->id(),
    //                 ]);

    //                 PlanillaDetalle::where('planilla_id', $item->id)
    //                 ->update([
    //                     'estado_id' => 2,
    //                     'usuario_modificacion' => auth()->id(),
    //                 ]);
    //             }

    //             return;
    //         }

    //         // fallback (planillas viejas sin lote)
    //         $planilla->update([
    //             'estado_id' => 2,
    //             'usuario_modificacion' => auth()->id(),
    //         ]);
    //     });

    //     return redirect()->route('planilla.index')->with('message', 'Planilla anulada con exito.');
    // }

    public function cobrar(Planilla $planilla)
    {
        return view('cobro.planilla', compact('planilla'));
    }
}
