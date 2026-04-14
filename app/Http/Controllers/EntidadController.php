<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use App\Models\Obligaciones;
use Illuminate\Http\Request;

class EntidadController extends Controller
{
    public function index()
    {
        return view('entidad.index');
    }

    public function firma()
    {
        $data = Entidad::find(1);
        return view('entidad.firma', compact('data'));
    }

    public function firma_post(Request $request)
    {
        $request->validate([
            'file' => 'file',
            'pass_firma' => 'required'
        ]);

        $file = $request->file('file');
        if ($request->hasFile('file')) {
            if ($file->getClientOriginalExtension() !== 'p12') {
                return back()->withErrors([
                    'file' => 'El archivo debe ser un certificado .p12'
                ]);
            }
        }

        $data = Entidad::find(1);
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $nombreArchivo = uniqid() . '.p12';
            $filePath = $file->storeAs('firma', $nombreArchivo);

            $data->firma = $filePath;
        }
        $data->pass_firma = $request->pass_firma;
        $data->update();

        return redirect()->route('entidad.index')->with('message', 'Firma actualizado con exito.');
    }

    public function obligaciones()
    {
        return view('entidad.obligacion');
    }

    public function obligaciones_post(Request $request)
    {
        $request->validate([
            'codigo' => 'required',
            'descripcion' => 'required'
        ]);

        Obligaciones::create([
            'entidad_id' => 1,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'estado_id' => 1
        ]);

        return redirect()->route('entidad.index')->with('message', 'Obligacion agregado con exito.');
    }

    public function obligacion_editar(Obligaciones $obligaciones)
    {
        $data = $obligaciones;
        return view('entidad.obligacion_edit', compact('data'));
    }

    public function obligacion_editar_post(Obligaciones $obligaciones, Request $request)
    {
        $request->validate([
            'codigo' => 'required',
            'descripcion' => 'required'
        ]);

        $obligaciones->update([
            'entidad_id' => 1,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'estado_id' => $request->estado_id
        ]);

        return redirect()->route('entidad.index')->with('message', 'Obligacion actualizado con exito.');
    }

}
