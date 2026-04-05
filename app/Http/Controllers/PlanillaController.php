<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanillaController extends Controller
{
    public function index(Request $request)
    {

        $tipo = $request->tipo_asociado_id;
        $mes  = $request->mes;
        $anio = $request->anio;

        $data = DB::table('planillas as p')
        ->select('p.*')
        ->when($tipo && $tipo != 0, function ($q) use ($tipo) {
            if ($tipo == 3) {
                $q->where('p.tipo_asociado_id', 3);
            } else {
                $q->whereIn('p.tipo_asociado_id', [1, 2]);
            }
        })
        ->when($mes && $mes != 0, fn($q) => $q->where('p.mes', $mes))
        ->when($anio, fn($q) => $q->where('p.anio', $anio))
        ->orderByDesc('p.anio')
        ->orderByDesc('p.mes')
        ->paginate(10);

        return view('planilla.index', compact('data'));
    }

    public function create()
    {
        return view('planilla.create');
    }
}
