<?php

namespace App\Services;

use App\Models\Planilla;
use App\Models\PlanillaDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlanillaAporteService
{
    public function generarDetalle(int $mes, int $anio, int $tipoAsociadoId)
    {
        $tipos = $tipoAsociadoId == 3 ? [3] : [1, 2];
        $periodo = Carbon::create($anio, $mes, 1);
        $periodoActual = sprintf('%04d%02d', $anio, $mes);
        $this->validarSecuenciaPlanilla($mes, $anio, $tipoAsociadoId);
        
        $subAportes = DB::table('aportes as ap')
        ->join('asociados as a', 'a.id', '=', 'ap.asociado_id')
        ->select(
            'ap.asociado_id',
            DB::raw('SUM(ap.aporte) as total_aportado')
        )
        ->where('ap.anio', $anio)
        ->where('ap.mes', $mes)
        ->where('a.estado_id', 1)
        ->whereIn('a.tipo_asociado_id', $tipos)
        ->groupBy('ap.asociado_id');

        $subPlanillasHistoricas = DB::table('planilla_detalles as pd')
        ->join('planillas as pl', 'pl.id', '=', 'pd.planilla_id')
        ->select(
            'pd.asociado_id',
            DB::raw("COUNT(DISTINCT CONCAT(pl.anio, LPAD(pl.mes, 2, '0'))) as meses_generados")
        )
        ->where('pl.estado_id', 1)
        ->where('pl.tipo_asociado_id', $tipoAsociadoId)
        ->whereRaw("CONCAT(pl.anio, LPAD(pl.mes, 2, '0')) < ?", [$periodoActual])
        ->groupBy('pd.asociado_id');

        $rows = DB::table('asociados as a')
        ->join('personas as p', 'p.id', '=', 'a.persona_id')
        ->leftJoinSub($subAportes, 'ap', function ($join) {
            $join->on('ap.asociado_id', '=', 'a.id');
        })
        ->leftJoinSub($subPlanillasHistoricas, 'ph', function ($join) {
            $join->on('ph.asociado_id', '=', 'a.id');
        })
        ->select(
            'a.id',
            'a.tipo_asociado_id',
            'a.numero_socio',
            'a.fecha_admision',
            'a.fecha_baja',
            'p.nombre',
            'p.apellido',
            DB::raw('COALESCE(ap.total_aportado, 0) as pagado'),
            DB::raw('COALESCE(ph.meses_generados, 0) as meses_generados')
        )
        ->whereIn('a.tipo_asociado_id', $tipos)
        ->where('a.estado_id', 1)
        ->whereDate('a.fecha_admision', '<=', $periodo->copy()->endOfMonth()->format('Y-m-d'))
        ->where(function ($q) use ($periodo) {
            $q->whereNull('a.fecha_baja')
                ->orWhereDate('a.fecha_baja', '>=', $periodo->copy()->startOfMonth()->format('Y-m-d'));
        })
        ->orderBy('a.numero_socio')
        ->get();

        return collect($rows)->map(function ($item) use ($periodo) {
            $fechaAdmision = Carbon::parse($item->fecha_admision)->startOfMonth();

            if ($fechaAdmision->gt($periodo)) {
                return null;
            }

            $numeroPeriodo = ((int) $item->meses_generados) + 1;
            $montoEsperado = $numeroPeriodo <= 5 ? 30000 : 20000;
            $pagado = (int) $item->pagado;
            $saldo = $montoEsperado - $pagado;

            if ($saldo <= 0) {
                return null;
            }

            return [
                'asociado_id'      => $item->id,
                'tipo_asociado_id' => $item->tipo_asociado_id,
                'numero_socio'     => $item->numero_socio,
                'nombre'           => trim(($item->nombre ?? '') . ' ' . ($item->apellido ?? '')),
                'numero_periodo'   => $numeroPeriodo,
                'monto_esperado'   => $montoEsperado,
                'pagado'           => $pagado,
                'saldo'            => $saldo,
                'estado'           => $pagado > 0 ? 'PARCIAL' : 'PENDIENTE',
            ];
        })->filter()->values();
    }

    public function guardarPlanilla(int $mes, int $anio, int $tipoAsociadoId)
    {
        return DB::transaction(function () use ($mes, $anio, $tipoAsociadoId) {
            $this->validarSecuenciaPlanilla($mes, $anio, $tipoAsociadoId);

            $existe = Planilla::where('tipo_asociado_id', $tipoAsociadoId)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->where('estado_id', 1)
            ->exists();

            if ($existe) {
                throw new \Exception('Ya existe una planilla generada para ese tipo, mes y año.');
            }

            $detalles = $this->generarDetalle($mes, $anio, $tipoAsociadoId);

            if ($detalles->isEmpty()) {
                throw new \Exception('No existen asociados pendientes para generar la planilla.');
            }

            $planilla = Planilla::create([
                'tipo_asociado_id'     => $tipoAsociadoId,
                'mes'                  => $mes,
                'anio'                 => $anio,
                'fecha'                => now()->toDateString(),
                'cantidad'             => $detalles->count(),
                'total'                => $detalles->sum('saldo'),
                'pagado' => 0,
                'monto_pagado' => 0,
                'fecha_pagado' => null,
                'estado_id'            => 1,
                'user_id'              => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            $ahora = now();
            $userId = auth()->id();
            $insert = [];

            foreach ($detalles as $item) {
                $insert[] = [
                    'planilla_id'          => $planilla->id,
                    'asociado_id'          => $item['asociado_id'],
                    'tipo_asociado_id'     => $item['tipo_asociado_id'],
                    'monto_esperado'       => $item['monto_esperado'],
                    'pagado'               => $item['pagado'],
                    'saldo'                => $item['saldo'],
                    'estado_id'            => 1,
                    'user_id'              => $userId,
                    'usuario_modificacion' => $userId,
                    'created_at'           => $ahora,
                    'updated_at'           => $ahora,
                ];
            }

            PlanillaDetalle::insert($insert);

            return $planilla;
        });
    }

    private function validarSecuenciaPlanilla(int $mes, int $anio, int $tipoAsociadoId): void
    {
        $tipoCabecera = $tipoAsociadoId == 3 ? 3 : 1;

        $ultimaPlanilla = Planilla::where('tipo_asociado_id', $tipoCabecera)
        ->where('estado_id', 1)
        ->orderBy('anio', 'desc')
        ->orderBy('mes', 'desc')
        ->first();

        if (!$ultimaPlanilla) {
            return;
        }

        $fechaUltima = Carbon::create($ultimaPlanilla->anio, $ultimaPlanilla->mes, 1);
        $fechaEsperada = $fechaUltima->copy()->addMonth();
        $fechaNueva = Carbon::create($anio, $mes, 1);

        if (!$fechaNueva->equalTo($fechaEsperada)) {
            throw new \Exception(
                'No puede generar la planilla de ' .
                ucfirst($fechaNueva->locale('es')->translatedFormat('F Y')) .
                '. Debe generar primero la planilla de ' .
                ucfirst($fechaEsperada->locale('es')->translatedFormat('F Y')) . '.'
            );
        }
    }
}