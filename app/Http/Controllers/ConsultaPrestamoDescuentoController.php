<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConsultaPrestamoDescuentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:prestamos.descuentos')->only('index');
    }

    public function index(Request $request)
    {
        $consultaEjecutada = $request->boolean('consultar');
        $mes = (int) $request->input('mes', now()->month);
        $anio = (int) $request->input('anio', now()->year);
        $tipoAsociado = $request->input('tipo_asociado');
        $institucionId = $request->input('institucion_id');
        $cuotas = null;

        $instituciones = Institucion::query()
            ->where('estado_id', 1)
            ->where('departamento_id', '<>', 100)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion']);

        if ($consultaEjecutada) {
            $request->validate([
                'mes' => ['required', 'integer', 'between:1,12'],
                'anio' => ['required', 'integer', 'between:2000,2100'],
                'tipo_asociado' => ['required', Rule::in(['jubilado', 'activo'])],
                'institucion_id' => [
                    'nullable',
                    'required_if:tipo_asociado,activo',
                    'integer',
                    Rule::exists('institucions', 'id')->where(function ($query) {
                        $query->where('estado_id', 1)->where('departamento_id', '<>', 100);
                    }),
                ],
            ], [
                'tipo_asociado.required' => 'Seleccione el tipo de asociado.',
                'institucion_id.required_if' => 'Seleccione el municipio o institución municipal.',
                'institucion_id.exists' => 'La institución seleccionada no está disponible.',
            ]);

            $tiposJubilados = DB::table('tipo_asociados')
                ->whereIn(DB::raw('UPPER(descripcion)'), ['JUBILADO', 'PENSIONADO'])
                ->pluck('id');
            $tipoAportante = DB::table('tipo_asociados')
                ->whereRaw('UPPER(descripcion) = ?', ['APORTANTE'])
                ->value('id');

            $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
            $fin = $inicio->copy()->endOfMonth();

            $consulta = DB::table('prestamo_detalles as pd')
                ->join('prestamos as pr', 'pr.id', '=', 'pd.prestamo_id')
                ->join('tipo_prestamos as tp', 'tp.id', '=', 'pr.tipo_prestamo_id')
                ->join('personas as p', 'p.id', '=', 'pr.persona_id')
                ->join('asociados as a', 'a.persona_id', '=', 'p.id')
                ->leftJoin('institucions as i', 'i.id', '=', 'a.institucion_id')
                ->where('pr.estado_id', 1)
                ->where('pd.estado_id', 1)
                ->where('a.estado_id', 1)
                ->whereRaw('UPPER(tp.descripcion) LIKE ?', ['%EMERGENCIA%'])
                ->whereBetween('pd.fecha_vencimiento', [$inicio->toDateString(), $fin->toDateString()])
                ->when($tipoAsociado === 'jubilado', function ($query) use ($tiposJubilados) {
                    $query->whereIn('a.tipo_asociado_id', $tiposJubilados);
                })
                ->when($tipoAsociado === 'activo', function ($query) use ($tipoAportante, $institucionId) {
                    $query->where('a.tipo_asociado_id', $tipoAportante)
                        ->where('a.institucion_id', $institucionId);
                })
                ->select([
                    'a.id as asociado_id',
                    'a.numero_socio',
                    'p.documento',
                    'p.nombre',
                    'p.apellido',
                    'i.descripcion as institucion',
                    'pr.id as prestamo_id',
                    'pr.anio as anio_prestamo',
                    'pr.numero_prestamo',
                    'pr.cantidad_cuotas as total_cuotas',
                    'tp.descripcion as tipo_prestamo',
                    'pd.id as cuota_id',
                    'pd.numero_cuota',
                    'pd.fecha_vencimiento',
                    'pd.monto_total',
                    'pd.monto_pagado',
                    'pd.saldo_total',
                    'pd.estado_pago_id',
                    'pd.fecha_pago',
                ])
                ->orderBy('p.apellido')
                ->orderBy('p.nombre')
                ->orderBy('a.numero_socio')
                ->orderBy('pd.numero_cuota');

            $cuotas = $consulta->paginate(50)->withQueryString();
            $cuotas->getCollection()->transform(function ($cuota) {
                $estadoPago = (int) $cuota->estado_pago_id;
                $saldo = (int) $cuota->saldo_total;
                $pagado = (int) $cuota->monto_pagado;

                if ($saldo <= 0 || $estadoPago === 2) {
                    $cuota->estado_consulta = 'Cobrado';
                    $cuota->clase_estado = 'success';
                } elseif ($estadoPago === 4) {
                    $cuota->estado_consulta = 'Anulado';
                    $cuota->clase_estado = 'secondary';
                } elseif ($estadoPago === 5) {
                    $cuota->estado_consulta = 'En planilla, pendiente de cobro';
                    $cuota->clase_estado = 'info';
                } elseif ($estadoPago === 3 || $pagado > 0) {
                    $cuota->estado_consulta = 'Cobro parcial';
                    $cuota->clase_estado = 'warning';
                } elseif ($estadoPago === 6) {
                    $cuota->estado_consulta = 'No cobrado';
                    $cuota->clase_estado = 'danger';
                } else {
                    $cuota->estado_consulta = 'Pendiente de cobro';
                    $cuota->clase_estado = 'light';
                }

                return $cuota;
            });
        }

        return view('consultas.prestamos_descuentos', compact(
            'consultaEjecutada',
            'mes',
            'anio',
            'tipoAsociado',
            'institucionId',
            'instituciones',
            'cuotas'
        ));
    }
}
