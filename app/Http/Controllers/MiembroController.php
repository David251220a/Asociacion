<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use Illuminate\Http\Request;

class MiembroController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:miembros.index')->only('index');
    }

    public function index()
    {
        $data = Miembro::orderBy('tipo', 'ASC')->get();
        $tipos = ['PRESIDENTE', 'VICEPRESIDENTE', 'SECRETARIO', 'TESORERA', 'PRO-TESORERA', 'MIEMBROS', 'SINDICO'];
        return view('miembro.index', compact('data', 'tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'crear_nombre' => 'required',
            'crear_apellido' => 'required',
            'crear_tipo_miembro' => 'required'
        ]);

        Miembro::create([
            'nombre' => mb_strtoupper($request->crear_nombre, 'UTF-8'),
            'apellido' => mb_strtoupper($request->crear_apellido, 'UTF-8'),
            'tipo' => $request->crear_tipo_miembro,
            'presente' => 1,
        ]);

        return redirect()->route('miembros.index')->with('message', 'Miembro creado correctamente.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'editar_nombre' => 'required',
            'editar_apellido' => 'required',
            'editar_tipo_miembro' => 'required',
            'miembro_id' => 'required',
        ]);

        $miembro = Miembro::find($request->miembro_id);
        $miembro->update([
            'nombre' => mb_strtoupper($request->editar_nombre, 'UTF-8'),
            'apellido' => mb_strtoupper($request->editar_apellido, 'UTF-8'),
            'tipo' => $request->editar_tipo_miembro,
        ]);

        return redirect()->route('miembros.index')->with('message', 'Miembro editado correctamente.');
    }

    public function cambiarPresente($id)
    {
        $miembro = Miembro::findOrFail($id);
        $miembro->presente = 1 - $miembro->presente; // 0→1, 1→0
        $miembro->save();
        return redirect()->route('miembros.index')->with('message', 'Miembro presente cambiado.');
    }
}
