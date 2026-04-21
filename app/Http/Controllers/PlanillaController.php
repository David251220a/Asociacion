<?php

namespace App\Http\Controllers;

use App\Exports\PlanillaDetalleExport;
use App\Models\Planilla;
use App\Models\PlanillaDetalle;
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
        DB::transaction(function () use ($planilla) {

            if ($planilla->pagado == 1) {
                throw new \Exception('No se puede anular una planilla pagada.');
            }

            // si tiene lote, anula todo el lote
            if ($planilla->lote_generacion) {

                $planillas = Planilla::where('lote_generacion', $planilla->lote_generacion)
                ->where('estado_id', 1)
                ->get();

                foreach ($planillas as $item) {
                    if ($item->pagado == 1) {
                        throw new \Exception('Existe una planilla pagada en el lote.');
                    }
                }

                foreach ($planillas as $item) {
                    $item->update([
                        'estado_id' => 2,
                        'usuario_modificacion' => auth()->id(),
                    ]);

                    PlanillaDetalle::where('planilla_id', $item->id)
                    ->update([
                        'estado_id' => 2,
                        'usuario_modificacion' => auth()->id(),
                    ]);
                }

                return;
            }

            // fallback (planillas viejas sin lote)
            $planilla->update([
                'estado_id' => 2,
                'usuario_modificacion' => auth()->id(),
            ]);
        });

        return redirect()->route('planilla.index')->with('message', 'Planilla anulada con exito.');
    }

    public function cobrar(Planilla $planilla)
    {
        return view('cobro.planilla', compact('planilla'));
    }
}
