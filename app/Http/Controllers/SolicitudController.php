<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\Entidad;
use App\Models\EstadoCivil;
use App\Models\Familiar;
use App\Models\FichaMedica;
use App\Models\Miembro;
use App\Models\Numeraciones;
use App\Models\Persona;
use App\Models\Solicitud;
use App\Models\SolicitudAprobado;
use App\Models\TipoAsociado;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:solicitud.index')->only('index');
        $this->middleware('permission:solicitud.show')->only('show');
        $this->middleware('permission:solicitud.show')->only('store');
    }

    public function index(Request $request)
    {
        $menorFechaPendiente = Solicitud::where('estado_id', 1)
        ->where('aprobado', 0)
        ->min('fecha_solicitud');

        $desde = $request->desde ?? ($menorFechaPendiente ?? now()->format('Y-m-d'));
        $hasta = $request->hasta ?? now()->format('Y-m-d');
        $estado = $request->estado ?? "0";

        $data = Solicitud::where('estado_id', 1)
        ->where('aprobado', $estado)
        ->whereBetween('fecha_solicitud', [$desde, $hasta])
        ->orderBy('created_at', 'ASC')
        ->paginate(50);

        return view('solicitud.index', compact('desde', 'hasta', 'data'));
    }

    public function show(Solicitud $solicitud)
    {
        $data = $solicitud;
        $entidad = Entidad::find(1);
        $tipos = ['PRESIDENTE', 'VICEPRESIDENTE', 'SECRETARIO', 'TESORERA', 'PRO-TESORERA', 'MIEMBROS', 'SINDICO'];
        $miembros = Miembro::where('presente', 1)->orderBy('tipo', 'ASC')->get();
        return view('solicitud.show', compact('data', 'entidad', 'miembros','tipos'));
    }

    public function store(Solicitud $solicitud, Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'acta' => 'required',
            'estado' => 'required'
        ], [
            'fecha_inicio.after_or_equal' => 'La fecha no puede ser menor a hoy.',
        ]);

        if($request->estado == 2){
            if (empty($request->motivo)){
                return redirect()->back()->withErrors('Si rechaza la solicitud debe especificar el motivo.');
            }
        }

        if(($solicitud->aprobado == 1) || ($solicitud->aprobado == 2)){
            return redirect()->back()->withErrors('La solicitud ya se encuentra aprobado/rechazado.');
        }

        DB::beginTransaction();
        try {

            $numeracionSocio = Numeraciones::where('tipo', 2)
            ->lockForUpdate()
            ->first();

            if (!$numeracionSocio) {
                $mayorSocio = Asociado::max('numero_socio');

                $numeroSocio = ($mayorSocio ?? 0) + 1;

                $numeracionSocio = Numeraciones::create([
                    'tipo' => 2,
                    'descripcion' => 'Numeración de socios',
                    'anio' => 0,
                    'numero' => $numeroSocio + 1,
                ]);
            } else {
                $numeroSocio = $numeracionSocio->numero;
                $existe = Asociado::where('numero_socio', $numeroSocio)->exists();
                if ($existe) {
                    $numeroSocio = Asociado::max('numero_socio') + 1;
                }
                $numeracionSocio->numero = $numeroSocio + 1;
                $numeracionSocio->save();
            }

            $solicitud->update([
                'aprobado' => $request->estado,
                'acta' => $request->acta,
                'fecha_aprobacion_o_rechazo' => now(),
                'usuario_modificacion' => auth()->id(),
                'numero_socio' => $numeroSocio,
            ]);

            $miembros = Miembro::where('presente', 1)->get();
            foreach ($miembros as $item) {
                SolicitudAprobado::create([
                    'solicitud_id' => $solicitud->id,
                    'miembro_id' => $item->id,
                    'nombre_apellido' => $item->nombre . ' ' . $item->apellido,
                    'presente' => 1,
                ]);
            }

            $persona = Persona::create([
                'departamento_id' => $solicitud->departamento_id,
                'distrito_id' => $solicitud->distrito_id,
                'ciudad_id' => $solicitud->ciudad_id,
                'tipo_persona_id' => $solicitud->tipo_persona_id,
                'sexo_id' => $solicitud->sexo_id,
                'estado_civil_id' => $solicitud->estado_civil_id,
                'tipo_vivienda_id' => $solicitud->tipo_vivienda_id,
                'documento' => $solicitud->documento,
                'ruc' => $solicitud->documento,
                'nombre' => $solicitud->nombre,
                'apellido' => $solicitud->apellido,
                'fecha_nacimiento' => $solicitud->fecha_nacimiento,
                'direccion' => $solicitud->direccion,
                'barrio' => $solicitud->barrio,
                'celular' => $solicitud->celular,
                'email' => $solicitud->email,
                'vivienda' => $solicitud->vivienda,
                'documento_frente' => $solicitud->documento_frente,
                'documento_reverso' => $solicitud->documento_reverso,
                'selfi' => $solicitud->selfi,
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            $fecha = Carbon::parse($request->fecha_inicio);
            $mes = $fecha->month;
            $anio = $fecha->year;

            $asociado = Asociado::create([
                'persona_id' => $persona->id,
                'tipo_asociado_id' => $solicitud->tipo_asociado_id,
                'institucion_id' => $solicitud->institucion_id,
                'fecha_admision' => now(),
                'solicitud_id' => $solicitud->id,
                'anio_aporte' => $anio,
                'mes_aporte' => $mes,
                'numero_socio' => $numeroSocio,
                'fecha_baja' => null,
                'motivo' => 0,
                'motivo_baja_otro' => '',
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            foreach ($solicitud->familiares as $item) {
                Familiar::create([
                    'persona_id' => $persona->id,
                    'tipo_familiar_id' => $item->tipo_familiar,
                    'documento' => $item->documento,
                    'nombre' => $item->nombre,
                    'apellido' => $item->apellido,
                    'celular' => $item->celular,
                    'estado_id' => 1,
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                ]);
            }

            FichaMedica::UpdateOrCreate([
                'asociado_id' => $asociado->id
            ],
            [
                'cancer' => $solicitud->ficha_medica->cancer,
                'diabetes' => $solicitud->ficha_medica->diabetes,
                'presion_alta' => $solicitud->ficha_medica->presion_alta,
                'otro' => $solicitud->ficha_medica->otro,
                'medicamentos' => $solicitud->ficha_medica->medicamentos,
                'seguro_particular' => $solicitud->ficha_medica->seguro_particular,
                'seguro_ips' => $solicitud->ficha_medica->seguro_ips,
                'seguro_ninguno' => $solicitud->ficha_medica->seguro_ninguno,
                'observacion' => $solicitud->ficha_medica->observacion,
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);
            $desc = ($request->estado == 1) ? 'APROBADO' : 'RECHAZADO' ;
            DB::commit();
            return redirect()->route('solicitud.show', $solicitud)->with('message', 'Solicitud'.$desc .' con exito.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage());
        }


        return $request->all();
    }

    public function imprimir(Solicitud $solicitud)
    {
        $entidad = Entidad::find(1);

        $solicitud->load([
            'familiares.tipo_familia',
            'ficha_medica',
            'estado_civil',
            'ciudad',
            'tipo_vivienda',
            'departamento',
            'distrito',
        ]);

        $tiposAsociados = TipoAsociado::all();
        $civil = EstadoCivil::all();
        $pdf = Pdf::loadView('solicitud.pdf', compact('solicitud', 'entidad','tiposAsociados','civil'))
        ->setPaper('legal', 'portrait');

        return $pdf->stream('solicitud-'.$solicitud->numero_solicitud.'.pdf');
    }

}
