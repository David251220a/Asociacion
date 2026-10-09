<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use App\Models\Miembro;
use App\Models\MiembroPlanilla;
use App\Models\MiembroPlanillaDetalle;
use App\Models\Numeraciones;
use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\Persona;
use App\Models\TipoEgreso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipStream\Bigint;

class MiembroController extends Controller
{
    private const TIPO_NUMERACION_PLANILLA_MIEMBROS = 8;
    private const TIPO_EGRESO_DIETA_MIEMBROS = 5;
    private const tipos = ['PRESIDENTE', 'VICEPRESIDENTE', 'SECRETARIO', 'TESORERA', 'PRO-TESORERA', 'MIEMBROS', 'SINDICO'];
    public function __construct()
    {
        $this->middleware('permission:miembros.index')->only('index');
    }

    public function index()
    {
        $data = Miembro::where('estado_id', 1)
        ->orderBy('tipo', 'ASC')->get();
        $tipos = self::tipos;
        return view('miembro.index', compact('data', 'tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'crear_nombre' => 'required',
            'crear_apellido' => 'required',
            'crear_tipo_miembro' => 'required',
            'crear_dieta' => 'required',
            'crear_documento' => 'required',
            'crear_percibe_dieta' => 'required',
        ]);

        $existe = Miembro::where('documento', $request->crear_documento)->first();
        if ($existe) {
            return redirect()->route('miembros.index')->with('error', 'El documento ya existe en la base de datos.');
        }

        Miembro::create([
            'documento' => $request->crear_documento,
            'nombre' => mb_strtoupper($request->crear_nombre, 'UTF-8'),
            'apellido' => mb_strtoupper($request->crear_apellido, 'UTF-8'),
            'tipo' => $request->crear_tipo_miembro,
            'presente' => 1,
            'pago' => $request->crear_percibe_dieta,
            'monto_remuneracion' => str_replace('.', '', $request->crear_dieta),
            'adelanto' => 0,
            'neto' => str_replace('.', '', $request->crear_dieta),
            'estado_id' => 1,
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
            'editar_percibe_dieta' => 'required',
            'editar_dieta' => 'required',
            'editar_documento' => 'required',
            'editar_estado_id' => 'required',
        ]);

        $miembro = Miembro::find($request->miembro_id);
        $miembro->update([
            'documento' => $request->editar_documento,
            'nombre' => mb_strtoupper($request->editar_nombre, 'UTF-8'),
            'apellido' => mb_strtoupper($request->editar_apellido, 'UTF-8'),
            'tipo' => $request->editar_tipo_miembro,
            'monto_remuneracion' => str_replace('.', '', $request->editar_dieta),
            'pago' => $request->editar_percibe_dieta,
            'neto' => str_replace('.', '', $request->editar_dieta),
            'estado_id' => $request->editar_estado_id,
        ]);

        return redirect()->route('miembros.index')->with('message', 'Miembro editado correctamente.');
    }

    public function cambiarPresente(Bigint $id)
    {
        $miembro = Miembro::findOrFail($id);
        $miembro->presente = 1 - $miembro->presente; // 0→1, 1→0
        $miembro->save();
        return redirect()->route('miembros.index')->with('message', 'Miembro presente cambiado.');
    }


    public function planilla_index()
    {
        $data = MiembroPlanilla::query()
        ->where('estado_id', 1)
        ->orderByDesc('anio')
        ->orderByDesc('mes')
        ->orderByDesc('numero')
        ->paginate(30)
        ->withQueryString();

        $periodosOcupados = MiembroPlanilla::query()
        ->where('estado_id', 1)
        ->where('estado_planilla', '<>', 3)
        ->get(['anio','mes'])
        ->mapWithKeys(function ($planilla) {
            $clave = $planilla->anio
                . '-'
                . $planilla->mes;

            return [$clave => true];
        })
        ->all();

        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return view('miembro.planilla_index',compact('data','periodosOcupados','meses'));
    }

    public function crear_planilla()
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $cantidadMiembros = Miembro::query()
        ->where('estado_id', 1)
        ->where('pago', 1)
        ->count();

        $totalRemuneracion = Miembro::query()
        ->where('estado_id', 1)
        ->where('pago', 1)
        ->sum('monto_remuneracion');

        $totalAdelanto = Miembro::query()
        ->where('estado_id', 1)
        ->where('pago', 1)
        ->sum('adelanto');

        $totalNeto = Miembro::query()
        ->where('estado_id', 1)
        ->where('pago', 1)
        ->sum('neto');

        return view('miembro.planilla_crear', compact('meses','cantidadMiembros','totalRemuneracion','totalAdelanto','totalNeto'));
    }

    public function guardar_planilla(Request $request)
    {
        $datos = $request->validate([
            'mes' => ['required','integer','between:1,12'],
            'anio' => ['required','integer','min:2020','max:' . (now()->year + 1)],
        ], [
            'mes.required' => 'Debe seleccionar el mes.',
            'mes.between' => 'El mes seleccionado no es válido.',
            'anio.required' => 'Debe seleccionar el año.',
            'anio.min' => 'El año seleccionado no es válido.',
            'anio.max' => 'El año seleccionado no es válido.',
        ]);

        try {
            $planilla = DB::transaction(
                function () use ($datos) {
                    $usuarioId = (int) auth()->id();
                    $mes = (int) $datos['mes'];
                    $anio = (int) $datos['anio'];
                    $ahora = now();

                    /*
                    |--------------------------------------------------------------------------
                    | NO PERMITIR REPETIR MES Y AÑO
                    |--------------------------------------------------------------------------
                    |
                    | También bloquea si la planilla encontrada está anulada.
                    | La anulada solamente se procesa desde Regenerar.
                    |
                    */
                    $existePlanilla = MiembroPlanilla::query()
                    ->where('anio', $anio)
                    ->where('mes', $mes)
                    ->lockForUpdate()
                    ->exists();

                    if ($existePlanilla) {
                        throw new \Exception(
                            'Ya existe una planilla para '
                            . str_pad($mes, 2, '0', STR_PAD_LEFT)
                            . '/'
                            . $anio
                            . '. Si está anulada, debe utilizar la opción Regenerar.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | MIEMBROS HABILITADOS PARA COBRAR
                    |--------------------------------------------------------------------------
                    */
                    $miembros = Miembro::query()
                    ->where('estado_id', 1)
                    ->where('pago', 1)
                    ->orderBy('tipo')
                    ->orderBy('apellido')
                    ->orderBy('nombre')
                    ->lockForUpdate()
                    ->get();

                    if ($miembros->isEmpty()) {
                        throw new \Exception('No existen miembros activos habilitados para percibir remuneración.');
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDAR IMPORTES CONFIGURADOS
                    |--------------------------------------------------------------------------
                    */
                    foreach ($miembros as $miembro) {
                        $montoRemuneracion = (int) $miembro->monto_remuneracion;
                        $adelanto = (int) $miembro->adelanto;

                        if ($montoRemuneracion <= 0) {
                            throw new \Exception(
                                'El miembro '
                                . trim(
                                    $miembro->nombre
                                    . ' '
                                    . $miembro->apellido
                                )
                                . ' no tiene una remuneración configurada.'
                            );
                        }

                        if ($adelanto < 0) {
                            throw new \Exception(
                                'El adelanto del miembro '
                                . trim(
                                    $miembro->nombre
                                    . ' '
                                    . $miembro->apellido
                                )
                                . ' no puede ser negativo.'
                            );
                        }

                        if ($adelanto > $montoRemuneracion) {
                            throw new \Exception(
                                'El adelanto del miembro '
                                . trim(
                                    $miembro->nombre
                                    . ' '
                                    . $miembro->apellido
                                )
                                . ' supera su remuneración.'
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | OBTENER NUMERACIÓN
                    |--------------------------------------------------------------------------
                    */
                    $numeracion = Numeraciones::query()
                    ->where('tipo',self::TIPO_NUMERACION_PLANILLA_MIEMBROS)
                    ->where('anio', $anio)
                    ->lockForUpdate()
                    ->first();

                    if (!$numeracion) {
                        $numero = 1;
                        Numeraciones::create([
                            'tipo' => self::TIPO_NUMERACION_PLANILLA_MIEMBROS,
                            'anio' => $anio,
                            'descripcion' => 'Planilla de miembros',
                            'numero' => 2,
                        ]);
                    } else {
                        $numero = (int) $numeracion->numero;
                        $numeracion->numero = $numero + 1;
                        $numeracion->save();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PREPARAR DETALLES Y TOTALES
                    |--------------------------------------------------------------------------
                    */
                    $detalles = [];

                    $totalRemuneracion = 0;
                    $totalAdelanto = 0;
                    $totalNeto = 0;

                    foreach ($miembros as $miembro) {
                        $montoRemuneracion = (int) $miembro->monto_remuneracion;
                        $adelanto = (int) $miembro->adelanto;
                        $neto = max(0,$montoRemuneracion - $adelanto);
                        $totalRemuneracion += $montoRemuneracion;
                        $totalAdelanto += $adelanto;
                        $totalNeto += $neto;

                        $detalles[] = [
                            'miembro_id' => $miembro->id,
                            'nombre_completo' => trim( $miembro->nombre . ' ' . $miembro->apellido),
                            'tipo' => $miembro->tipo,
                            'monto_remuneracion' => $montoRemuneracion,
                            'adelanto' => $adelanto,
                            'neto' => $neto,
                            'estado_pago' => 1,
                            'fecha_pago' => null,
                            'estado_id' => 1,
                            'user_id' => $usuarioId,
                            'usuario_modificacion' => $usuarioId,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CREAR CABECERA
                    |--------------------------------------------------------------------------
                    */
                    $planilla = MiembroPlanilla::create([
                        'numero' => $numero,
                        'anio' => $anio,
                        'mes' => $mes,
                        'fecha_generacion' => $ahora->toDateString(),
                        'cantidad' => $miembros->count(),
                        'total_remuneracion' => $totalRemuneracion,
                        'total_adelanto' => $totalAdelanto,
                        'total_neto' => $totalNeto,
                        'estado_planilla' => 1,
                        'fecha_pago' => null,
                        'fecha_anulacion' => null,
                        'motivo_anulacion' => null,
                        'estado_id' => 1,
                        'user_id' => $usuarioId,
                        'usuario_modificacion' => $usuarioId,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | VINCULAR DETALLES A LA CABECERA
                    |--------------------------------------------------------------------------
                    */
                    foreach ($detalles as &$detalle) {
                        $detalle['miembro_planilla_id'] = $planilla->id;
                    }

                    unset($detalle);

                    MiembroPlanillaDetalle::insert(
                        $detalles
                    );

                    return $planilla;
                }
            );

            return redirect()
                ->route('miembros.planillas.index')
                ->with(
                    'message',
                    'La planilla N.º '
                    . str_pad(
                        $planilla->numero,
                        5,
                        '0',
                        STR_PAD_LEFT
                    )
                    . '/'
                    . $planilla->anio
                    . ' fue generada correctamente.'
                );
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['planilla' => $e->getMessage()])->withInput();
        }
    }

    public function generar_orden_pago(MiembroPlanilla $planilla)
    {
        try {
            $orden = DB::transaction(
                function () use ($planilla) {
                    $usuarioId = (int) auth()->id();
                    $ahora = now();
                    $anio = (int) $ahora->year;

                    $planilla = MiembroPlanilla::query()
                    ->with([
                        'detalles' => function ($query) {
                            $query->where('estado_id', 1)
                            ->orderBy('tipo')
                            ->orderBy('nombre_completo');
                        },
                    ])
                    ->lockForUpdate()
                    ->findOrFail($planilla->id);

                    $entidad = Entidad::find(1);
                    $persona = Persona::where('documento', $entidad->ruc_sin_digito)->first();
                    if (!$persona) {
                        throw new \Exception('No se encontró la persona correspondiente a la entidad.');
                    }

                    if ((int) $planilla->estado_planilla !== 1) {
                        throw new \Exception( 'La planilla no se encuentra en estado Generada.');
                    }

                    if ($planilla->orden_pago_id) {
                        throw new \Exception('La planilla ya tiene una orden de pago generada.');
                    }

                    if ($planilla->detalles->isEmpty()) {
                        throw new \Exception('La planilla no tiene miembros para pagar.');
                    }

                    $totalDetalles = (int) $planilla
                    ->detalles
                    ->sum('neto');

                    if ($totalDetalles  !== (int) $planilla->total_neto) {
                        throw new \Exception('El total de los detalles no coincide con el neto de la planilla.');
                    }

                    if ($totalDetalles <= 0) {
                        throw new \Exception('La planilla no tiene un importe neto para pagar.');
                    }

                    $tipoEgreso = TipoEgreso::findOrFail(self::TIPO_EGRESO_DIETA_MIEMBROS);

                    /*
                    |--------------------------------------------------------------------------
                    | NUMERACIÓN DE ORDEN DE PAGO
                    |--------------------------------------------------------------------------
                    */
                    $numeracion = Numeraciones::query()
                    ->where('tipo', 3)
                    ->where('anio', $anio)
                    ->lockForUpdate()
                    ->first();

                    if (!$numeracion) {
                        $numeroOrden = 1;
                        Numeraciones::create([
                            'tipo' => 3,
                            'anio' => $anio,
                            'descripcion' => 'Orden de Pago',
                            'numero' => 2,
                        ]);
                    } else {
                        $numeroOrden = (int) $numeracion->numero;
                        $numeracion->numero = $numeroOrden + 1;
                        $numeracion->save();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CABECERA DE LA ORDEN
                    |--------------------------------------------------------------------------
                    */
                    $orden = OrdenPago::create([
                        'anio' => $anio,
                        'numero' => $numeroOrden,
                        'fecha' => $ahora->toDateString(),
                        'tipo_egreso_id' => $tipoEgreso->id,
                        'origen_id' => $planilla->id,
                        'persona_id' => $persona->id,
                        'beneficiario' => 'MIEMBROS DE LA INSTITUCIÓN',
                        'concepto' => 'PAGO DE DIETAS A MIEMBROS CORRESPONDIENTE A '. str_pad( $planilla->mes,2,'0',STR_PAD_LEFT). '/'. $planilla->anio,
                        'observacion' => 'PLANILLA N.º '. str_pad( $planilla->numero, 5,'0', STR_PAD_LEFT). '/'. $planilla->anio,
                        'total' => $totalDetalles,
                        'estado_id' => 1,
                        'estado_pago' => 0,
                        'motivo_anulado' => null,
                        'fecha_anulado' => null,
                        'fecha_pago' => null,
                        'user_id' => $usuarioId,
                        'usuario_modificacion' => $usuarioId,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | UN DETALLE POR MIEMBRO
                    |--------------------------------------------------------------------------
                    */
                    foreach ($planilla->detalles as $detalle) {
                        if ((int) $detalle->neto <= 0) {
                            continue;
                        }

                        OrdenPagoDetalle::create([
                            'orden_pago_id' => $orden->id,
                            'descripcion' => 'DIETA - '. mb_strtoupper($detalle->nombre_completo,'UTF-8'),
                            'cantidad' => 1,
                            'precio' => (int) $detalle->neto,
                            'subtotal' => (int) $detalle->neto,
                            'estado_id' => 1,
                            'user_id' => $usuarioId,
                            'usuario_modificacion' => $usuarioId,
                        ]);
                    }

                    /*
                    * Solamente se vincula la orden.
                    * Todavía no se marca la planilla como pagada.
                    */
                    $planilla->update([
                        'orden_pago_id' => $orden->id,
                        'usuario_modificacion' => $usuarioId,
                    ]);

                    return $orden;
                }
            );

            return redirect()->route('orden.pago', $orden->id)->with('message','La orden de pago fue generada correctamente.');

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['planilla' => $e->getMessage(),]);
        }
    }

    public function imprimir_planilla(MiembroPlanilla $planilla)
    {
        $planilla->load([
            'detalles' => function ($query) {
                $query->where('estado_id', 1)
                ->orderBy('nombre_completo')
                ->orderBy('id');
            },
        ]);

        if ($planilla->detalles->isEmpty()) {
            return redirect()->route('miembros.planillas.index')->with('error', 'La planilla no posee detalles para imprimir.');
        }

        /*
        |--------------------------------------------------------------------------
        | ENTIDAD
        |--------------------------------------------------------------------------
        */
        $entidad = Entidad::find(1);

        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        $estadosPlanilla = [
            1 => 'GENERADA',
            2 => 'PAGADA',
            3 => 'ANULADA',
        ];

        $numeroFormateado = str_pad($planilla->numero,5,'0', STR_PAD_LEFT);
        $nombreArchivo = 'planilla-miembros-' . $numeroFormateado. '-'. $planilla->anio. '.pdf';
        $tipos = self::tipos;
        $pdf = Pdf::loadView('miembro.imprimir',compact('planilla','entidad','meses','estadosPlanilla','tipos'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream($nombreArchivo);
    }

    public function anular_planilla(Request $request,MiembroPlanilla $planilla)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required','string','min:5','max:500',],
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de la anulación.',
            'motivo_anulacion.min' =>'El motivo debe tener al menos 5 caracteres.',
            'motivo_anulacion.max' => 'El motivo no puede superar los 500 caracteres.',
        ]);

        try {
            DB::transaction(function () use ($planilla,$datos) {
                /*
                |--------------------------------------------------------------------------
                | BLOQUEAR PLANILLA
                |--------------------------------------------------------------------------
                */
                $planillaBloqueada = MiembroPlanilla::query()
                ->whereKey($planilla->id)
                ->lockForUpdate()
                ->firstOrFail();

                if ((int) $planillaBloqueada->estado_id !== 1) {
                    throw new \Exception('La planilla seleccionada no se encuentra activa.');
                }

                /*
                |--------------------------------------------------------------------------
                | Estado 1: generada
                | Estado 2: pagada
                | Estado 3: anulada
                |--------------------------------------------------------------------------
                */
                if ((int) $planillaBloqueada->estado_planilla === 2) {
                    throw new \Exception('La planilla ya fue pagada y no puede ser anulada.');
                }

                if ((int) $planillaBloqueada->estado_planilla === 3) {
                    throw new \Exception('La planilla ya se encuentra anulada.');
                }

                if ((int) $planillaBloqueada->estado_planilla !== 1) {
                    throw new \Exception('El estado actual de la planilla no permite anularla.');
                }

                /*
                |--------------------------------------------------------------------------
                | VALIDAR ORDEN DE PAGO
                |--------------------------------------------------------------------------
                |
                | Si ya tiene una orden de pago, primero se debe anular esa orden.
                | Así evitamos que quede una orden pendiente correspondiente a una
                | planilla anulada.
                |
                */
                if ($planillaBloqueada->orden_pago_id) {
                    throw new \Exception('La planilla posee una orden de pago vinculada. Primero debe anular la orden de pago.');
                }

                /*
                |--------------------------------------------------------------------------
                | ANULAR PLANILLA
                |--------------------------------------------------------------------------
                */
                $planillaBloqueada->update([
                    'estado_planilla' => 3,
                    'fecha_anulacion' => now()->toDateString(),
                    'motivo_anulacion' => trim($datos['motivo_anulacion']),
                    'usuario_modificacion' => auth()->id(),
                ]);
            });

            return redirect()->route('miembros.planillas.index')->with('message','La planilla fue anulada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->route('miembros.planillas.index')->with('error', $e->getMessage());
        }
    }

    public function regenerar_planilla(MiembroPlanilla $planilla)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDAR PLANILLA ORIGINAL
        |--------------------------------------------------------------------------
        */
        if ((int) $planilla->estado_id !== 1) {
            return redirect()->route('miembros.planillas.index')->with('error','La planilla seleccionada no se encuentra activa.');
        }

        /*
        |--------------------------------------------------------------------------
        | Estado 1: generada
        | Estado 2: pagada
        | Estado 3: anulada
        |--------------------------------------------------------------------------
        */
        if ((int) $planilla->estado_planilla !== 3) {
            return redirect()->route('miembros.planillas.index')->with('error','Solamente se pueden regenerar planillas anuladas.');
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICAR QUE NO EXISTA OTRA PLANILLA VÁLIDA DEL MISMO PERIODO
        |--------------------------------------------------------------------------
        */
        $existeOtraPlanilla = MiembroPlanilla::query()
        ->where('anio', $planilla->anio)
        ->where('mes', $planilla->mes)
        ->where('estado_id', 1)
        ->where('estado_planilla', '<>', 3)
        ->where('id', '<>', $planilla->id)
        ->exists();

        if ($existeOtraPlanilla) {
            return redirect()->route('miembros.planillas.index')->with('error','Ya existe otra planilla vigente para este periodo.');
        }

        /*
        |--------------------------------------------------------------------------
        | MIEMBROS ACTUALES
        |--------------------------------------------------------------------------
        |
        | No se consulta el detalle de la planilla anulada.
        |
        */
        $miembros = Miembro::query()
        ->where('estado_id', 1)
        ->where('pago', 1)
        ->orderBy('tipo')
        ->orderBy('nombre')
        ->orderBy('apellido')
        ->get();

        if ($miembros->isEmpty()) {
            return redirect()->route('miembros.planillas.index')->with('error','No existen miembros habilitados para generar la planilla.');
        }

        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        $tipos = self::tipos;
        $totalRemuneracion = (int) $miembros->sum('monto_remuneracion');
        $totalAdelanto = (int) $miembros->sum('adelanto');
        $totalNeto = (int) $miembros->sum('neto');

        return view('miembro.regenerar',compact('planilla','miembros','meses','tipos','totalRemuneracion','totalAdelanto','totalNeto'));
    }

    public function guardar_regeneracion(MiembroPlanilla $planilla)
    {
        try {
            $nuevaPlanilla = DB::transaction(
                function () use ($planilla) {
                    /*
                    |--------------------------------------------------------------------------
                    | BLOQUEAR PLANILLA ANULADA
                    |--------------------------------------------------------------------------
                    */
                    $planillaOriginal = MiembroPlanilla::query()
                    ->whereKey($planilla->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                    if ((int) $planillaOriginal->estado_id !== 1) {
                        throw new \Exception('La planilla seleccionada no se encuentra activa.');
                    }

                    if ((int) $planillaOriginal->estado_planilla !== 3) {
                        throw new \Exception('La planilla seleccionada ya no se encuentra anulada.');
                    }

                    $anio = (int) $planillaOriginal->anio;
                    $mes = (int) $planillaOriginal->mes;

                    /*
                    |--------------------------------------------------------------------------
                    | EVITAR OTRA PLANILLA VIGENTE PARA EL MISMO PERIODO
                    |--------------------------------------------------------------------------
                    */
                    $planillasPeriodo = MiembroPlanilla::query()
                    ->where('anio', $anio)
                    ->where('mes', $mes)
                    ->where('estado_id', 1)
                    ->where('id', '<>', $planillaOriginal->id)
                    ->lockForUpdate()
                    ->get();

                    $existeOtraPlanilla = $planillasPeriodo->contains(
                        function ($item) {
                            return (int) $item->estado_planilla !== 3;
                        }
                    );

                    if ($existeOtraPlanilla) {
                        throw new \Exception('Ya existe otra planilla vigente para este periodo.');
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | OBTENER MIEMBROS ACTUALES
                    |--------------------------------------------------------------------------
                    |
                    | No se utilizan los detalles de la planilla anterior.
                    |
                    */
                    $miembros = Miembro::query()
                    ->where('estado_id', 1)
                    ->where('pago', 1)
                    ->orderBy('tipo')
                    ->orderBy('nombre')
                    ->orderBy('apellido')
                    ->lockForUpdate()
                    ->get();

                    if ($miembros->isEmpty()) {
                        throw new \Exception('No existen miembros habilitados para generar la planilla.');
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | GENERAR NUEVO NÚMERO
                    |--------------------------------------------------------------------------
                    */
                    $numeracion = Numeraciones::query()
                    ->where('tipo',self::TIPO_NUMERACION_PLANILLA_MIEMBROS)
                    ->where('anio', $anio)
                    ->lockForUpdate()
                    ->first();

                    if (!$numeracion) {
                        $numero = 1;
                        Numeraciones::create([
                            'tipo' => self::TIPO_NUMERACION_PLANILLA_MIEMBROS,
                            'anio' => $anio,
                            'descripcion' =>'Planilla de remuneración de miembros',
                            'numero' => 2,
                        ]);
                    } else {
                        $numero = (int) $numeracion->numero;
                        $numeracion->update(['numero' => $numero + 1]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CALCULAR TOTALES ACTUALES
                    |--------------------------------------------------------------------------
                    */
                    $totalRemuneracion = (int) $miembros->sum('monto_remuneracion');
                    $totalAdelanto = (int) $miembros->sum('adelanto');
                    $totalNeto = (int) $miembros->sum('neto');
                    /*
                    |--------------------------------------------------------------------------
                    | CREAR NUEVA CABECERA
                    |--------------------------------------------------------------------------
                    */
                    $nuevaPlanilla = MiembroPlanilla::create([
                        'numero' => $numero,
                        'anio' => $anio,
                        'mes' => $mes,
                        'fecha_generacion' => now()->toDateString(),
                        'cantidad' => $miembros->count(),
                        'total_remuneracion' => $totalRemuneracion,
                        'total_adelanto' => $totalAdelanto,
                        'total_neto' => $totalNeto,
                        'estado_planilla' => 1,
                        'orden_pago_id' => null,
                        'fecha_pago' => null,
                        'fecha_anulacion' => null,
                        'motivo_anulacion' => null,
                        'estado_id' => 1,
                        'user_id' => auth()->id(),
                        'usuario_modificacion' => null,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | CREAR NUEVOS DETALLES
                    |--------------------------------------------------------------------------
                    */
                    foreach ($miembros as $miembro) {
                        MiembroPlanillaDetalle::create([
                            'miembro_planilla_id' => $nuevaPlanilla->id,
                            'miembro_id' => $miembro->id,
                            'nombre_completo' => trim($miembro->nombre. ' '. $miembro->apellido),
                            'tipo' => $miembro->tipo,
                            'monto_remuneracion' => (int) $miembro->monto_remuneracion,
                            'adelanto' => (int) $miembro->adelanto,
                            'neto' => (int) $miembro->neto,
                            'estado_pago' => 1,
                            'fecha_pago' => null,
                            'estado_id' => 1,
                            'user_id' => auth()->id(),
                            'usuario_modificacion' => null,
                        ]);
                    }

                    return $nuevaPlanilla;
                }
            );

            return redirect()->route('miembros.planillas.index')
                ->with(
                    'message',
                    'La planilla N.º '
                    . str_pad(
                        $nuevaPlanilla->numero,
                        5,
                        '0',
                        STR_PAD_LEFT
                    )
                    . '/'
                    . $nuevaPlanilla->anio
                    . ' fue regenerada correctamente.'
                );
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

}
