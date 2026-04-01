<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\EstadoCivil;
use App\Models\Persona;
use App\Models\Sexo;
use App\Models\TipoAsociado;
use App\Models\TipoFamiliar;
use App\Models\TipoVivienda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsociadoController extends Controller
{
    public function index(Request $request)
    {
        $search = str_replace('.', '', $request->search);
        if($search){
            $data = Asociado::with('persona')
            ->where('estado_id', 1)
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
        ], [
            'documento.unique' => 'Ya existe persona con este documento',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ingresar un correo válido',
            'email.unique' => 'Ya existe una persona con este correo',
            'documento.required' => 'El documento es obligatorio',
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
