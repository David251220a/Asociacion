@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <link href="{{ asset('assets/css/elements/infobox.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .planilla-page { --planilla-ink: #1f2937; --planilla-muted: #718096; --planilla-border: #e6ebf2; }
        .planilla-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; }
        .planilla-heading h3 { color:var(--planilla-ink); font-size:1.55rem; font-weight:700; letter-spacing:-.02em; }
        .planilla-heading p { margin:.35rem 0 0; color:var(--planilla-muted); }
        .planilla-heading .btn { border-radius:.55rem; padding:.65rem 1rem; font-weight:600; box-shadow:0 5px 14px rgba(27,85,226,.16); }
        .planilla-card { overflow:hidden; border:1px solid var(--planilla-border); border-radius:.8rem; box-shadow:0 8px 24px rgba(31,41,55,.045); }
        .planilla-card .widget-content-area { padding:1.35rem; }
        .planilla-filter { margin:0 0 1rem; padding:1rem; border:1px solid var(--planilla-border); border-radius:.7rem; background:#f8fafc; }
        .planilla-filter label { margin-bottom:.4rem; color:#566274; font-size:.78rem; font-weight:700; letter-spacing:.035em; text-transform:uppercase; }
        .planilla-filter .form-control { height:42px; border-color:#dce3ec; border-radius:.5rem; background:#fff; }
        .planilla-filter .form-control:focus { border-color:#91b4ff; box-shadow:0 0 0 .18rem rgba(27,85,226,.12); }
        .planilla-filter-actions { display:flex; align-items:flex-end; gap:.5rem; }
        .planilla-filter-actions .btn { height:42px; border-radius:.5rem; font-weight:600; }
        .planilla-summary { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin:0 0 .9rem; color:var(--planilla-muted); font-size:.9rem; }
        .planilla-summary strong { color:var(--planilla-ink); }
        .planilla-table-wrap { border:1px solid var(--planilla-border); border-radius:.65rem; }
        .planilla-table { min-width:960px; margin:0; color:#344054; }
        .planilla-table thead th { padding:.9rem .8rem; border:0; border-bottom:1px solid var(--planilla-border); background:#f3f6fb; color:#667085; font-size:.72rem; font-weight:700; letter-spacing:.045em; text-transform:uppercase; white-space:nowrap; }
        .planilla-table tbody td { padding:.85rem .8rem; border-color:#edf0f5; vertical-align:middle; }
        .planilla-table tbody tr:hover { background:#f9fbff; }
        .planilla-number { color:#26364d; font-weight:700; white-space:nowrap; }
        .planilla-period { display:inline-flex; padding:.32rem .55rem; border-radius:.4rem; background:#eef4ff; color:#315da8; font-weight:600; white-space:nowrap; }
        .planilla-type { color:#475467; font-weight:600; }
        .planilla-amount { color:#26364d; font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
        .planilla-paid { display:inline-flex; align-items:center; gap:.35rem; padding:.32rem .58rem; border-radius:99px; font-size:.75rem; font-weight:700; }
        .planilla-paid.is-paid { background:#e8f7ef; color:#18824b; }
        .planilla-paid.is-pending { background:#fff3df; color:#9a6500; }
        .planilla-actions { display:flex; align-items:center; justify-content:center; gap:.38rem; white-space:nowrap; }
        .planilla-action { display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; border:1px solid #dfe5ed; border-radius:.45rem; background:#fff; color:#58677b; transition:all .15s ease; }
        .planilla-action:hover { border-color:#9bb8ed; background:#f0f5ff; color:#1b55e2; text-decoration:none; }
        .planilla-action svg { width:17px; height:17px; }
        .planilla-action.danger { color:#c24141; }
        .planilla-action.danger:hover { border-color:#efb6b6; background:#fff1f1; color:#b42318; }
        .planilla-empty { padding:2.75rem 1rem !important; color:var(--planilla-muted); text-align:center; }
        .planilla-pagination { display:flex; justify-content:flex-end; margin-top:1rem; }
        .planilla-pagination nav { margin-bottom:0; }
        @media (max-width:767.98px) {
            .planilla-heading { align-items:flex-start; flex-direction:column; }
            .planilla-heading .btn { width:100%; }
            .planilla-card .widget-content-area { padding:1rem; }
            .planilla-summary { align-items:flex-start; flex-direction:column; gap:.2rem; }
            .planilla-filter-actions { margin-top:.25rem; }
        }
    </style>
@endsection

@section('content')
    @php
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    @endphp

    <div class="col-lg-12 layout-spacing planilla-page">
        <div class="statbox widget box box-shadow planilla-card">
            <div class="widget-content widget-content-area">
                <div class="planilla-heading">
                    <div>
                        <h3 class="mb-0">Planillas generadas</h3>
                        <p>Consulta, descarga y gestiona las planillas de aportes y préstamos.</p>
                    </div>
                    @can('planilla.create')
                        <a href="{{ route('planilla.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i> Generar planilla
                        </a>
                    @endcan
                </div>

                @include('varios.mensaje')

                <form action="{{ route('planilla.index') }}" method="GET" class="planilla-filter">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-3 mb-md-0">
                            <label for="tipo_asociado_id">Tipo de asociado</label>
                            <select name="tipo_asociado_id" id="tipo_asociado_id" class="form-control">
                                <option value="0" {{ request('tipo_asociado_id', 0) == 0 ? 'selected' : '' }}>Todos</option>
                                <option value="3" {{ request('tipo_asociado_id') == 3 ? 'selected' : '' }}>Aportantes</option>
                                <option value="1" {{ request('tipo_asociado_id') == 1 ? 'selected' : '' }}>Jubilados/as</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-md-0">
                            <label for="mes">Mes</label>
                            <select name="mes" id="mes" class="form-control">
                                <option value="0" {{ request('mes', 0) == 0 ? 'selected' : '' }}>Todos los meses</option>
                                @foreach ($meses as $key => $nombre)
                                    <option value="{{ $key }}" {{ request('mes') == $key ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-md-0">
                            <label for="anio">Año</label>
                            <select name="anio" id="anio" class="form-control">
                                @php
                                    $anioActual = now()->year;
                                    $anioSeleccionado = request('anio', $anioActual);
                                @endphp
                                @for ($i = 0; $i <= 2; $i++)
                                    @php $anio = $anioActual - $i; @endphp
                                    <option value="{{ $anio }}" {{ $anioSeleccionado == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-0 planilla-filter-actions">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fa fa-search mr-1"></i> Buscar
                            </button>
                            <a href="{{ route('planilla.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros">
                                <i class="fa fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>

                <div class="planilla-summary">
                    <span>Resultados: <strong>{{ $data->total() }}</strong></span>
                    <span>Mostrando {{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }} de {{ $data->total() }}</span>
                </div>

                <div class="table-responsive planilla-table-wrap">
                    <table class="table planilla-table">
                        <thead>
                            <tr>
                                <th>Planilla</th>
                                <th>Período</th>
                                <th>Tipo de asociado</th>
                                <th>Institución</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Monto a cobrar</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right">Monto pagado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                                <tr>
                                    <td><span class="planilla-number">{{ $item->planilla_anio }}/{{ str_pad($item->planilla_numero, 5, '0', STR_PAD_LEFT) }}</span></td>
                                    <td><span class="planilla-period">{{ $item->anio }}/{{ str_pad($item->mes, 2, '0', STR_PAD_LEFT) }}</span></td>
                                    <td class="planilla-type">{{ $item->tipoAsociado->descripcion ?? '' }}</td>
                                    <td>
                                        @if ($item->tipo_asociado_id == 3)
                                            {{ $item->institucion->descripcion ?? '—' }}
                                        @else
                                            Jubilados
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($item->cantidad, 0, ',', '.') }}</td>
                                    <td class="text-right planilla-amount">G. {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($item->pagado == 1)
                                            <span class="planilla-paid is-paid"><i class="fa fa-check-circle"></i> Pagada</span>
                                        @else
                                            <span class="planilla-paid is-pending"><i class="fa fa-clock-o"></i> Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="text-right">G. {{ number_format($item->monto_pagado, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="planilla-actions">
                                            @can('planilla.exportarDetalle')
                                                <a href="{{ route('planilla.exportarDetalle', $item) }}" class="planilla-action" title="Descargar detalle" aria-label="Descargar detalle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                </a>
                                            @endcan

                                            <a href="{{ route('pdf.planilla', $item) }}" target="_blank" class="planilla-action" title="Imprimir planilla" aria-label="Imprimir planilla">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            @can('planilla.cobrar')
                                                @if ($item->pagado == 0)
                                                    <a href="{{ route('planilla.cobrar', $item) }}" class="planilla-action" title="Cobrar planilla" aria-label="Cobrar planilla">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                                    </a>
                                                @endif
                                            @endcan

                                            @can('planilla.anular')
                                                @php
                                                    $puedeAnular = true;
                                                    if ($item->pagado == 1) {
                                                        $puedeAnular = false;
                                                    }
                                                @endphp
                                                @if ($puedeAnular)
                                                    <button type="button" class="planilla-action danger" data-toggle="modal" data-target="#exampleModalCenter_{{ $item->id }}" title="Anular planilla" aria-label="Anular planilla">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </button>

                                                    <div class="modal fade" id="exampleModalCenter_{{ $item->id }}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="modalTitle_{{ $item->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalTitle_{{ $item->id }}">Anular planilla</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                                                </div>
                                                                <div class="modal-body">¿Está seguro que desea anular la planilla <strong>{{ $item->planilla_anio }}/{{ str_pad($item->planilla_numero, 5, '0', STR_PAD_LEFT) }}</strong>?</div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                                                    <form action="{{ route('planilla.anular', $item) }}" method="POST">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-danger">Anular planilla</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="planilla-empty"><i class="fa fa-folder-open-o fa-2x d-block mb-2"></i>No se encontraron planillas con estos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="planilla-pagination">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
