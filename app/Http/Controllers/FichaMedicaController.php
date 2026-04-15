<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\FichaMedica;
use Illuminate\Http\Request;

class FichaMedicaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ficha_medica.create')->only(['create', 'store']);
    }
    public function create(Asociado $asociado)
    {
        $data = $asociado;
        
        return view('asociados.ficha', compact('data'));
    }

    public function store(Asociado $asociado, Request $request)
    {

        $request->validate([
            'otro_enfermedad' => 'nullable|string|max:255',
            'medicamentos' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:500',
        ]);
        $cancer = $request->has('cancer') ? 1 : 0;
        $diabetes = $request->has('diabetes') ? 1 : 0;
        $presion_alta = $request->has('presion_alta') ? 1 : 0;

        $ips = $request->has('seguro_ips') ? 1 : 0;
        $particular = $request->has('seguro_particular') ? 1 : 0;
        $ninguno = $request->has('seguro_ninguno') ? 1 : 0;

        FichaMedica::UpdateOrCreate([
            'asociado_id' => $asociado->id
        ],
        [
            'cancer' => $cancer,
            'diabetes' => $diabetes,
            'presion_alta' => $presion_alta,
            'otro' => $request->otro_enfermedad,
            'medicamentos' => $request->medicamentos,
            'seguro_particular' => $particular,
            'seguro_ips' => $ips,
            'seguro_ninguno' => $ninguno,
            'observacion' => $request->observacion,
            'estado_id' => 1,
            'user_id' => auth()->id(),
            'usuario_modificacion' => auth()->id(),
        ]);

        return redirect()->route('asociado.index')->with('message', 'Ficha Medica actualizada.');
    }
}
