<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\EstadoCivil;
use App\Models\Persona;
use App\Models\Sexo;
use App\Models\TipoPersona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:persona.index')->only('index');
        $this->middleware('permission:persona.create')->only(['create', 'store']);
        $this->middleware('permission:persona.edit')->only(['edit', 'update']);
    }

    public function index(Request $request)
    {
        $search =  $request->search;
        if($search){
            $data = Persona::where('documento', $search)
            ->orWhere('nombre', 'LIKE', '%' . $search . '%')
            ->orWhere('apellido', 'LIKE', '%' . $search . '%')
            ->paginate(50);
        }else{
            $data = Persona::paginate(50);
        }
        return view('persona.index', compact('search', 'data'));
    }

    public function create()
    {
        $departamento = Departamento::all();
        $distrito = Distrito::where('departamento_id', $departamento[0]->id)->get();
        $ciudad = Ciudad::where('distrito_id', $distrito[0]->id)->get();
        $tipoPersona = TipoPersona::all();
        $sexo = Sexo::all();
        $estado_civil = EstadoCivil::all();
        return view('persona.create', compact('departamento', 'distrito', 'ciudad', 'tipoPersona', 'sexo', 'estado_civil'));
    }

    public function store(Request $request)
    {
        $documento = str_replace(['.', ' '], '', $request->documento);

        $request->merge([
            'documento' => $documento,
        ]);

        $request->validate([
            'documento' => 'required|unique:personas,documento',
            'ruc' => 'required|unique:personas,ruc',
            'nombre' => 'required',
            'apellido' => 'required',
            'direccion' => 'required',
            'email' => 'required|email|unique:personas,email',
            'celular' => 'required',
            'barrio' => 'required'
        ]);

        DB::beginTransaction();

        try {

            $ruc = trim((string) $request->ruc);

            Persona::create([
                'departamento_id' => $request->departamento_id,
                'distrito_id' => $request->distrito_id,
                'ciudad_id' => $request->ciudad_id,
                'tipo_persona_id' => $request->tipo_persona_id,
                'sexo_id' => $request->sexo_id,
                'estado_civil_id' => $request->estado_civil_id,
                'tipo_vivienda_id' => 1,
                'documento' => $documento,
                'ruc' => $ruc === '' ? $documento : $ruc,
                'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
                'apellido' => mb_strtoupper($request->apellido, 'UTF-8'),
                'fecha_nacimiento' => '1900-01-01',
                'direccion' => mb_strtoupper($request->direccion, 'UTF-8'),
                'barrio' => mb_strtoupper($request->barrio, 'UTF-8'),
                'celular' => $request->celular,
                'email' => $request->email,
                'vivienda' => '',
                'documento_frente' => '',
                'documento_reverso' => '',
                'selfi' => '',
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect()->route('persona.index')->with('message', 'Persona creado con exito.');
    }

    public function edit(Persona $persona)
    {
        $departamento = Departamento::all();
        $distrito = Distrito::where('departamento_id', $persona->departamento_id)->get();
        $ciudad = Ciudad::where('distrito_id', $persona->distrito_id)->get();
        $tipoPersona = TipoPersona::all();
        $sexo = Sexo::all();
        $estado_civil = EstadoCivil::all();
        return view('persona.edit', compact('departamento', 'distrito', 'ciudad', 'tipoPersona', 'sexo', 'estado_civil', 'persona'));
    }

    public function update(Persona $persona, Request $request)
    {
        $documento = str_replace(['.', ' '], '', $request->documento);

        $request->merge([
            'documento' => $documento,
        ]);

        $request->validate([
            'documento' => [
                'required',
                Rule::unique('personas', 'documento')->ignore($persona->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('personas', 'email')->ignore($persona->id),
            ],
            'nombre' => 'required',
            'apellido' => 'required',
            'departamento_id' => 'required',
            'distrito_id' => 'required',
            'ciudad_id' => 'required',
            'barrio' => 'required',
            'direccion' => 'required',
        ]);

        DB::beginTransaction();

        try {

            $ruc = trim((string) $request->ruc);

            $persona->update([
                'departamento_id' => $request->departamento_id,
                'distrito_id' => $request->distrito_id,
                'ciudad_id' => $request->ciudad_id,
                'tipo_persona_id' => $request->tipo_persona_id,
                'sexo_id' => $request->sexo_id,
                'estado_civil_id' => $request->estado_civil_id,
                'tipo_vivienda_id' => 1,
                'documento' => $documento,
                'ruc' => $ruc === '' ? $documento : $ruc,
                'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
                'apellido' => mb_strtoupper($request->apellido, 'UTF-8'),
                'fecha_nacimiento' => '1900-01-01',
                'direccion' => mb_strtoupper($request->direccion, 'UTF-8'),
                'barrio' => mb_strtoupper($request->barrio, 'UTF-8'),
                'celular' => $request->celular,
                'email' => $request->email,
                'vivienda' => '',
                'documento_frente' => '',
                'documento_reverso' => '',
                'selfi' => '',
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect()->route('persona.index')->with('message', 'Persona editado con exito.');
    }
}
