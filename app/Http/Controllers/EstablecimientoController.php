<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Numeracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstablecimientoController extends Controller
{
    public function index()
    {
        $data = Establecimiento::where('entidad_id', 1)->get();
        return view('establecimiento.index', compact('data'));
    }

    public function create()
    {
        return view('establecimiento.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'departamento_id' => 'required',
            'distrito_id' => 'required',
            'ciudad_id' => 'required',
            'punto' => 'required',
            'numero_casa' => 'required',
            'telefono' => 'required',
            'descripcion' => 'required',
            'direccion' => 'required',
            'sucursal' => 'required',
            'general' => 'required'
        ]);

        $descripcion = strtoupper(trim($request->descripcion));
        $existe = Establecimiento::where('descripcion', $descripcion)->exists();
        if($existe){
            return back()->withErrors('Ya existe establecimiento con el mismo nombre');
        }

        $existe = Establecimiento::where('punto', $request->punto)->exists();
        if($existe){
            return back()->withErrors('Ya existe establecimiento con el mismo punto de expedicion');
        }

        try {
            DB::transaction(function () use ($request) {
                $establecimiento = Establecimiento::create([
                    'entidad_id' => 1,
                    'departamento_id' => $request->departamento_id,
                    'distrito_id' => $request->distrito_id,
                    'ciudad_id' => $request->ciudad_id,
                    'punto' => $request->punto,
                    'numero_casa' => $request->numero_casa,
                    'telefono' => $request->telefono,
                    'descripcion' => $request->descripcion,
                    'direccion' => $request->direccion,
                    'sucursal' => $request->sucursal,
                    'general' => $request->general,
                    'estado_id' => 1,
                    'user_id' => auth()->user()->id,
                ]);

                $tipos = [1,2,3,4,5,6,7,8];

                foreach ($tipos as $tipo) {

                    Numeracion::create([
                        'timbrado_id' => 1,
                        'establecimiento_id' => $establecimiento->id,
                        'tipo_documento_id' => $tipo,
                        'numero_siguiente' => 1,
                        'estado_id' => 1,
                        'user_id' => auth()->id(),
                    ]);

                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('establecimiento.index')->with('message', 'Establecimiento creado con exito');
    }

    public function edit(Establecimiento $establecimiento)
    {
        $data = $establecimiento;
        return view('establecimiento.edit', compact('data'));
    }

    public function update(Establecimiento $establecimiento, Request $request)
    {
        $request->validate([
            'departamento_id' => 'required',
            'distrito_id' => 'required',
            'ciudad_id' => 'required',
            'punto' => 'required',
            'numero_casa' => 'required',
            'telefono' => 'required',
            'descripcion' => 'required',
            'direccion' => 'required',
            'sucursal' => 'required',
            'general' => 'required'
        ]);

        $descripcion = strtoupper(trim($request->descripcion));
        $existe = Establecimiento::where('descripcion', $descripcion)
        ->where('id', '<>', $establecimiento->id)
        ->exists();
        if($existe){
            return back()->withErrors('Ya existe establecimiento con el mismo nombre');
        }

        $existe = Establecimiento::where('punto', $request->punto)
        ->where('id', '<>', $establecimiento->id)
        ->exists();

        if($existe){
            return back()->withErrors('Ya existe establecimiento con el mismo punto de expedicion');
        }

        try {
            DB::transaction(function () use ($request, $establecimiento) {
                $establecimiento->update([
                    'departamento_id' => $request->departamento_id,
                    'distrito_id' => $request->distrito_id,
                    'ciudad_id' => $request->ciudad_id,
                    'punto' => $request->punto,
                    'numero_casa' => $request->numero_casa,
                    'telefono' => $request->telefono,
                    'descripcion' => $request->descripcion,
                    'direccion' => $request->direccion,
                    'sucursal' => $request->sucursal,
                    'general' => $request->general,
                    'estado_id' => 1,
                    'user_id' => auth()->user()->id,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('establecimiento.index')->with('message', 'Establecimiento actualizado  con exito');
    }
}
