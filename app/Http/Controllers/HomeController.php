<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\Entidad;
use App\Models\Institucion;
use App\Models\Numeraciones;
use App\Models\Persona;
use App\Models\SolicitudActualizacionDatos;
use App\Models\SolicitudAyudaSocial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $persona = Persona::where('documento', auth()->user()->documento)->first();
        return view('home', compact('persona'));
    }

    public function aporte()
    {
        $persona = Persona::where('documento', auth()->user()->documento)->firstOrFail();

        $asociado = Asociado::with([
            'persona',
            'tipo_asociado',
        ])->where('persona_id', $persona->id)->firstOrFail();

        $aportesUltimos = $asociado->aportes_activo()
        ->with('recibo')
        ->orderByDesc('fecha_aporte')
        ->orderByDesc('id')
        ->limit(6)
        ->get();

        $idsUltimos = $aportesUltimos->pluck('id');
        $totalAportado = $asociado->aportes_activo()->sum('aporte');
        $cantidadAportes = $asociado->aportes_activo()->count();

        $totalAnterior = $asociado->aportes_activo()
        ->whereNotIn('id', $idsUltimos)
        ->sum('aporte');

        $cantidadMeses = $asociado->aportes_activo()
        ->select('anio', 'mes')
        ->distinct()
        ->count();

        $anios = intdiv($cantidadMeses, 12);
        $meses = $cantidadMeses % 12;

        $antiguedad = '';

        if ($anios > 0) {
            $antiguedad .= $anios . ' año' . ($anios > 1 ? 's' : '');
        }

        if ($meses > 0) {
            if ($antiguedad != '') {
                $antiguedad .= ' y ';
            }

            $antiguedad .= $meses . ' mes' . ($meses > 1 ? 'es' : '');
        }

        if ($antiguedad == '') {
            $antiguedad = '0 meses';
        }

        $cantidadAnterior = $asociado->aportes_activo()
        ->whereNotIn('id', $idsUltimos)
        ->count();

        $ultimoAporte = $aportesUltimos->first();

        return view('aporte', compact(
            'asociado',
            'aportesUltimos',
            'totalAportado',
            'cantidadAportes',
            'totalAnterior',
            'cantidadAnterior',
            'ultimoAporte',
            'antiguedad'
        ));
    }

    public function persona_foto_actualizar(Persona $persona, Request $request)
    {
        $request->validate([
            'selfi' => [
                'required',
                'image',
                'mimes:jpg,jpeg',
            ],
        ]);

        $ruta = $request
        ->file('selfi')
        ->store('personas/selfis', 'public');

        $persona->selfi = $ruta;
        $persona->save();

        return redirect()->route('home');
    }

    public function solicitudes()
    {
        $anio = now()->year;
        $persona = auth()->user()->persona;

        if (!$persona) {
            return redirect()->route('home')->withErrors(['persona' => 'No se encontró una persona vinculada a su usuario.',]);
        }
        $tablaAyuda = (new SolicitudAyudaSocial())->getTable();
        $tablaActualizacion =(new SolicitudActualizacionDatos())->getTable();
        /*
        |--------------------------------------------------------------------------
        | Solicitudes de ayuda social
        |--------------------------------------------------------------------------
        */
        $ayudaSocial = SolicitudAyudaSocial::query()
        ->join(
            'estado_solicituds as estado_ayuda',
            'estado_ayuda.id',
            '=',
            $tablaAyuda . '.estado_solicitud_id'
        )
        ->where(
            $tablaAyuda . '.persona_id',
            $persona->id
        )
        ->where($tablaAyuda . '.anio', $anio)
        ->where($tablaAyuda . '.estado_id', 1)
        ->select([
            $tablaAyuda . '.id',
            $tablaAyuda . '.anio',
            $tablaAyuda . '.numero',
            $tablaAyuda . '.fecha_solicitud',

            DB::raw(
                "'AYUDA_SOCIAL' AS tipo_codigo"
            ),

            DB::raw(
                "'AYUDA SOCIAL' AS tipo_solicitud"
            ),

            $tablaAyuda . '.monto_aprobado as monto',

            'estado_ayuda.descripcion as estado_descripcion',
            'estado_ayuda.color as estado_color',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Solicitudes de actualización de datos
        |--------------------------------------------------------------------------
        */

        $actualizacionDatos =
        SolicitudActualizacionDatos::query()
        ->join(
            'estado_solicituds as estado_actualizacion',
            'estado_actualizacion.id',
            '=',
            $tablaActualizacion . '.estado_solicitud_id'
        )
        ->where(
            $tablaActualizacion . '.persona_id',
            $persona->id
        )
        ->where($tablaActualizacion . '.anio', $anio)
        ->where($tablaActualizacion . '.estado_id', 1)
        ->select([
            $tablaActualizacion . '.id',
            $tablaActualizacion . '.anio',
            $tablaActualizacion . '.numero',
            $tablaActualizacion . '.fecha_solicitud',

            DB::raw(
                "'ACTUALIZACION_DATOS' AS tipo_codigo"
            ),

            DB::raw(
                "'ACTUALIZACIÓN DE DATOS' AS tipo_solicitud"
            ),

            DB::raw('0 AS monto'),

            'estado_actualizacion.descripcion as estado_descripcion',
            'estado_actualizacion.color as estado_color',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Unir y paginar
        |--------------------------------------------------------------------------
        */

        $consulta = $ayudaSocial->unionAll($actualizacionDatos);

        $data = DB::query()
        ->fromSub($consulta, 'solicitudes')
        ->orderByDesc('fecha_solicitud')
        ->orderByDesc('numero')
        ->paginate(15)
        ->withQueryString();

        return view('portal.solicitud_index',compact('data'));
    }
    public function nueva_solicitud()
    {
        return view('portal.seleccionar_solicitud');
    }

    public function ayuda_social()
    {
        $entidad = Entidad::find(1);
        if($entidad->activo_ayuda_social == 0) {
            return redirect()->route('nueva_solicitud')->withErrors('La recepción de solicitudes de ayuda social se encuentra temporalmente inhabilitada. Por favor, intente nuevamente más adelante.');
        }

        $persona = Persona::where('documento', auth()->user()->documento)
        ->first();

        if ($persona->asociado->estado_id == 2){
            return redirect()->route('nueva_solicitud')->withErrors('No es posible registrar la solicitud porque actualmente no figura como asociado activo de AJUPEM. Para más información, comuníquese con la administración.');
        }

        $solicitud_activa = SolicitudAyudaSocial::where('persona_id', $persona->id)
        ->whereIn('estado_solicitud_id', [1,2])
        ->first();

        if ($solicitud_activa){
            return redirect()->route('nueva_solicitud')->withErrors([
            'ayuda_social' => 'Ya cuenta con una solicitud de ayuda social pendiente o en proceso, N.º '
                . $solicitud_activa->numero
                . '/'
                . $solicitud_activa->anio
                . '. Debe esperar su resolución antes de presentar una nueva solicitud.',
            ]);
        }

        $anioActual = now()->year;
        $limiteAnual = (int) $entidad->limite_ayuda_social;
        $solicitudes_anio = SolicitudAyudaSocial::where('persona_id', $persona->id)
        ->whereIn('estado_solicitud_id', [1,2,3])
        ->where('anio', $anioActual)
        ->count();

        if ($solicitudes_anio >= $limiteAnual){
            $textoSolicitud = $limiteAnual === 1 ? 'solicitud' : 'solicitudes';
            return redirect()
            ->route('nueva_solicitud')
            ->withErrors([
                'ayuda_social' => 'Ha alcanzado el límite anual de '
                . $limiteAnual . ' '
                . $textoSolicitud
                . ' de ayuda social establecido por AJUPEM para el año '
                . $anioActual
                . '.',
            ]);
        }

        return view('portal.ayuda_social', compact('persona'));
    }

    public function ayuda_social_store(Request $request)
    {
        $request->validate([
            'motivo' => [
                'required',
                'string',
                'max:3000',
            ],
            'documento_respaldo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,pdf',
                'max:51200', // 50 MB
            ],
        ], [
            'monto_solicitado.required' =>
                'Debe ingresar el monto solicitado.',

            'monto_solicitado.min' =>
                'El monto solicitado debe ser mayor a cero.',

            'motivo.required' =>
                'Debe explicar el motivo de la solicitud.',

            'documento_respaldo.mimes' =>
                'El documento debe ser JPG, JPEG o PDF.',

            'documento_respaldo.max' =>
                'El documento no debe superar los 50 MB.',
        ]);

        $rutaDocumento = null;
        if ($request->hasFile('documento_respaldo')) {
            $rutaDocumento = $request->file('documento_respaldo')->store('solicitudes/ayuda-social', 'public');
        }

        $persona = $request->user()->persona;

        DB::beginTransaction();

        try {
            $anio = now()->year;

            $numeracion = Numeraciones::where('tipo', 4)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

            if (!$numeracion) {
                $numero = 1;
                Numeraciones::create([
                    'tipo' => 4,
                    'anio' => $anio,
                    'descripcion' => 'Solicitud de ayuda social',
                    'numero' => 2
                ]);
            } else {
                $numero = $numeracion->numero;
                $numeracion->numero = $numero + 1;
                $numeracion->save();
            }

            $new_solicitud = SolicitudAyudaSocial::create([
                'anio' => $anio,
                'numero' => $numero,
                'fecha_solicitud' => now(),
                'persona_id' => $persona->id,
                'beneficiario' => '',
                'motivo' => $request->motivo,
                'monto_solicitado' => 0,
                'monto_aprobado' => 0,
                'documento_respaldo' => $rutaDocumento,
                'estado_solicitud_id' => 1,
                'fecha_resolucion' => null,
                'motivo_rechazo' => '',
                'observacion' => '',
                'orden_pago_id' => null,
                'estado_id' => 1,
                'user_id' => auth()->user()->id,
                'usuario_modificacion' => auth()->user()->id,
                'fecha_anulacion' => null,
                'motivo_anulacion' => '',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('ayuda_social')->withErrors($e->getMessage());
        }

        return redirect()->route('solicitudes')->with(
            'message',
            'La solicitud de ayuda social N.º '
            . str_pad($new_solicitud->numero, 5, '0', STR_PAD_LEFT)
            . '/'
            . $new_solicitud->anio
            . ' fue registrada correctamente y se encuentra pendiente de revisión.'
        );
    }

    public function ayuda_social_show(int $id)
    {
        $persona = auth()->user()->persona;
        $data = SolicitudAyudaSocial::find($id);

        if ($persona->id <> $data->persona_id){
            return redirect()->route('solicitudes')->withErrors('La solicitud seleccionada no existe o no tiene autorización para consultarla.');
        }

        return view('portal.ayuda_social_show', compact('data','persona'));
    }

    public function actualizar_datos()
    {
        $persona = auth()->user()->persona;
        $instituciones = Institucion::where('estado_id', 1)
        ->orderBy('descripcion')
        ->get();
        $solicitudActiva = SolicitudActualizacionDatos::where('persona_id',$persona->id)
        ->whereIn('estado_solicitud_id', [1, 2])
        ->where('estado_id', 1)
        ->orderByDesc('fecha_solicitud')
        ->first();

        if ($solicitudActiva) {
            return redirect()
            ->route('nueva_solicitud')
            ->withErrors([
                'actualizacion' => 'Ya cuenta con una solicitud de actualización de datos N.º '
                    . str_pad(
                        $solicitudActiva->numero,
                        5,
                        '0',
                        STR_PAD_LEFT
                    )
                    . '/'
                    . $solicitudActiva->anio
                    . ' pendiente o en proceso. Debe esperar su resolución antes de presentar una nueva solicitud.',
            ]);
        }
        return view('portal.actualizacion_datos', compact('persona','instituciones'));
    }

    public function actualizar_datos_post(Request $request)
    {
        $persona = $request->user()->persona;

        if (!$persona) {
            return redirect()->route('nueva_solicitud')->withErrors(['persona' => 'No se encontró una persona vinculada a su usuario.',]);
        }
        /*
        |--------------------------------------------------------------------------
        | Validar que haya seleccionado algún cambio
        |--------------------------------------------------------------------------
        */

        $opcionesCambio = [
            'cambiar_documento',
            'cambiar_nombre',
            'cambiar_apellido',
            'cambiar_fecha_nacimiento',
            'cambiar_institucion',
            'cambiar_email',
            'cambiar_celular',
            'cambiar_documentos',
        ];

        $seleccionoCambio = collect($opcionesCambio)
        ->contains(function ($campo) use ($request) {
            return $request->boolean($campo);
        });

        if (!$seleccionoCambio) {
            return redirect()->back()->withInput()->withErrors(['actualizacion' => 'Debe seleccionar al menos un dato para actualizar.',]);
        }
        /*
        |--------------------------------------------------------------------------
        | Validaciones
        |--------------------------------------------------------------------------
        */
        $validator = Validator::make(
            $request->all(),
            [
                'cambiar_documento' => [
                    'nullable',
                    'in:1',
                ],
                'documento_nuevo' => [
                    'exclude_unless:cambiar_documento,1',
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('personas', 'documento')
                        ->ignore($persona->id),
                ],

                'cambiar_nombre' => [
                    'nullable',
                    'in:1',
                ],
                'nombre_nuevo' => [
                    'exclude_unless:cambiar_nombre,1',
                    'required',
                    'string',
                    'max:200',
                ],

                'cambiar_apellido' => [
                    'nullable',
                    'in:1',
                ],
                'apellido_nuevo' => [
                    'exclude_unless:cambiar_apellido,1',
                    'required',
                    'string',
                    'max:200',
                ],

                'cambiar_fecha_nacimiento' => [
                    'nullable',
                    'in:1',
                ],
                'fecha_nacimiento_nueva' => [
                    'exclude_unless:cambiar_fecha_nacimiento,1',
                    'required',
                    'date',
                    'before_or_equal:today',
                ],

                'cambiar_institucion' => [
                    'nullable',
                    'in:1',
                ],
                'institucion_municipal_id_nueva' => [
                    'exclude_unless:cambiar_institucion,1',
                    'required',
                    'integer',
                    Rule::exists('institucions', 'id')
                        ->where('estado_id', 1),
                ],

                'cambiar_email' => [
                    'nullable',
                    'in:1',
                ],
                'email_nuevo' => [
                    'exclude_unless:cambiar_email,1',
                    'required',
                    'email',
                    'max:250',
                    Rule::unique('personas', 'email')
                        ->ignore($persona->id),
                ],

                'cambiar_celular' => [
                    'nullable',
                    'in:1',
                ],
                'celular_nuevo' => [
                    'exclude_unless:cambiar_celular,1',
                    'required',
                    'string',
                    'max:255',
                ],

                'cambiar_documentos' => [
                    'nullable',
                    'in:1',
                ],
                'documento_frente_nuevo' => [
                    'exclude_unless:cambiar_documentos,1',
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png',
                    'max:51200',
                ],
                'documento_reverso_nuevo' => [
                    'exclude_unless:cambiar_documentos,1',
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png',
                    'max:51200',
                ],

                'motivo' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'documento_nuevo.required' =>
                    'Debe ingresar el nuevo número de documento.',

                'documento_nuevo.unique' =>
                    'El número de documento ingresado ya pertenece a otra persona.',

                'nombre_nuevo.required' =>
                    'Debe ingresar el nuevo nombre.',

                'apellido_nuevo.required' =>
                    'Debe ingresar el nuevo apellido.',

                'fecha_nacimiento_nueva.required' =>
                    'Debe ingresar la nueva fecha de nacimiento.',

                'fecha_nacimiento_nueva.before_or_equal' =>
                    'La fecha de nacimiento no puede ser posterior a la fecha actual.',

                'institucion_municipal_id_nueva.required' =>
                    'Debe seleccionar la nueva institución municipal.',

                'institucion_municipal_id_nueva.exists' =>
                    'La institución seleccionada no es válida.',

                'email_nuevo.required' =>
                    'Debe ingresar el nuevo correo electrónico.',

                'email_nuevo.email' =>
                    'El correo electrónico ingresado no es válido.',

                'email_nuevo.unique' =>
                    'El correo electrónico ingresado ya pertenece a otra persona.',

                'celular_nuevo.required' =>
                    'Debe ingresar el nuevo número de celular.',

                'documento_frente_nuevo.required' =>
                    'Debe adjuntar la fotografía del frente de la cédula.',

                'documento_reverso_nuevo.required' =>
                    'Debe adjuntar la fotografía del reverso de la cédula.',

                'documento_frente_nuevo.mimes' =>
                    'La fotografía del frente debe ser JPG, JPEG o PNG.',

                'documento_reverso_nuevo.mimes' =>
                    'La fotografía del reverso debe ser JPG, JPEG o PNG.',

                'documento_frente_nuevo.max' =>
                    'La fotografía del frente no debe superar los 50 MB.',

                'documento_reverso_nuevo.max' =>
                    'La fotografía del reverso no debe superar los 50 MB.',

                'motivo.required' =>
                    'Debe indicar el motivo de la actualización.',
            ]
        );
        /*
        |--------------------------------------------------------------------------
        | Validar que el dato nuevo sea diferente al actual
        |--------------------------------------------------------------------------
        */
        $validator->after(function ($validator) use ($request, $persona) {

            if (
                $request->boolean('cambiar_documento') &&
                trim($request->documento_nuevo) ===
                trim($persona->documento)
            ) {
                $validator->errors()->add(
                    'documento_nuevo',
                    'El nuevo documento debe ser diferente al actual.'
                );
            }

            if (
                $request->boolean('cambiar_nombre') &&
                mb_strtoupper(trim($request->nombre_nuevo)) ===
                mb_strtoupper(trim($persona->nombre))
            ) {
                $validator->errors()->add(
                    'nombre_nuevo',
                    'El nuevo nombre debe ser diferente al actual.'
                );
            }

            if (
                $request->boolean('cambiar_apellido') &&
                mb_strtoupper(trim($request->apellido_nuevo)) ===
                mb_strtoupper(trim($persona->apellido))
            ) {
                $validator->errors()->add(
                    'apellido_nuevo',
                    'El nuevo apellido debe ser diferente al actual.'
                );
            }

            if ($request->boolean('cambiar_fecha_nacimiento')) {
                $fechaActual = $persona->fecha_nacimiento
                    ? Carbon::parse(
                        $persona->fecha_nacimiento
                    )->toDateString()
                    : null;

                if (
                    $request->fecha_nacimiento_nueva ===
                    $fechaActual
                ) {
                    $validator->errors()->add(
                        'fecha_nacimiento_nueva',
                        'La nueva fecha de nacimiento debe ser diferente a la actual.'
                    );
                }
            }

            if (
                $request->boolean('cambiar_email') &&
                mb_strtolower(trim($request->email_nuevo)) ===
                mb_strtolower(trim($persona->email))
            ) {
                $validator->errors()->add(
                    'email_nuevo',
                    'El nuevo correo electrónico debe ser diferente al actual.'
                );
            }

            if (
                $request->boolean('cambiar_celular') &&
                trim($request->celular_nuevo) ===
                trim($persona->celular)
            ) {
                $validator->errors()->add(
                    'celular_nuevo',
                    'El nuevo número de celular debe ser diferente al actual.'
                );
            }

            /*
            * Ajustar institucion_municipal_id al nombre real
            * de la columna del modelo Asociado.
            */
            if (
                $request->boolean('cambiar_institucion') &&
                (int) $request->institucion_municipal_id_nueva ===
                (int) $persona->asociado?->institucion_municipal_id
            ) {
                $validator->errors()->add(
                    'institucion_municipal_id_nueva',
                    'Debe seleccionar una institución diferente a la actual.'
                );
            }
        });
        $datosValidados = $validator->validate();

        $rutaFrenteNueva = null;
        $rutaReversoNueva = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Guardar documentos nuevos
            |--------------------------------------------------------------------------
            */

            if ($request->boolean('cambiar_documentos') && $request->hasFile('documento_frente_nuevo')) {
                $rutaFrenteNueva = $request->file('documento_frente_nuevo')->store('solicitudes/actualizacion-datos','public');
            }

            if ($request->boolean('cambiar_documentos') && $request->hasFile('documento_reverso_nuevo')) {
                $rutaReversoNueva = $request->file('documento_reverso_nuevo')->store('solicitudes/actualizacion-datos','public');
            }
            /*
            |--------------------------------------------------------------------------
            | Transacción
            |--------------------------------------------------------------------------
            */
            $solicitud = DB::transaction(function () use (
                $request,
                $datosValidados,
                $persona,
                $rutaFrenteNueva,
                $rutaReversoNueva,
            ) {
                $anio = now()->year;
                /*
                |--------------------------------------------------------------------------
                | Numeración
                |--------------------------------------------------------------------------
                */
                $numeracion = Numeraciones::where('tipo', 5)
                ->where('anio', $anio)
                ->lockForUpdate()
                ->first();

                if (!$numeracion) {
                    $numero = 1;
                    Numeraciones::create([
                        'tipo'        => 5,
                        'anio'        => $anio,
                        'descripcion' => 'Solicitud de actualización de datos',
                        'numero'      => 2,
                    ]);
                } else {
                    $numero = $numeracion->numero;
                    $numeracion->numero = $numero + 1;
                    $numeracion->save();
                }
                /*
                |--------------------------------------------------------------------------
                | Crear solicitud
                |--------------------------------------------------------------------------
                */


                return SolicitudActualizacionDatos::create([
                    'anio'              => $anio,
                    'numero'            => $numero,
                    'fecha_solicitud'   => now()->toDateString(),
                    'persona_id'        => $persona->id,
                    /*
                    * Documento
                    */
                    'documento_actual'  => $persona->documento,
                    // 'documento_nuevo'   =>$request->boolean('cambiar_documento') ? strtoupper(trim($datosValidados['documento_nuevo'])) : null,
                    'documento_nuevo'   => null,
                    /*
                    * Nombre
                    */
                    'nombre_actual'     => $persona->nombre,
                    'nombre_nuevo'      => $request->boolean('cambiar_nombre') ? strtoupper(trim($datosValidados['nombre_nuevo'])) : null,
                    /*
                    * Apellido
                    */
                    'apellido_actual'   => $persona->apellido,
                    'apellido_nuevo'    => $request->boolean('cambiar_apellido') ? strtoupper(trim($datosValidados['apellido_nuevo'])) : null,
                    /*
                    * Fecha de nacimiento
                    */
                    'fecha_nacimiento_actual' => $persona->fecha_nacimiento,
                    'fecha_nacimiento_nueva' => $request->boolean('cambiar_fecha_nacimiento') ? $datosValidados['fecha_nacimiento_nueva'] : null,
                    /*
                    * Institución municipal
                    *
                    * Ajustar institucion_municipal_id al nombre
                    * real de la columna del asociado.
                    */
                    'institucion_municipal_id_actual' => $persona->asociado?->institucion_id,
                    'institucion_municipal_id_nueva' => $request->boolean('cambiar_institucion') ? $datosValidados['institucion_municipal_id_nueva'] : null,
                    /*
                    * Correo
                    */
                    'email_actual'      => $persona->email,
                    'email_nuevo'       => $request->boolean('cambiar_email') ? strtolower(trim($datosValidados['email_nuevo'])) : null,
                    /*
                    * Celular
                    */
                    'celular_actual'    => $persona->celular,
                    'celular_nuevo'     => $request->boolean('cambiar_celular') ? trim($datosValidados['celular_nuevo']) : null,
                    /*
                    * Fotografías de la cédula
                    */
                    'documento_frente_actual' => $persona->documento_frente,
                    'documento_frente_nuevo' => $rutaFrenteNueva,
                    'documento_reverso_actual' => $persona->documento_reverso,
                    'documento_reverso_nuevo' => $rutaReversoNueva,
                    /*
                    * Motivo y estado
                    */
                    'motivo'                => trim($datosValidados['motivo']),
                    'estado_solicitud_id'   => 1,
                    'fecha_resolucion'      => null,
                    'usuario_resolucion'    => auth()->user()->id,
                    'motivo_rechazo'        => '',
                    'observacion'           => '',
                    'campos_aprobados' => null,
                    'campos_no_aprobados' => null,
                    /*
                    * Auditoría
                    */
                    'estado_id'             => 1,
                    'user_id'               => $request->user()->id,
                    'usuario_modificacion'  => $request->user()->id,
                ]);
            });

            return redirect()->route('solicitudes')
            ->with(
                'message',
                'La solicitud de actualización de datos N.º '
                . str_pad(
                    $solicitud->numero,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
                . '/'
                . $solicitud->anio
                . ' fue registrada correctamente y se encuentra pendiente de revisión.'
            );

        } catch (\Throwable $e) {

            /*
            * Si falla la base de datos, eliminar los archivos
            * que ya fueron guardados.
            */
            if ($rutaFrenteNueva) {
                Storage::disk('public')->delete($rutaFrenteNueva);
            }

            if ($rutaReversoNueva) {
                Storage::disk('public')->delete($rutaReversoNueva);
            }
            report($e);
            return redirect()
            ->route('actualizar_datos')
            ->withInput()
            ->withErrors([
                'actualizacion' => $e->getMessage(),
            ]);
        }
    }

    public function actualizacion_datos_show($id)
    {
        $persona = auth()->user()->persona;

        if (!$persona) {
            return redirect()->route('solicitudes')->withErrors(['persona' => 'No se encontró una persona vinculada a su usuario.',]);
        }

        $data = SolicitudActualizacionDatos::with(['estadoSolicitud','institucionActual','institucionNueva',])
        ->where('id', $id)
        ->where('persona_id', $persona->id)
        ->where('estado_id', 1)
        ->first();

        if (!$data) {
            return redirect()->route('solicitudes')->withErrors(['solicitud' => 'La solicitud seleccionada no existe o no tiene autorización para consultarla.',]);
        }

        return view('portal.actualizacion_datos_show',compact('data'));
    }

}
