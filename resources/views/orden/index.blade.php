@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <link href="{{ asset('assets/css/elements/infobox.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .orden-page { --orden-ink:#1f2937; --orden-muted:#718096; --orden-border:#e6ebf2; }
        .orden-heading { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.25rem; }
        .orden-heading h3 { color:var(--orden-ink); font-size:1.55rem; font-weight:700; letter-spacing:-.02em; }
        .orden-heading p { margin:.35rem 0 0; color:var(--orden-muted); }
        .orden-heading .btn { border-radius:.55rem; padding:.65rem 1rem; font-weight:600; box-shadow:0 5px 14px rgba(27,85,226,.16); }
        .orden-card { overflow:hidden; border:1px solid var(--orden-border); border-radius:.8rem; box-shadow:0 8px 24px rgba(31,41,55,.045); }
        .orden-card .widget-content-area { padding:1.35rem; }
        .orden-filter { margin:0 0 1rem; padding:1rem; border:1px solid var(--orden-border); border-radius:.7rem; background:#f8fafc; }
        .orden-filter label { margin-bottom:.4rem; color:#566274; font-size:.78rem; font-weight:700; letter-spacing:.035em; text-transform:uppercase; }
        .orden-filter .form-control { height:42px; border-color:#dce3ec; border-radius:.5rem; background:#fff; }
        .orden-filter .form-control:focus { border-color:#91b4ff; box-shadow:0 0 0 .18rem rgba(27,85,226,.12); }
        .orden-filter .btn { height:42px; border-radius:.5rem; font-weight:600; }
        .orden-summary { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin:0 0 .9rem; color:var(--orden-muted); font-size:.9rem; }
        .orden-summary strong { color:var(--orden-ink); }
        .orden-table-wrap { border:1px solid var(--orden-border); border-radius:.65rem; }
        .orden-table { min-width:1000px; margin:0; color:#344054; }
        .orden-table thead th { padding:.9rem .8rem; border:0; border-bottom:1px solid var(--orden-border); background:#f3f6fb; color:#667085; font-size:.72rem; font-weight:700; letter-spacing:.045em; text-transform:uppercase; white-space:nowrap; }
        .orden-table tbody td { padding:.85rem .8rem; border-color:#edf0f5; vertical-align:middle; }
        .orden-table tbody tr:hover { background:#f9fbff; }
        .orden-number { color:#26364d; font-weight:700; white-space:nowrap; }
        .orden-date { color:#667085; white-space:nowrap; }
        .orden-beneficiario { color:#344054; font-weight:600; }
        .orden-concepto { min-width:210px; max-width:320px; color:#667085; white-space:normal; }
        .orden-total { color:#26364d; font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
        .orden-status { display:inline-flex; padding:.32rem .58rem; border-radius:99px; font-size:.72rem; font-weight:700; white-space:nowrap; }
        .orden-status.is-approved { background:#e8f7ef; color:#18824b; }
        .orden-status.is-void { background:#fff0f0; color:#b42318; }
        .orden-status.is-pending { background:#fff3df; color:#9a6500; }
        .orden-actions { display:flex; align-items:center; justify-content:center; gap:.38rem; white-space:nowrap; }
        .orden-action { display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; border:1px solid #dfe5ed; border-radius:.45rem; background:#fff; color:#58677b; transition:all .15s ease; }
        .orden-action:hover { border-color:#9bb8ed; background:#f0f5ff; color:#1b55e2; text-decoration:none; }
        .orden-action.danger { color:#c24141; }
        .orden-action.danger:hover { border-color:#efb6b6; background:#fff1f1; color:#b42318; }
        .orden-action i { font-size:15px; }
        .orden-empty { padding:2.75rem 1rem !important; color:var(--orden-muted); text-align:center; }
        .orden-table tfoot th { padding:.9rem .8rem; border-top:1px solid #dce3ec; background:#f8fafc; color:#344054; }
        .orden-grand-total { color:#1b55e2 !important; font-variant-numeric:tabular-nums; font-size:1rem; }
        .orden-pagination { display:flex; justify-content:flex-end; margin-top:1rem; }
        .orden-pagination nav { margin-bottom:0; }
        .opcion-anulacion { display:flex; align-items:flex-start; width:100%; margin-bottom:12px; padding:16px; border:1px solid #dfe5eb; border-radius:10px; background:#fff; cursor:pointer; transition:all .2s ease; }
        .opcion-anulacion:hover { border-color:#1b6fc2; background:#f5f9fd; }
        .opcion-anulacion input { margin-top:5px; margin-right:13px; }
        .opcion-anulacion span { display:block; }
        .opcion-anulacion strong { display:block; margin-bottom:4px; color:#1b3a5b; }
        .opcion-anulacion small { display:block; color:#6c757d; line-height:1.5; }
        @media (max-width:767.98px) {
            .orden-heading { align-items:flex-start; flex-direction:column; }
            .orden-heading .btn { width:100%; }
            .orden-card .widget-content-area { padding:1rem; }
            .orden-summary { align-items:flex-start; flex-direction:column; gap:.2rem; }
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 layout-spacing orden-page">
        <div class="statbox widget box box-shadow orden-card">
            <div class="widget-content widget-content-area">
                <div class="orden-heading">
                    <div>
                        <h3 class="mb-0">Órdenes de pago</h3>
                        <p>Consulta, registra pagos y revisa el estado de las órdenes.</p>
                    </div>
                    @can('orden.create')
                        <a href="{{ route('orden.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i> Crear orden
                        </a>
                    @endcan
                </div>

                @include('varios.mensaje')

                <form action="{{ route('orden.index') }}" method="GET" class="orden-filter">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-3 mb-md-0">
                            <label for="fecha_desde">Fecha desde</label>
                            <input type="date" id="fecha_desde" name="fecha_desde" value="{{ $fecha_desde }}" class="form-control">
                        </div>
                        <div class="form-group col-md-3 mb-md-0">
                            <label for="fecha_hasta">Fecha hasta</label>
                            <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{ $fecha_hasta }}" class="form-control">
                        </div>
                        <div class="form-group col-md-3 mb-md-0">
                            <label for="tipo_egrego_id">Tipo de egreso</label>
                            <select name="tipo_egrego_id" id="tipo_egrego_id" class="form-control">
                                <option value="0" {{ request('tipo_egrego_id') == 0 ? 'selected' : '' }}>Todos</option>
                                @foreach ($tipo_egresos as $item)
                                    <option value="{{ $item->id }}" {{ request('tipo_egrego_id') == $item->id ? 'selected' : '' }}>{{ $item->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-0">
                            <label for="estado">Estado</label>
                            <div class="input-group">
                                <select name="estado" id="estado" class="form-control">
                                    <option value="9" {{ $estado == 9 ? 'selected' : '' }}>Todos</option>
                                    <option value="1" {{ $estado == 1 ? 'selected' : '' }}>Pagado</option>
                                    <option value="2" {{ $estado == 2 ? 'selected' : '' }}>Anulado</option>
                                    <option value="0" {{ $estado == 0 ? 'selected' : '' }}>Pendiente</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search mr-1"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="orden-summary">
                    <span>Órdenes encontradas: <strong>{{ $data->total() }}</strong></span>
                    <span>Total general: <strong>G. {{ number_format($totalGeneral, 0, ',', '.') }}</strong></span>
                </div>

                <div class="table-responsive orden-table-wrap">
                    <table class="table orden-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Orden de pago</th>
                                <th>Tipo de egreso</th>
                                <th>Beneficiario</th>
                                <th>Concepto</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                                <tr>
                                    <td class="orden-date">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="orden-number">{{ str_pad($item->numero, 7, '0', STR_PAD_LEFT) }}/{{ $item->anio }}</span></td>
                                    <td>{{ $item->tipo_egreso->descripcion ?? '' }}</td>
                                    <td class="orden-beneficiario">{{ $item->persona->nombre }} {{ $item->persona->apellido }}</td>
                                    <td class="orden-concepto">{{ $item->concepto }}</td>
                                    <td class="text-right orden-total">G. {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($item->estado_pago == 1)
                                            <span class="orden-status is-approved">Aprobado</span>
                                        @elseif ($item->estado_pago == 2)
                                            <span class="orden-status is-void">Anulado</span>
                                        @else
                                            <span class="orden-status is-pending">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="orden-actions">
                                            @can('orden.pago')
                                                @if ($item->pagos->sum('monto') <> $item->total)
                                                    <a href="{{ route('orden.pago', $item) }}" class="orden-action" title="Registrar pago" aria-label="Registrar pago"><i class="fas fa-receipt"></i></a>
                                                @endif
                                            @endcan

                                            @can('orden.show')
                                                <a href="{{ route('orden.show', $item) }}" class="orden-action" title="Ver orden de pago" aria-label="Ver orden de pago"><i class="fas fa-eye"></i></a>
                                            @endcan

                                            @can('orden.anular')
                                                @if ($item->estado_pago <> 2)
                                                    <button type="button" class="orden-action danger" title="Anular orden de pago" aria-label="Anular orden de pago" data-toggle="modal" data-target="#anular_dos_{{ $item->id }}"><i class="fas fa-trash-alt"></i></button>
                                                @endif
                                            @endcan

                                            <a href="{{ route('pdf.orden', $item) }}" target="_blank" class="orden-action" title="Imprimir orden" aria-label="Imprimir orden"><i class="fas fa-print"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @include('orden.anular')
                                @include('orden.anular_dos')
                            @empty
                                <tr><td colspan="8" class="orden-empty"><i class="fa fa-folder-open-o fa-2x d-block mb-2"></i>No se encontraron órdenes con estos filtros.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5">Total general</th>
                                <th class="text-right orden-grand-total">G. {{ number_format($totalGeneral, 0, ',', '.') }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="orden-pagination">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function bloquearAnulacion(formulario) {
            if (formulario.dataset.enviado === '1') return false;
            formulario.dataset.enviado = '1';

            const boton = formulario.querySelector('.btn-confirmar-anulacion');
            if (boton) {
                boton.disabled = true;
                boton.style.pointerEvents = 'none';
                boton.innerHTML = `
                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                    Anulando...
                `;
            }

            setTimeout(function () { formulario.submit(); }, 400);
            return false;
        }
    </script>
@endsection
