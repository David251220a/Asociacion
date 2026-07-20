@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
    <style>

        .resumen-card{
            position:relative;
            background:#fff;
            border-radius:12px;
            padding:25px;
            box-shadow:0 5px 18px rgba(0,0,0,.08);
            border:1px solid #ececec;
            transition:.2s;
        }

        .resumen-card:hover{
            transform:translateY(-3px);
            box-shadow:0 12px 25px rgba(0,0,0,.12);
        }

        .resumen-icon{
            width:60px;
            height:60px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:26px;
            margin:-45px auto 15px auto;
            box-shadow:0 5px 15px rgba(0,0,0,.20);
        }

        .resumen-title{
            text-align:center;
            font-size:15px;
            color:#6c757d;
            font-weight:600;
            margin-bottom:8px;
        }

        .resumen-value{
            text-align:center;
            font-size:34px;
            font-weight:bold;
            margin-bottom:5px;
        }

        .resumen-sub{
            text-align:center;
            color:#999;
            font-size:13px;
        }

        .bg-info{background:#17a2b8;}
        .bg-success{background:#28a745;}
        .bg-danger{background:#dc3545;}
        .bg-primary{background:#4361ee;}
        .bg-dark{background:#3b3f5c;}

    </style>

    <style>
        .chart-card{
            background:#fff;
            border:1px solid #e9ecef;
            border-radius:12px;
            padding:20px;
            box-shadow:0 6px 18px rgba(0,0,0,.07);
        }

        .chart-title{
            font-size:16px;
            font-weight:600;
            color:#3b3f5c;
            margin-bottom:15px;
        }
    </style>
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Resumen de Ingresos y Egresos</h3>
                    </div>

                </div>

                <form method="GET" action="{{ route('resumen.index') }}">
                    <div class="form-row align-items-end">

                        <div class="form-group col-md-3">
                            <label>Mes</label>
                            <select name="mes" class="form-control">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $mes == $i ? 'selected' : '' }}>
                                        {{ strtoupper(\Carbon\Carbon::create()->month($i)->translatedFormat('F')) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Año</label>
                            <select name="anio" class="form-control">
                                @for($i = date('Y'); $i >= 2024; $i--)
                                    <option value="{{ $i }}" {{ $anio == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                        </div>

                        <div class="form-group col-md-2">
                            <a href="{{ route('resumen.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-sync-alt mr-1"></i> Limpiar
                            </a>
                        </div>

                        <div class="form-group col-md-2">
                            <a href="{{ route('resumen.recalcular') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-sync-alt mr-1"></i> Recalcular
                            </a>
                        </div>

                    </div>
                </form>

                <div class="row mt-5">

                    <div class="col-lg col-md-6 mb-5">
                        <div class="resumen-card">

                            <div class="resumen-icon bg-info">
                                <i class="fas fa-wallet"></i>
                            </div>

                            <div class="resumen-title">
                                SALDO ANTERIOR
                            </div>

                            <div class="resumen-value text-info">
                                {{ number_format($saldoAnterior,0,',','.') }}
                            </div>

                            <div class="resumen-sub">
                                Acumulado
                            </div>

                        </div>
                    </div>



                    <div class="col-lg col-md-6 mb-5">
                        <div class="resumen-card">

                            <div class="resumen-icon bg-success">
                                <i class="fas fa-arrow-up"></i>
                            </div>

                            <div class="resumen-title">
                                INGRESOS DEL MES
                            </div>

                            <div class="resumen-value text-success">
                                {{ number_format($totalIngreso,0,',','.') }}
                            </div>

                            <div class="resumen-sub">
                                Total de ingresos
                            </div>

                        </div>
                    </div>



                    <div class="col-lg col-md-6 mb-5">
                        <div class="resumen-card">

                            <div class="resumen-icon bg-danger">
                                <i class="fas fa-arrow-down"></i>
                            </div>

                            <div class="resumen-title">
                                EGRESOS DEL MES
                            </div>

                            <div class="resumen-value text-danger">
                                {{ number_format($totalEgreso,0,',','.') }}
                            </div>

                            <div class="resumen-sub">
                                Total de egresos
                            </div>

                        </div>
                    </div>



                    <div class="col-lg col-md-6 mb-5">
                        <div class="resumen-card">

                            <div class="resumen-icon bg-primary">
                                <i class="fas fa-chart-line"></i>
                            </div>

                            <div class="resumen-title">
                                RESULTADO DEL MES
                            </div>

                            <div class="resumen-value {{ $resultadoMes>=0 ? 'text-primary':'text-danger' }}">
                                {{ number_format($resultadoMes,0,',','.') }}
                            </div>

                            <div class="resumen-sub">
                                Ingresos - Egresos
                            </div>

                        </div>
                    </div>



                    <div class="col-lg col-md-6 mb-5">
                        <div class="resumen-card">

                            <div class="resumen-icon bg-dark">
                                <i class="fas fa-piggy-bank"></i>
                            </div>

                            <div class="resumen-title">
                                SALDO ACTUAL
                            </div>

                            <div class="resumen-value text-dark">
                                {{ number_format($saldoActual,0,',','.') }}
                            </div>

                            <div class="resumen-sub">
                                Saldo disponible
                            </div>

                        </div>
                    </div>

                </div>

<div class="row mt-4">

    <div class="col-lg-6 mb-4">
        <div class="chart-card">
            <div class="chart-title">Ingresos vs Egresos</div>
            <canvas id="graficoBarras" height="120"></canvas>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="chart-card">
            <div class="chart-title">Evolución del Saldo</div>
            <canvas id="graficoSaldo" height="120"></canvas>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="chart-card" style="height: 600px">
            <div class="chart-title">Ingresos por Tipo</div>
            <canvas id="graficoIngresosTipo" height="130"></canvas>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="chart-card" style="height: 600px">
            <div class="chart-title">Egresos por Tipo</div>
            <canvas id="graficoEgresosTipo" height="130"></canvas>
        </div>
    </div>

</div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="statbox widget box box-shadow">
                            <div class="widget-content widget-content-area">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Detalle de Movimientos</h5>
                                    <span class="badge badge-light">
                                        {{ $detalles->total() }} registros
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 100px;">Fecha</th>
                                                <th style="width: 110px;">Tipo</th>
                                                <th style="width: 120px;">Documento</th>
                                                <th style="width: 140px;">Número</th>
                                                <th>Concepto</th>
                                                <th class="text-right" style="width: 130px;">Ingreso</th>
                                                <th class="text-right" style="width: 130px;">Egreso</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse($detalles as $item)
                                                <tr>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}
                                                    </td>

                                                    <td>
                                                        @if($item->tipo == 'INGRESO')
                                                            <span class="badge badge-success">Ingreso</span>
                                                        @else
                                                            <span class="badge badge-danger">Egreso</span>
                                                        @endif
                                                    </td>

                                                    <td>{{ $item->documento }}</td>

                                                    <td>{{ $item->numero }}</td>

                                                    <td>{{ $item->concepto }}</td>

                                                    <td class="text-right text-success">
                                                        @if($item->ingreso > 0)
                                                            {{ number_format($item->ingreso, 0, ',', '.') }}
                                                        @endif
                                                    </td>

                                                    <td class="text-right text-danger">
                                                        @if($item->egreso > 0)
                                                            {{ number_format($item->egreso, 0, ',', '.') }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">
                                                        No hay movimientos para el período seleccionado.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $detalles->links() }}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const formatoGs = (value) => {
            return new Intl.NumberFormat('es-PY').format(value);
        };

        // Barras: ingresos vs egresos
        new Chart(document.getElementById('graficoBarras'), {
            type: 'bar',
            data: {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    label: 'Total',
                    data: [
                        {{ $totalIngreso }},
                        {{ $totalEgreso }}
                    ],
                    backgroundColor: [
                        '#00ab55',
                        '#e7515a'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Gs. ' + formatoGs(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return formatoGs(value);
                            }
                        }
                    }
                }
            }
        });

        // Línea: evolución del saldo
        new Chart(document.getElementById('graficoSaldo'), {
            type: 'line',
            data: {
                labels: @json($graficoSaldoLabels ?? []),
                datasets: [{
                    label: 'Saldo',
                    data: @json($graficoSaldoDatos ?? []),
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, .12)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Saldo: Gs. ' + formatoGs(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return formatoGs(value);
                            }
                        }
                    }
                }
            }
        });

        // Donut ingresos por tipo
        new Chart(document.getElementById('graficoIngresosTipo'), {
            type: 'doughnut',
            data: {
                labels: @json($ingresosTipoLabels ?? []),
                datasets: [{
                    data: @json($ingresosTipoDatos ?? []),
                    backgroundColor: [
                        '#00ab55',
                        '#2196f3',
                        '#ffbb44',
                        '#805dca',
                        '#3b3f5c'
                    ]
                }]
            },
            options: {
                responsive:true,
                cutout:'75%',
                radius:'70%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': Gs. ' + formatoGs(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // Donut egresos por tipo
        new Chart(document.getElementById('graficoEgresosTipo'), {
            type: 'doughnut',
            data: {
                labels: @json($egresosTipoLabels ?? []),
                datasets: [{
                    data: @json($egresosTipoDatos ?? []),
                    backgroundColor: [
                        '#e7515a',
                        '#ffbb44',
                        '#805dca',
                        '#2196f3',
                        '#3b3f5c'
                    ]
                }]
            },
            options: {
                responsive: true,
                cutout:'75%',
                radius:'70%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': Gs. ' + formatoGs(context.raw);
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
