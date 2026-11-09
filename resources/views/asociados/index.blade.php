@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <link href="{{ asset('assets/css/elements/infobox.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .asociado-page { --asociado-ink:#1f2937; --asociado-muted:#718096; --asociado-border:#e6ebf2; }
        .asociado-heading { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.25rem; }
        .asociado-heading h3 { color:var(--asociado-ink); font-size:1.55rem; font-weight:700; letter-spacing:-.02em; }
        .asociado-heading p { margin:.35rem 0 0; color:var(--asociado-muted); }
        .asociado-heading .btn { border-radius:.55rem; padding:.65rem 1rem; font-weight:600; box-shadow:0 5px 14px rgba(27,85,226,.16); }
        .asociado-card { overflow:hidden; border:1px solid var(--asociado-border); border-radius:.8rem; box-shadow:0 8px 24px rgba(31,41,55,.045); }
        .asociado-card .widget-content-area { padding:1.35rem; }
        .asociado-search { margin:0 0 1rem; padding:1rem; border:1px solid var(--asociado-border); border-radius:.7rem; background:#f8fafc; }
        .asociado-search label { margin-bottom:.4rem; color:#566274; font-size:.78rem; font-weight:700; letter-spacing:.035em; text-transform:uppercase; }
        .asociado-search .form-control { height:42px; border-color:#dce3ec; border-radius:.5rem; background:#fff; }
        .asociado-search .form-control:focus { border-color:#91b4ff; box-shadow:0 0 0 .18rem rgba(27,85,226,.12); }
        .asociado-search .btn { height:42px; min-width:48px; border-radius:.5rem; }
        .asociado-summary { display:flex; justify-content:space-between; gap:1rem; margin:0 0 .9rem; color:var(--asociado-muted); font-size:.9rem; }
        .asociado-summary strong { color:var(--asociado-ink); }
        .asociado-table-wrap { border:1px solid var(--asociado-border); border-radius:.65rem; }
        .asociado-table { min-width:930px; margin:0; color:#344054; }
        .asociado-table thead th { padding:.9rem .8rem; border:0; border-bottom:1px solid var(--asociado-border); background:#f3f6fb; color:#667085; font-size:.72rem; font-weight:700; letter-spacing:.045em; text-transform:uppercase; white-space:nowrap; }
        .asociado-table tbody td { padding:.85rem .8rem; border-color:#edf0f5; vertical-align:middle; }
        .asociado-table tbody tr:hover { background:#f9fbff; }
        .asociado-number { color:#26364d; font-weight:700; white-space:nowrap; }
        .asociado-documento { color:#667085; font-variant-numeric:tabular-nums; white-space:nowrap; }
        .asociado-name { color:#344054; font-weight:600; }
        .asociado-type { display:inline-flex; padding:.32rem .58rem; border-radius:99px; background:#eef4ff; color:#315da8; font-size:.72rem; font-weight:700; }
        .asociado-contact { color:#667085; }
        .asociado-address { min-width:150px; max-width:270px; color:#667085; white-space:normal; }
        .asociado-actions { display:flex; justify-content:center; align-items:center; gap:.38rem; white-space:nowrap; }
        .asociado-action { display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; border:1px solid #dfe5ed; border-radius:.45rem; background:#fff; color:#58677b; transition:all .15s ease; }
        .asociado-action:hover { border-color:#9bb8ed; background:#f0f5ff; color:#1b55e2; text-decoration:none; }
        .asociado-action svg { width:17px; height:17px; }
        .asociado-empty { padding:2.75rem 1rem !important; color:var(--asociado-muted); text-align:center; }
        .asociado-pagination { display:flex; justify-content:flex-end; margin-top:1rem; }
        .asociado-pagination nav { margin-bottom:0; }
        @media (max-width:767.98px) {
            .asociado-heading { align-items:flex-start; flex-direction:column; }
            .asociado-heading .btn { width:100%; }
            .asociado-card .widget-content-area { padding:1rem; }
            .asociado-summary { flex-direction:column; gap:.2rem; }
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 layout-spacing asociado-page">
        <div class="statbox widget box box-shadow asociado-card">
            <div class="widget-content widget-content-area">
                <div class="asociado-heading">
                    <div>
                        <h3 class="mb-0">Asociados</h3>
                        <p>Consulta los datos de contacto y gestiona la información de los asociados.</p>
                    </div>
                    @can('asociado.create')
                        <a href="{{ route('asociado.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i> Agregar asociado
                        </a>
                    @endcan
                </div>

                @include('varios.mensaje')

                <form action="{{ route('asociado.index') }}" method="GET" class="asociado-search">
                    <label for="search">Buscar asociados</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Nombre, apellido o documento..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary" title="Buscar" aria-label="Buscar"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>

                <div class="asociado-summary">
                    <span>Asociados encontrados: <strong>{{ $data->total() }}</strong></span>
                    <span>Mostrando {{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }} de {{ $data->total() }}</span>
                </div>

                <div class="table-responsive asociado-table-wrap">
                    <table class="table asociado-table">
                        <thead>
                            <tr>
                                <th>N.º socio</th>
                                <th>Documento</th>
                                <th>Asociado</th>
                                <th>Tipo</th>
                                <th>Celular</th>
                                <th>Dirección</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                                <tr>
                                    <td class="asociado-number">{{ number_format($item->numero_socio, 0, '.', '.') }}</td>
                                    <td class="asociado-documento">{{ $item->persona->documento }}</td>
                                    <td class="asociado-name">{{ $item->persona->nombre }} {{ $item->persona->apellido }}</td>
                                    <td><span class="asociado-type">{{ $item->tipo_asociado->descripcion }}</span></td>
                                    <td class="asociado-contact">{{ $item->persona->celular }}</td>
                                    <td class="asociado-address">{{ $item->persona->direccion }}</td>
                                    <td>
                                        <div class="asociado-actions">
                                            @can('asociado.edit')
                                                <a href="{{ route('asociado.edit', $item) }}" class="asociado-action" title="Editar asociado" aria-label="Editar asociado">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                </a>
                                            @endcan

                                            @can('ficha_medica.create')
                                                <a href="{{ route('ficha_medica.create', $item) }}" class="asociado-action" title="Ficha médica" aria-label="Ficha médica">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                </a>
                                            @endcan

                                            <a href="{{ route('asociado.solicitud_prestamos', $item) }}" class="asociado-action" title="Crear solicitud de préstamo" aria-label="Crear solicitud de préstamo">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 15h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.5.6L3 16"></path><path d="m7 20 1.6-1.4c.4-.4 1-.6 1.6-.6H15c1.1 0 2.1-.4 2.8-1.2L22 13"></path><path d="M2 15l6 6"></path><circle cx="16" cy="7" r="4"></circle><path d="M16 5v4"></path><path d="M14.8 6h2.1"></path><path d="M15.1 8h2.1"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="asociado-empty"><i class="fa fa-users fa-2x d-block mb-2"></i>No se encontraron asociados con esta búsqueda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="asociado-pagination">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
