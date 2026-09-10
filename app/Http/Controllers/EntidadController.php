<?php

namespace App\Http\Controllers;

use App\Models\ActividadEconomica;
use App\Models\Entidad;
use App\Models\Obligaciones;
use App\Models\SolicitudConfig;
use Illuminate\Http\Request;

class EntidadController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:entidad.index')->only('index');
        $this->middleware('permission:entidad.firma')->only(['firma', 'firma_post']);
        $this->middleware('permission:entidad.obligaciones')->only(['obligaciones', 'obligaciones_post']);
        $this->middleware('permission:entidad.obligacion_editar')->only(['obligacion_editar', 'obligacion_editar_post']);
        $this->middleware('permission:entidad.actividades')->only(['actividades', 'actividades_post']);
        $this->middleware('permission:entidad.actividades_editar')->only(['actividades_editar', 'actividades_editar_post']);
        $this->middleware('permission:entidad_soli.solicitud')->only('solicitud');
        $this->middleware('permission:entidad_soli.solicitud_ayuda_social')->only(['solicitud_ayuda_social', 'solicitud_ayuda_social_post']);
    }

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

    public function actividades()
    {
        return view('entidad.actividad_crear');
    }

    public function actividades_post(Request $request)
    {
        $request->validate([
            'codigo' => 'required',
            'descripcion' => 'required'
        ]);

        $entidad = Entidad::find(1);

        ActividadEconomica::create([
            'entidad_id' => $entidad->id,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'estado_id' => 1,
        ]);

        return redirect()->route('entidad.index')->with('message', 'Actividad Economica creada correctamente.');
    }

    public function actividades_editar(ActividadEconomica $actividadEconomica)
    {
        $data = $actividadEconomica;
        return view('entidad.actividad_editar', compact('data'));
    }

    public function actividades_editar_post(ActividadEconomica $actividadEconomica, Request $request)
    {
        $request->validate([
            'codigo' => 'required',
            'descripcion' => 'required'
        ]);

        $entidad = Entidad::find(1);

        $actividadEconomica->update([
            'entidad_id' => $entidad->id,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'estado_id' => $request->estado_id,
        ]);

        return redirect()->route('entidad.index')->with('message', 'Actividad Economica editado correctamente.');
    }

    public function solicitud()
    {
        $data = SolicitudConfig::all();
        return view('entidad.solicitud', compact('data'));
    }

    public function solicitud_ayuda_social(SolicitudConfig $solicitudConfig)
    {
        $data = $solicitudConfig;
        return view('entidad.solicitud_activar_ayuda', compact('data'));
    }

    public function solicitud_ayuda_social_post(SolicitudConfig $solicitudConfig, Request $request)
    {
        $datos = $request->validate([
            'descripcion' => [
                'required',
                'string',
                'max:255',
            ],

            'activo' => [
                'required',
                'boolean',
            ],

            'tasa_cuota_unica' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'tasa_cuota_mensual' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'tasa_mora' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'monto_minimo' => [
                'required',
                'integer',
                'min:0',
                'multiple_of:100000',
            ],

            'monto_maximo' => [
                'required',
                'integer',
                'gte:monto_minimo',
                'multiple_of:100000',
            ],

            'plazo_minimo' => [
                'required',
                'integer',
                'min:1',
                'max:255',
            ],

            'plazo_maximo' => [
                'required',
                'integer',
                'gte:plazo_minimo',
                'max:255',
            ],

            'limite_solicitud' => [
                'required',
                'boolean',
            ],

            'limite_solicitud_anual' => [
                'nullable',
                'required_if:limite_solicitud,1',
                'integer',
                'min:1',
                'max:255',
            ],
        ], [
            'descripcion.required' =>
                'Debe ingresar la descripción.',

            'tasa_cuota_unica.required' =>
                'Debe ingresar la tasa para cuota única.',

            'tasa_cuota_mensual.required' =>
                'Debe ingresar la tasa para varias cuotas.',

            'tasa_mora.required' =>
                'Debe ingresar la tasa de mora.',

            'monto_minimo.required' =>
                'Debe ingresar el monto mínimo.',

            'monto_minimo.multiple_of' =>
                'El monto mínimo debe avanzar de G. 100.000 en G. 100.000.',

            'monto_maximo.required' =>
                'Debe ingresar el monto máximo.',

            'monto_maximo.gte' =>
                'El monto máximo debe ser igual o mayor al monto mínimo.',

            'monto_maximo.multiple_of' =>
                'El monto máximo debe avanzar de G. 100.000 en G. 100.000.',

            'plazo_minimo.required' =>
                'Debe ingresar el plazo mínimo.',

            'plazo_maximo.required' =>
                'Debe ingresar el plazo máximo.',

            'plazo_maximo.gte' =>
                'El plazo máximo debe ser igual o mayor al plazo mínimo.',

            'limite_solicitud_anual.required_if' =>
                'Debe ingresar la cantidad máxima de solicitudes por año.',
        ]);

        $datos['activo'] = (int) $datos['activo'];
        $datos['limite_solicitud'] = (int) $datos['limite_solicitud'];

        if ($datos['limite_solicitud'] === 0) {
            $datos['limite_solicitud_anual'] = 0;
        } else {
            $datos['limite_solicitud_anual'] = (int) $datos['limite_solicitud_anual'];
        }

        $solicitudConfig->update($datos);

        return redirect()
        ->route('entidad_soli.solicitud', $solicitudConfig)
        ->with('message','La configuración de solicitudes fue actualizada correctamente.');
    }

}
