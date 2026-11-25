<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\OtroDescuento;
use App\Models\OtroDescuentoAsociado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtroDescuentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:planilla.descuentos.index')->only('index');
        $this->middleware('permission:planilla.descuentos.show')->only('show');
        $this->middleware('permission:planilla.descuentos.create')->only(['create', 'store']);
        $this->middleware('permission:planilla.descuentos.edit')->only(['edit', 'update']);
        $this->middleware('permission:planilla.descuentos.destroy')->only('destroy');
        $this->middleware('permission:planilla.descuentos.socios')->only(['createAsociados', 'storeAsociados', 'destroyAsociado']);
    }

    public function index()
    {
        $descuentos = OtroDescuento::withCount('asociados')
            ->withSum('asociados as monto_asignado', 'monto_total')
            ->orderByDesc('id')
            ->paginate(15);

        return view('planilla.otros_descuentos.index', compact('descuentos'));
    }

    public function create()
    {
        return view('planilla.otros_descuentos.create');
    }

    public function edit(OtroDescuento $otroDescuento)
    {
        if ($this->tienePlanillaActiva($otroDescuento->id) || $this->tieneCuotasPagadas($otroDescuento->id)) {
            return redirect()->route('planilla.otros-descuentos.show', $otroDescuento)
                ->withErrors(['descuento' => 'No se puede editar porque este descuento ya tiene cuotas en una planilla activa o pagadas.']);
        }

        return view('planilla.otros_descuentos.edit', compact('otroDescuento'));
    }

    public function update(Request $request, OtroDescuento $otroDescuento)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'monto_por_socio' => ['required', 'integer', 'min:1'],
            'cantidad_cuotas' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $usuarioId = (int) auth()->id();

        try {
            DB::transaction(function () use ($datos, $otroDescuento, $usuarioId) {
                $descuento = OtroDescuento::query()->whereKey($otroDescuento->id)->lockForUpdate()->firstOrFail();

                if ($this->tienePlanillaActiva($descuento->id) || $this->tieneCuotasPagadas($descuento->id)) {
                    throw new \RuntimeException('No se puede editar porque este descuento ya tiene cuotas en una planilla activa o pagadas.');
                }

                $asignaciones = $descuento->asociados()->with('cuotas')->lockForUpdate()->get();

                foreach ($asignaciones as $asignacion) {
                    if ((int) $asignacion->cantidad_cuotas > (int) $datos['cantidad_cuotas']) {
                        throw new \RuntimeException('No se puede reducir el máximo de cuotas por debajo de las cuotas ya configuradas para un socio.');
                    }

                    $periodoOcupado = $this->periodoConPlanillaGenerada(
                        [$asignacion->asociado_id],
                        (int) $asignacion->mes_inicio,
                        (int) $asignacion->anio_inicio,
                        (int) $asignacion->cantidad_cuotas
                    );
                    if ($periodoOcupado) {
                        throw new \RuntimeException('Ya está generada la planilla de un socio para ' . $this->formatearPeriodo($periodoOcupado) . '. No se puede modificar el descuento mientras esa planilla esté activa.');
                    }
                }

                $descuento->update([
                    'nombre' => $datos['nombre'],
                    'descripcion' => $datos['descripcion'] ?? null,
                    'monto_por_socio' => (int) $datos['monto_por_socio'],
                    'cantidad_cuotas' => (int) $datos['cantidad_cuotas'],
                    'usuario_modificacion' => $usuarioId,
                ]);

                foreach ($asignaciones as $asignacion) {
                    if ($asignacion->cuotas->contains(fn ($cuota) => $cuota->planilla_detalle_id || (int) $cuota->monto_pagado > 0)) {
                        throw new \RuntimeException('No se pueden regenerar las cuotas porque una ya fue aplicada o pagada.');
                    }

                    $asignacion->cuotas()->delete();
                    $asignacion->update(['monto_total' => (int) $datos['monto_por_socio']]);
                    $this->programarCuotas($asignacion, $usuarioId);
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['descuento' => $e->getMessage()])->withInput();
        }

        return redirect()->route('planilla.otros-descuentos.show', $otroDescuento)
            ->with('message', 'El concepto y las cuotas de todos los socios fueron actualizados.');
    }

    public function destroy(OtroDescuento $otroDescuento)
    {
        try {
            DB::transaction(function () use ($otroDescuento) {
                $descuento = OtroDescuento::query()->whereKey($otroDescuento->id)->lockForUpdate()->firstOrFail();

                if ($this->tienePlanillaActiva($descuento->id) || $this->tieneCuotasPagadas($descuento->id)) {
                    throw new \RuntimeException('No se puede eliminar porque el descuento tiene cuotas vinculadas a planillas activas o pagadas.');
                }

                $asignaciones = $descuento->asociados()->with('cuotas')->lockForUpdate()->get();
                foreach ($asignaciones as $asignacion) {
                    if ($asignacion->cuotas->contains(fn ($cuota) => $cuota->planilla_detalle_id)) {
                        throw new \RuntimeException('No se puede eliminar porque una cuota conserva una referencia a una planilla.');
                    }
                    $asignacion->cuotas()->delete();
                    $asignacion->delete();
                }

                $descuento->delete();
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['descuento' => $e->getMessage()]);
        }

        return redirect()->route('planilla.descuentos.index')
            ->with('message', 'El descuento y sus asignaciones fueron eliminados.');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'monto_por_socio' => ['required', 'integer', 'min:1'],
            'cantidad_cuotas' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $usuarioId = (int) auth()->id();
        $descuento = OtroDescuento::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'monto_por_socio' => (int) $datos['monto_por_socio'],
                'cantidad_cuotas' => (int) $datos['cantidad_cuotas'],
                'estado_id' => 1,
                'user_id' => $usuarioId,
                'usuario_modificacion' => $usuarioId,
            ]);

        return redirect()->route('planilla.otros-descuentos.show', $descuento)
            ->with('message', 'El concepto fue creado. Ahora puede agregar los socios afectados.');
    }

    public function createAsociados(OtroDescuento $otroDescuento)
    {
        $asignados = $otroDescuento->asociados()->pluck('asociado_id');
        $asociados = Asociado::query()
            ->join('personas', 'personas.id', '=', 'asociados.persona_id')
            ->where('asociados.estado_id', 1)
            ->whereNotIn('asociados.id', $asignados)
            ->orderBy('personas.apellido')
            ->orderBy('personas.nombre')
            ->get(['asociados.id', 'asociados.numero_socio', 'personas.documento', 'personas.nombre', 'personas.apellido']);

        return view('planilla.otros_descuentos.socios', compact('otroDescuento', 'asociados'));
    }

    public function storeAsociados(Request $request, OtroDescuento $otroDescuento)
    {
        $reglas = [
            'asociados' => ['required', 'array', 'min:1'],
            'asociados.*' => ['required', 'integer', 'distinct', 'exists:asociados,id'],
            'configuracion' => ['required', 'array'],
            'configuracion.*.cantidad_cuotas' => ['required', 'integer', 'min:1', 'max:' . (int) $otroDescuento->cantidad_cuotas],
            'configuracion.*.mes_inicio' => ['required', 'integer', 'between:1,12'],
            'configuracion.*.anio_inicio' => ['required', 'integer', 'between:2020,2100'],
        ];

        $mensajes = [
            'asociados.required' => 'Seleccione al menos un socio para este descuento.',
            'asociados.min' => 'Seleccione al menos un socio para este descuento.',
            'configuracion.*.cantidad_cuotas.max' => 'La cantidad de cuotas de cada socio no puede superar el máximo del concepto.',
        ];

        $datos = $request->validate($reglas, $mensajes);
        foreach ($datos['asociados'] as $asociadoId) {
            if (!isset($datos['configuracion'][$asociadoId])) {
                return back()->withErrors(['configuracion' => 'Configure el período y las cuotas de cada socio seleccionado.'])->withInput();
            }
        }

        $usuarioId = (int) auth()->id();
        $montoTotal = (int) $otroDescuento->monto_por_socio;

        try {
            DB::transaction(function () use ($datos, $usuarioId, $otroDescuento, $montoTotal) {
                $sociosValidos = Asociado::query()
                    ->whereIn('id', $datos['asociados'])
                    ->where('estado_id', 1)
                    ->lockForUpdate()
                    ->get();

                if ($sociosValidos->count() !== count($datos['asociados'])) {
                    throw new \RuntimeException('Uno o más socios seleccionados ya no están activos.');
                }

                foreach ($sociosValidos as $socio) {
                    if ($otroDescuento->asociados()->where('asociado_id', $socio->id)->exists()) {
                        throw new \RuntimeException('Uno o más socios ya fueron agregados a este descuento.');
                    }

                    $configuracion = $datos['configuracion'][$socio->id];
                    $cantidadCuotas = (int) $configuracion['cantidad_cuotas'];
                    $mesInicio = (int) $configuracion['mes_inicio'];
                    $anioInicio = (int) $configuracion['anio_inicio'];

                    $periodoOcupado = $this->periodoConPlanillaGenerada([$socio->id], $mesInicio, $anioInicio, $cantidadCuotas);
                    if ($periodoOcupado) {
                        throw new \RuntimeException('Ya está generada la planilla del socio N.º ' . $socio->numero_socio . ' para ' . $this->formatearPeriodo($periodoOcupado) . '. Elija otro período o anule esa planilla antes de programar el descuento.');
                    }

                    $asignacion = OtroDescuentoAsociado::create([
                        'otro_descuento_id' => $otroDescuento->id,
                        'asociado_id' => $socio->id,
                        'monto_total' => $montoTotal,
                        'cantidad_cuotas' => $cantidadCuotas,
                        'mes_inicio' => $mesInicio,
                        'anio_inicio' => $anioInicio,
                    ]);

                    $this->programarCuotas($asignacion, $usuarioId);
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['descuento' => $e->getMessage()])->withInput();
        }

        return redirect()->route('planilla.otros-descuentos.show', $otroDescuento)
            ->with('message', 'Los socios y sus calendarios de descuento fueron agregados correctamente.');
    }

    public function show(OtroDescuento $otroDescuento)
    {
        $otroDescuento->load([
            'asociados' => function ($query) {
                $query->with('cuotas')->with('asociado.persona');
            },
        ]);

        return view('planilla.otros_descuentos.show', compact('otroDescuento'));
    }

    public function destroyAsociado(OtroDescuento $otroDescuento, OtroDescuentoAsociado $asignacion)
    {
        if ((int) $asignacion->otro_descuento_id !== (int) $otroDescuento->id) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($otroDescuento, $asignacion) {
                $asignacionActual = OtroDescuentoAsociado::query()
                    ->whereKey($asignacion->id)
                    ->where('otro_descuento_id', $otroDescuento->id)
                    ->with('cuotas')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($this->tienePlanillaActiva($otroDescuento->id, $asignacionActual->id)
                    || $asignacionActual->cuotas->contains(fn ($cuota) => $cuota->planilla_detalle_id || (int) $cuota->monto_pagado > 0)) {
                    throw new \RuntimeException('No se puede quitar este socio porque alguna de sus cuotas coincide con una planilla activa o ya fue aplicada.');
                }

                $asignacionActual->cuotas()->delete();
                $asignacionActual->delete();
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['descuento' => $e->getMessage()]);
        }

        return redirect()->route('planilla.otros-descuentos.show', $otroDescuento)
            ->with('message', 'El socio y las cuotas pendientes fueron quitados del descuento.');
    }

    private function programarCuotas(OtroDescuentoAsociado $asignacion, int $usuarioId): void
    {
        $montoTotal = (int) $asignacion->monto_total;
        $cantidadCuotas = (int) $asignacion->cantidad_cuotas;
        $montoBase = intdiv($montoTotal, $cantidadCuotas);
        $resto = $montoTotal % $cantidadCuotas;
        $periodoInicial = Carbon::create((int) $asignacion->anio_inicio, (int) $asignacion->mes_inicio, 1);

        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            $montoCuota = $montoBase + ($i <= $resto ? 1 : 0);
            $fecha = $periodoInicial->copy()->addMonths($i - 1);
            $asignacion->cuotas()->create([
                'numero_cuota' => $i,
                'mes' => $fecha->month,
                'anio' => $fecha->year,
                'monto_esperado' => $montoCuota,
                'monto_pagado' => 0,
                'saldo' => $montoCuota,
                'estado_pago_id' => 1,
                'estado_id' => 1,
                'user_id' => $usuarioId,
                'usuario_modificacion' => $usuarioId,
            ]);
        }
    }

    private function periodoConPlanillaGenerada($asociadoIds, int $mesInicio, int $anioInicio, int $cantidadCuotas): ?array
    {
        $periodoInicial = Carbon::create($anioInicio, $mesInicio, 1);
        for ($i = 0; $i < $cantidadCuotas; $i++) {
            $fecha = $periodoInicial->copy()->addMonths($i);
            $planillaExiste = DB::table('planilla_detalles as pd')
                ->join('planillas as pl', 'pl.id', '=', 'pd.planilla_id')
                ->whereIn('pd.asociado_id', $asociadoIds)
                ->where('pl.mes', $fecha->month)
                ->where('pl.anio', $fecha->year)
                ->where('pl.estado_id', 1)
                ->exists();

            if ($planillaExiste) {
                return ['mes' => $fecha->month, 'anio' => $fecha->year];
            }
        }

        return null;
    }

    private function formatearPeriodo(array $periodo): string
    {
        return str_pad((string) $periodo['mes'], 2, '0', STR_PAD_LEFT) . '/' . $periodo['anio'];
    }

    private function tienePlanillaActiva(int $descuentoId, ?int $asignacionId = null): bool
    {
        return DB::table('otro_descuento_cuotas as odc')
            ->join('otro_descuento_asociados as oda', 'oda.id', '=', 'odc.otro_descuento_asociado_id')
            ->join('planilla_detalles as pd', 'pd.asociado_id', '=', 'oda.asociado_id')
            ->join('planillas as pl', function ($join) {
                $join->on('pl.id', '=', 'pd.planilla_id')
                    ->on('pl.mes', '=', 'odc.mes')
                    ->on('pl.anio', '=', 'odc.anio');
            })
            ->where('oda.otro_descuento_id', $descuentoId)
            ->when($asignacionId, fn ($query) => $query->where('oda.id', $asignacionId))
            ->where('odc.estado_id', 1)
            ->where('pd.estado_id', 1)
            ->where('pl.estado_id', 1)
            ->exists();
    }

    private function tieneCuotasPagadas(int $descuentoId): bool
    {
        return DB::table('otro_descuento_cuotas as odc')
            ->join('otro_descuento_asociados as oda', 'oda.id', '=', 'odc.otro_descuento_asociado_id')
            ->where('oda.otro_descuento_id', $descuentoId)
            ->where(function ($query) {
                $query->where('odc.monto_pagado', '>', 0)
                    ->orWhereIn('odc.estado_pago_id', [2, 3]);
            })
            ->exists();
    }
}
