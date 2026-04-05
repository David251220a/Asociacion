<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Estado;
use App\Models\EstadoCivil;
use App\Models\Familiar;
use App\Models\Persona;
use App\Models\Sexo;
use App\Models\TipoAsociado;
use App\Models\TipoFamiliar;
use App\Models\TipoVivienda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsociadoController extends Controller
{
    public function index(Request $request)
    {
        $search = str_replace('.', '', $request->search);
        if($search){
            $data = Asociado::with('persona')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('persona', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%")
                    ->orWhere('apellido', 'like', "%$search%")
                    ->orWhere('documento', 'like', "%$search%");
                });
            })
            ->paginate(50);
        }else{
            $data = Asociado::where('estado_id', 1)
            ->paginate(50);
        }
        return view('asociados.index', compact('search', 'data'));
    }

    public function create()
    {
        $sexo = Sexo::all();
        $estado_civil = EstadoCivil::all();
        $tipo_vivienda = TipoVivienda::all();
        $tipo_familiar = TipoFamiliar::all();
        $departamento = Departamento::all();
        $distrito = Distrito::where('departamento_id', $departamento[0]->id)->get();
        $ciudad = Ciudad::where('distrito_id', $distrito[0]->id)->get();
        $tipo_asociado = TipoAsociado::all();
        return view('asociados.create', compact('sexo', 'estado_civil', 'tipo_familiar', 'tipo_vivienda', 'departamento', 'distrito', 'ciudad', 'tipo_asociado'));
    }

    public function store(Request $request)
    {
        $documento = str_replace(['.', ' '], '', $request->documento);
        $numero_socio = str_replace(['.', ' '], '', $request->numero_socio);

        $request->merge([
            'documento' => $documento,
            'numero_socio' => $numero_socio,
        ]);

        $request->validate([
            'numero_socio' => 'required|unique:asociados,numero_socio',
            'documento' => 'required|unique:personas,documento',
            'nombre' => 'required',
            'nombre' => 'required',
            'fecha_nacimiento' => 'required|date',
            'departamento_id' => 'required',
            'distrito_id' => 'required',
            'ciudad_id' => 'required',
            'barrio' => 'required',
            'direccion' => 'required',
            'email' => 'required|email|unique:personas,email',
            'documento_frente' => 'mimes:jpg,jpeg',
            'documento_reverso' => 'mimes:jpg,jpeg',
            'selfi' => 'mimes:jpg,jpeg',
            'fecha_admision' => 'required',
            'documento_conyuge' => 'nullable|required_with:nombre_conyuge,apellido_conyuge',
            'nombre_conyuge' => 'nullable|required_with:documento_conyuge,apellido_conyuge',
            'apellido_conyuge' => 'nullable|required_with:documento_conyuge,nombre_conyuge',
            'documento_hijo1' => 'nullable|required_with:nombre_hijo1,apellido_hijo1',
            'nombre_hijo1' => 'nullable|required_with:documento_hijo1,apellido_hijo1',
            'apellido_hijo1' => 'nullable|required_with:documento_hijo1,nombre_hijo1',
            'documento_hijo2' => 'nullable|required_with:nombre_hijo2,apellido_hijo2',
            'nombre_hijo2' => 'nullable|required_with:documento_hijo2,apellido_hijo2',
            'apellido_hijo2' => 'nullable|required_with:documento_hijo2,nombre_hijo2',
        ], [
            'documento.unique' => 'Ya existe persona con este documento',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ingresar un correo válido',
            'email.unique' => 'Ya existe una persona con este correo',
            'documento.required' => 'El documento es obligatorio',
            'documento_conyuge.required_with' => 'Debe completar el documento del conyuge si carga nombre o apellido.',
            'nombre_conyuge.required_with' => 'Debe completar el nombre del conyuge si carga documento o apellido.',
            'apellido_conyuge.required_with' => 'Debe completar el apellido del conyuge si carga documento o nombre.',
            'documento_hijo1.required_with' => 'Debe completar el documento del hijo/a si carga nombre o apellido.',
            'nombre_hijo1.required_with' => 'Debe completar el nombre del hijo/a si carga documento o apellido.',
            'apellido_hijo1.required_with' => 'Debe completar el apellido del hijo/a si carga documento o nombre.',
            'documento_hijo2.required_with' => 'Debe completar el documento del hijo/a si carga nombre o apellido.',
            'nombre_hijo2.required_with' => 'Debe completar el nombre del hijo/a si carga documento o apellido.',
            'apellido_hijo2.required_with' => 'Debe completar el apellido del hijo/a si carga documento o nombre.',
        ]);

        

        try {
            $asociado = DB::transaction(function () use ($request, $documento, $numero_socio) {
                $documento_frente = '';
                $documento_reverso = '';
                $selfi = '';
                $ruc = trim((string) $request->ruc);

                if ($request->hasFile('documento_frente')) {
                    $documento_frente = $request->file('documento_frente')->store('documentos');
                }

                if ($request->hasFile('documento_reverso')) {
                    $documento_reverso = $request->file('documento_reverso')->store('documentos');
                }

                if ($request->hasFile('selfi')) {
                    $selfi = $request->file('selfi')->store('documentos');
                }

                $persona = Persona::create([
                    'departamento_id' => $request->departamento_id,
                    'distrito_id' => $request->distrito_id,
                    'ciudad_id' => $request->ciudad_id,
                    'tipo_persona_id' => 1,
                    'sexo_id' => $request->sexo_id,
                    'estado_civil_id' => $request->estado_civil_id,
                    'tipo_vivienda_id' => $request->tipo_vivienda_id,
                    'documento' => $documento,
                    'ruc' => $ruc === '' ? $documento : $ruc,
                    'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
                    'apellido' => mb_strtoupper($request->apellido, 'UTF-8'),
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'direccion' => mb_strtoupper($request->direccion, 'UTF-8'),
                    'barrio' => mb_strtoupper($request->barrio, 'UTF-8'),
                    'celular' => $request->celular,
                    'email' => $request->email,
                    'vivienda' => mb_strtoupper($request->vivienda, 'UTF-8'),
                    'documento_frente' => $documento_frente,
                    'documento_reverso' => $documento_reverso,
                    'selfi' => $selfi,
                    'estado_id' => 1,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);

                $fecha = Carbon::parse($request->fecha_admision);
                $mes = $fecha->month;
                $anio = $fecha->year;

                $asociado = Asociado::create([
                    'persona_id' => $persona->id,
                    'tipo_asociado_id' => $request->tipo_asociado_id,
                    'fecha_admision' => $request->fecha_admision,
                    'solicitud_id' => 0,
                    'anio_aporte' => $anio,
                    'mes_aporte' => $mes,
                    'numero_socio' => $numero_socio,
                    'fecha_baja' => null,
                    'motivo' => 0,
                    'motivo_baja_otro' => '',
                    'estado_id' => 1,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);

                if ($request->filled('documento_conyuge') && 
                    $request->filled('nombre_conyuge') && 
                    $request->filled('apellido_conyuge')) {

                    // guardar hijo
                    Familiar::create([
                        'persona_id' => $persona->id,
                        'tipo_familiar' => 1,
                        'documento' => $request->documento_conyuge,
                        'nombre' => mb_strtoupper($request->nombre_conyuge, 'UTF-8'),
                        'apellido' => mb_strtoupper($request->apellido_conyuge, 'UTF-8'),
                        'celular' => $request->celular_conyuge,
                        'estado_id' => 1,
                        'user_id' => auth()->id(),
                        'usuario_modificacion' => auth()->id(),
                    ]);
                }

                if ($request->filled('documento_hijo1') && 
                    $request->filled('nombre_hijo1') && 
                    $request->filled('apellido_hijo1')) {

                    // guardar hijo
                    Familiar::create([
                        'persona_id' => $persona->id,
                        'tipo_familiar' => 2,
                        'documento' => $request->documento_hijo1,
                        'nombre' => mb_strtoupper($request->nombre_hijo1, 'UTF-8'),
                        'apellido' => mb_strtoupper($request->apellido_hijo1, 'UTF-8'),
                        'celular' => $request->celular_hijo1,
                        'estado_id' => 1,
                        'user_id' => auth()->id(),
                        'usuario_modificacion' => auth()->id(),
                    ]);
                }

                if ($request->filled('documento_hijo2') && 
                    $request->filled('nombre_hijo2') && 
                    $request->filled('apellido_hijo2')) {

                    // guardar hijo
                    Familiar::create([
                        'persona_id' => $persona->id,
                        'tipo_familiar' => 2,
                        'documento' => $request->documento_hijo2,
                        'nombre' => mb_strtoupper($request->nombre_hijo2, 'UTF-8'),
                        'apellido' => mb_strtoupper($request->apellido_hijo2, 'UTF-8'),
                        'celular' => $request->celular_hijo2,
                        'estado_id' => 1,
                        'user_id' => auth()->id(),
                        'usuario_modificacion' => auth()->id(),
                    ]);
                }

                return $asociado;

            });
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }

        $asociado->load('ficha_medica');

        if (!$asociado->ficha_medica) {
            return redirect()->route('ficha_medica.create', $asociado)->with('message', 'Asociado registrado correctamente. Debe registrar su ficha medica');
        }

        return redirect()->route('asociado.index')->with('message', 'Asociado registrado correctamente.');
        
    }

    public function edit(Asociado $asociado)
    {
        $sexo = Sexo::all();
        $estado_civil = EstadoCivil::all();
        $tipo_vivienda = TipoVivienda::all();
        $tipo_familiar = TipoFamiliar::all();
        $departamento = Departamento::all();
        $distrito = Distrito::where('departamento_id', $asociado->departamento_id)->get();
        $ciudad = Ciudad::where('distrito_id', $asociado->distrito_id)->get();
        $tipo_asociado = TipoAsociado::all();
        $data = $asociado;
        $persona = $asociado->persona;
        $estado = Estado::all();
        return view('asociados.edit', compact('sexo', 'estado_civil', 'tipo_familiar', 'tipo_vivienda', 'departamento', 'distrito', 'ciudad', 'tipo_asociado', 'data', 'persona', 'estado'));
    }

    public function update(Asociado $asociado, Request $request)
    {
        $documento = str_replace(['.', ' '], '', $request->documento);
        $numero_socio = str_replace(['.', ' '], '', $request->numero_socio);

        $request->merge([
            'documento' => $documento,
            'numero_socio' => $numero_socio,
        ]);

        $request->validate([
            'numero_socio' => [
                'required',
                Rule::unique('asociados', 'numero_socio')->ignore($asociado->id),
            ],
            'documento' => [
                'required',
                Rule::unique('personas', 'documento')->ignore($asociado->persona->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('personas', 'email')->ignore($asociado->persona->id),
            ],
            'nombre' => 'required',
            'apellido' => 'required',
            'fecha_nacimiento' => 'required|date',
            'departamento_id' => 'required',
            'distrito_id' => 'required',
            'ciudad_id' => 'required',
            'barrio' => 'required',
            'direccion' => 'required',
            'documento_frente' => 'nullable|mimes:jpg,jpeg',
            'documento_reverso' => 'nullable|mimes:jpg,jpeg',
            'selfi' => 'nullable|mimes:jpg,jpeg',
            'fecha_admision' => 'required',
        ], [
            'documento.unique' => 'Ya existe persona con este documento',
            'numero_socio.unique' => 'Ya existe persona con este numero de socio',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ingresar un correo válido',
            'email.unique' => 'Ya existe una persona con este correo',
            'documento.required' => 'El documento es obligatorio',
        ]);

        if ($request->estado_id == 2){
            if (empty($request->fecha_baja)){
                return redirect()->back()->withErrors('La fecha de baja no puede ser vacio si pone en estado inactivo');
            }
        }

        try {
            DB::transaction(function () use ($request, $documento, $numero_socio, $asociado) {
                $documento_frente = $asociado->persona->documento_frente;
                $documento_reverso = $asociado->persona->documento_reverso;
                $selfi = $asociado->persona->selfi;
                $ruc = trim((string) $request->ruc);

                if ($request->hasFile('documento_frente')) {
                    $documento_frente = $request->file('documento_frente')->store('documentos');
                }

                if ($request->hasFile('documento_reverso')) {
                    $documento_reverso = $request->file('documento_reverso')->store('documentos');
                }

                if ($request->hasFile('selfi')) {
                    $selfi = $request->file('selfi')->store('documentos');
                }

                $asociado->persona->update([
                    'departamento_id' => $request->departamento_id,
                    'distrito_id' => $request->distrito_id,
                    'ciudad_id' => $request->ciudad_id,
                    'tipo_persona_id' => 1,
                    'sexo_id' => $request->sexo_id,
                    'estado_civil_id' => $request->estado_civil_id,
                    'tipo_vivienda_id' => $request->tipo_vivienda_id,
                    'documento' => $documento,
                    'ruc' => $ruc === '' ? $documento : $ruc,
                    'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
                    'apellido' => mb_strtoupper($request->apellido, 'UTF-8'),
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'direccion' => mb_strtoupper($request->direccion, 'UTF-8'),
                    'barrio' => mb_strtoupper($request->barrio, 'UTF-8'),
                    'celular' => $request->celular,
                    'email' => $request->email,
                    'vivienda' => mb_strtoupper($request->vivienda, 'UTF-8'),
                    'documento_frente' => $documento_frente,
                    'documento_reverso' => $documento_reverso,
                    'selfi' => $selfi,
                    'estado_id' => $request->estado_id,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);

                $fecha = Carbon::parse($request->fecha_admision);
                $mes = $fecha->month;
                $anio = $fecha->year;
                $fecha_baja = $request->fecha_baja;

                $asociado->update([
                    'tipo_asociado_id' => $request->tipo_asociado_id,
                    'fecha_admision' => $request->fecha_admision,
                    'solicitud_id' => 0,
                    'anio_aporte' => $anio,
                    'mes_aporte' => $mes,
                    'numero_socio' => $numero_socio,
                    'fecha_baja' => $fecha_baja,
                    'motivo' => $request->motivo,
                    'motivo_baja_otro' => $request->motivo_baja,
                    'estado_id' => $request->estado_id,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);

            });
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('asociado.index')->with('message', 'Asociado actualizado con exito');
    }

    public function getDistritos($id)
    {
        $distrito = Distrito::where('departamento_id', $id)
        ->orderBy('descripcion')
        ->get(['id', 'descripcion']);

        return response()->json($distrito);
    }

    public function getCiudades($id)
    {
        $ciudad = Ciudad::where('distrito_id', $id)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion']);

        return response()->json($ciudad);
    }
}
