<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\EstadoSolicitud;
use App\Models\Institucion;
use App\Models\Persona;
use App\Models\SolicitudActualizacionDatos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Override;

class SolicitudActualizacionDatosController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:actu_datos.index')->only('index');
        $this->middleware('permission:actu_datos.show')->only(['show', 'store']);
    }

    public function index(Request $request)
    {
        $menorFechaPendiente = SolicitudActualizacionDatos::where('estado_solicitud_id', 1)
        ->min('fecha_solicitud');

        $desde = $request->desde ?? ($menorFechaPendiente ?? now()->format('Y-m-d'));
        $hasta = $request->hasta ?? now()->format('Y-m-d');
        $estado = $request->estado ?? "1";
        $estados_solicitud = EstadoSolicitud::all();
        $data = SolicitudActualizacionDatos::where('estado_solicitud_id', $estado)
        ->whereBetween('fecha_solicitud', [$desde, $hasta])
        ->orderBy('created_at', 'ASC')
        ->paginate(50);
        return view('actualizacion_datos.index', compact('desde', 'hasta', 'data','estados_solicitud'));
    }

    public function show(SolicitudActualizacionDatos $solicitudActualizacion)
    {
        $data = $solicitudActualizacion;
        $institucionActual = Institucion::find($data->institucion_municipal_id_actual);
        $institucionNueva = Institucion::find($data->institucion_municipal_id_nueva);
        return view('actualizacion_datos.show', compact('data','institucionActual','institucionNueva'));
    }

    public function store(SolicitudActualizacionDatos $solicitudActualizacion, Request $request)
    {
        $camposPermitidos = [
            'documento',
            'nombre',
            'apellido',
            'fecha_nacimiento',
            'institucion_municipal_id',
            'email',
            'celular',
            'documento_frente',
            'documento_reverso',
        ];

        $datos = $request->validate([
            'campos_aprobados' => [
                'required',
                'array',
                'min:1',
            ],
            'campos_aprobados.*' => [
                'required',
                'string',
                Rule::in($camposPermitidos),
            ],
            'observacion' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'campos_aprobados.required' =>
                'Debe seleccionar al menos un cambio para aprobar.',

            'campos_aprobados.min' =>
                'Debe seleccionar al menos un cambio para aprobar.',

            'campos_aprobados.*.in' =>
                'Uno de los campos seleccionados no es válido.',
        ]);

        try {
            DB::transaction(function () use ($solicitudActualizacion,$datos) {
                $usuarioId = auth()->id();
                /*
                |----------------------------------------------------------
                | BLOQUEAR SOLICITUD
                |----------------------------------------------------------
                */
                $solicitud = SolicitudActualizacionDatos::lockForUpdate()
                ->findOrFail($solicitudActualizacion->id);

                if (!in_array((int) $solicitud->estado_solicitud_id,[1, 2],true)) {
                    throw new \Exception(
                        'La solicitud ya fue resuelta anteriormente.'
                    );
                }
                /*
                |----------------------------------------------------------
                | DETERMINAR CAMPOS REALMENTE SOLICITADOS
                |----------------------------------------------------------
                */
                $mapaCampos = [
                    'documento' => 'documento_nuevo',
                    'nombre' => 'nombre_nuevo',
                    'apellido' => 'apellido_nuevo',
                    'fecha_nacimiento' => 'fecha_nacimiento_nueva',
                    'institucion_municipal_id' =>'institucion_municipal_id_nueva',
                    'email' => 'email_nuevo',
                    'celular' => 'celular_nuevo',
                    'documento_frente' => 'documento_frente_nuevo',
                    'documento_reverso' => 'documento_reverso_nuevo',
                ];

                $camposSolicitados = collect($mapaCampos)
                ->filter(fn ($columna) => !is_null($solicitud->{$columna}))
                ->keys()
                ->values();

                $camposAprobados = collect($datos['campos_aprobados'])
                ->unique()
                ->values();
                /*
                * Impedir que se envíen campos que no fueron solicitados.
                */
                $camposInvalidos = $camposAprobados->diff($camposSolicitados);

                if ($camposInvalidos->isNotEmpty()) {
                    throw new \Exception(
                        'Se intentó aprobar un campo que no forma parte de la solicitud.'
                    );
                }

                $camposNoAprobados = $camposSolicitados
                ->diff($camposAprobados)
                ->values();
                /*
                |----------------------------------------------------------
                | BLOQUEAR PERSONA
                |----------------------------------------------------------
                */
                $persona = Persona::lockForUpdate()
                ->findOrFail($solicitud->persona_id);
                /*
                * La cuenta se obtiene antes de cambiar el documento.
                */
                $usuarioAsociado = User::where('documento', $persona->documento)
                ->lockForUpdate()
                ->first();

                /*
                |----------------------------------------------------------
                | VALIDAR DOCUMENTO
                |----------------------------------------------------------
                */
                if ($camposAprobados->contains('documento')) {
                    $nuevoDocumento = $solicitud->documento_nuevo;

                    $documentoExiste = Persona::where('documento',$nuevoDocumento)
                    ->where('id', '<>', $persona->id)
                    ->exists();

                    if ($documentoExiste) {
                        throw new \Exception('El nuevo documento ya pertenece a otra persona.');
                    }

                    $usuarioExiste = User::where('documento',$nuevoDocumento)
                    ->when(
                        $usuarioAsociado,
                        fn ($query) => $query->where(
                            'id',
                            '<>',
                            $usuarioAsociado->id
                        )
                    )
                    ->exists();

                    if ($usuarioExiste) {
                        throw new \Exception('El nuevo documento ya pertenece a otra cuenta de usuario.');
                    }
                }

                /*
                |----------------------------------------------------------
                | VALIDAR CORREO
                |----------------------------------------------------------
                */
                if ($camposAprobados->contains('email')) {
                    $emailExiste = Persona::where('email', $solicitud->email_nuevo)
                    ->where('id', '<>', $persona->id)
                    ->exists();

                    if ($emailExiste) {
                        throw new \Exception('El nuevo correo electrónico ya pertenece a otra persona.');
                    }
                }
                /*
                |----------------------------------------------------------
                | ACTUALIZAR PERSONA
                |----------------------------------------------------------
                */
                $camposPersona = [
                    'documento',
                    'nombre',
                    'apellido',
                    'fecha_nacimiento',
                    'email',
                    'celular',
                    'documento_frente',
                    'documento_reverso',
                ];

                foreach ($camposPersona as $campo) {
                    if ($camposAprobados->contains($campo)) {
                        $columnaNueva = $mapaCampos[$campo];

                        $persona->{$campo} = $solicitud->{$columnaNueva};
                    }
                }

                $persona->usuario_modificacion = $usuarioId;
                $persona->save();

                /*
                |----------------------------------------------------------
                | ACTUALIZAR INSTITUCIÓN
                |----------------------------------------------------------
                */
                if ($camposAprobados->contains('institucion_municipal_id')) {
                    $institucionExiste = Institucion::whereKey($solicitud->institucion_municipal_id_nueva)
                    ->where('estado_id', 1)
                    ->exists();

                    if (!$institucionExiste) {
                        throw new \Exception('La institución seleccionada ya no se encuentra disponible.');
                    }

                    $asociado = Asociado::where('persona_id',$persona->id)
                    ->lockForUpdate()
                    ->first();

                    if (!$asociado) {
                        throw new \Exception('No se encontró el registro del asociado.');
                    }

                    $asociado->institucion_municipal_id = $solicitud->institucion_municipal_id_nueva;
                    $asociado->usuario_modificacion = $usuarioId;
                    $asociado->save();
                }
                /*
                |----------------------------------------------------------
                | ACTUALIZAR DOCUMENTO DEL USUARIO
                |----------------------------------------------------------
                */
                if ($camposAprobados->contains('documento')) {
                    if (!$usuarioAsociado) {
                        throw new \Exception('No se encontró la cuenta de usuario asociada.');
                    }

                    $usuarioAsociado->documento = $solicitud->documento_nuevo;
                    $usuarioAsociado->save();
                }

                /*
                |----------------------------------------------------------
                | DETERMINAR APROBACIÓN COMPLETA O PARCIAL
                |----------------------------------------------------------
                */
                $aprobacionCompleta = $camposNoAprobados->isEmpty();

                $solicitud->estado_solicitud_id = $aprobacionCompleta ? 3 : 6;

                $solicitud->fecha_resolucion = now();
                $solicitud->usuario_resolucion = $usuarioId;
                $solicitud->campos_aprobados = $camposAprobados->all();
                $solicitud->campos_no_aprobados = $camposNoAprobados->all();
                $solicitud->motivo_rechazo = null;
                $solicitud->observacion = $datos['observacion'] ?? null;
                $solicitud->usuario_modificacion = $usuarioId;
                $solicitud->save();
            });

            return redirect()->route('actu_datos.show',$solicitudActualizacion->id)
            ->with(
                'message',
                'Los cambios seleccionados fueron aprobados y los datos fueron actualizados correctamente.'
            );

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['solicitud' => $e->getMessage(),]);
        }
    }

}
