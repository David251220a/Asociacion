@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
    <script src="{{asset('plugins/sweetalerts/promise-polyfill.js')}}"></script>
    <link href="{{asset('plugins/sweetalerts/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('plugins/sweetalerts/sweetalert.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/components/custom-sweetalert.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<style>
    .socio-card {
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        padding: 18px 22px;
        margin-bottom: 20px;
        background: #fff;
    }

    .socio-title {
        font-size: 18px;
        font-weight: 700;
        color: #1b2e4b;
        margin-bottom: 15px;
        text-transform: uppercase;
    }

    .socio-label {
        font-size: 12px;
        font-weight: 700;
        color: #4361ee;
        text-transform: uppercase;
    }

    .socio-value {
        font-size: 14px;
        font-weight: 600;
        color: #1b2e4b;
    }

    .resumen-box {
        border-radius: 6px;
        padding: 15px;
        color: #fff;
        text-align: center;
        font-weight: 600;
    }

    .resumen-box .valor {
        font-size: 18px;
        margin-top: 5px;
    }

    .table-aportes th {
        background: #f1f2f3;
        color: #1b2e4b;
        font-size: 12px;
        text-transform: uppercase;
    }

    .table-aportes td {
        color: #1b2e4b;
        font-size: 13px;
        vertical-align: middle;
    }
</style>

<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">

            <h3 class="mb-3">Aportes y Antigüedad del Socio</h3>

            <div class="alert alert-info mb-4">
                Consulta detallada de aportes registrados y antigüedad acumulada del socio.
            </div>

            <div class="socio-card">
                <div class="socio-title">
                    {{ $asociado->persona->nombre }} {{ $asociado->persona->apellido }}
                </div>

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <div class="socio-label">Cédula</div>
                        <div class="socio-value">{{ $asociado->persona->documento }}</div>
                    </div>

                    <div class="col-md-3 mb-2">
                        <div class="socio-label">Tipo Asociado</div>
                        <div class="socio-value">{{ $asociado->tipo_asociado->descripcion }}</div>
                    </div>

                    <div class="col-md-3 mb-2">
                        <div class="socio-label">N° Socio</div>
                        <div class="socio-value">{{ $asociado->numero_socio ?? 'SIN ESPECIFICAR' }}</div>
                    </div>

                    <div class="col-md-3 mb-2">
                        <div class="socio-label">Estado</div>
                        <div class="socio-value">{{ $asociado->estado->descripcion ?? 'ACTIVO' }}</div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 mb-2">
                    <div class="resumen-box bg-primary">
                        Total Aportado
                        <div class="valor">
                            {{ number_format($asociado->aportes_activo()->sum('aporte'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-2">
                    <div class="resumen-box bg-success">
                        Cantidad Aportes
                        <div class="valor">
                            {{ number_format($asociado->aportes_activo()->count(), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-2">
                    <div class="resumen-box bg-warning">
                        Antigüedad
                        <div class="valor">{{$antiguedad}}</div>
                    </div>
                </div>

                <div class="col-md-3 mb-2">
                    <div class="resumen-box bg-dark">
                        Último Aporte
                        <div class="valor">
                            @php
                                $ultimo = $asociado->aportes_activo()->latest('fecha_aporte')->first();
                            @endphp

                            {{ $ultimo ? \Carbon\Carbon::parse($ultimo->fecha_aporte)->format('d/m/Y') : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-aportes">
                    <thead>
                        <tr>
                            <th class="text-center">Periodo</th>
                            <th class="text-center">Fecha Aporte</th>
                            <th class="text-right">Monto</th>
                            <th class="text-center">Recibo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($cantidadAnterior > 0)
                            <tr>
                                <td class="text-center">ANTERIOR</td>
                                <td class="text-center">Aportes anteriores agrupados</td>
                                <td class="text-right">
                                    {{ number_format($totalAnterior, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    {{ $cantidadAnterior }} aportes
                                </td>
                            </tr>
                        @endif

                        @forelse($aportesUltimos as $item)
                            <tr>
                                <td class="text-center">
                                    {{ str_pad($item->mes, 2, '0', STR_PAD_LEFT) }}/{{ $item->anio }}
                                </td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($item->fecha_aporte)->format('d/m/Y') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($item->aporte, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    {{ $item->recibo->sucursal }}-{{ $item->recibo->general }}-{{ str_pad($item->recibo->numero, 7, '0', STR_PAD_LEFT) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    No se encontraron aportes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">TOTAL</th>
                            <th class="text-right">
                                {{ number_format($asociado->aportes_activo()->sum('aporte'), 0, ',', '.') }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection


@section('js')
    <script src="{{asset('plugins/sweetalerts/sweetalert2.min.js')}}"></script>
    <script src="{{asset('plugins/sweetalerts/custom-sweetalert.js')}}"></script>
    <script src="{{asset('js/cobro_planilla.js')}}"></script>
@endsection
