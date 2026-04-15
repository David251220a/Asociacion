<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\Estado;
use App\Models\Familiar;
use App\Models\Persona;
use App\Models\TipoFamiliar;
use Illuminate\Http\Request;

class FamiliarController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:familiar.create')->only(['create', 'store']);
        $this->middleware('permission:familiar.edit')->only(['edit', 'update']);
        $this->middleware('permission:familiar.delete')->only('delete');
    }

    public function create(Persona $persona)
    {
        $tipo = TipoFamiliar::all();
        return view('familiar.create', compact('tipo', 'persona'));
    }

    public function store(Persona $persona, Request $request)
    {
        $request->validate([
            'documento' => 'required',
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'celular' => 'nullable|string',
            'tipo_familiar' => 'required'
        ]);

        Familiar::create([
            'persona_id' => $persona->id,
            'documento' => str_replace('.', '', $request->documento),
            'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
            'apellido' => mb_strtoupper($request->apellido, 'UTF-8'),
            'celular' => $request->celular,
            'tipo_familiar_id' => $request->tipo_familiar,
            'estado_id' => 1,
            'user_id' => auth()->id(),
            'usuario_modificacion' => auth()->id(),
        ]);

        return redirect()->route('asociado.edit', $persona->asociado)->with('message', 'Familiar creado con exito.');
    }

    public function edit(Familiar $familiar)
    {
        $tipo = TipoFamiliar::all();
        $data = $familiar;
        $estado = Estado::all();
        return view('familiar.edit', compact('tipo', 'data', 'estado'));
    }

    public function update(Familiar $familiar, Request $request)
    {
        $request->validate([
            'documento' => 'required',
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'celular' => 'nullable|string',
            'tipo_familiar' => 'required'
        ]);

        $asociado = Asociado::where('persona_id', $familiar->persona_id)->first();
        $familiar->update([
            'documento' => str_replace('.', '', $request->documento),
            'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
            'apellido' => mb_strtoupper($request->apellido, 'UTF-8'),
            'celular' => $request->celular,
            'tipo_familiar_id' => $request->tipo_familiar,
            'estado_id' => $request->estado_id,
            'user_id' => auth()->id(),
            'usuario_modificacion' => auth()->id(),
        ]);

        return redirect()->route('asociado.edit', $asociado)->with('message', 'Familiar actualizado con exito.');
    }

    public function delete(Familiar $familiar)
    {
        $asociado = Asociado::where('persona_id', $familiar->persona_id)->first();
        $familiar->delete();

        return redirect()->route('asociado.edit', $asociado->id)->with('message', 'Familiar eliminado correctamente.');
    }

    
}
