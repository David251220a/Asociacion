@extends('layouts.admin')

@section('content')
<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="mb-4">
                <h3 class="mb-1">Descuentos de préstamos de emergencia</h3>
                <p class="text-muted mb-0">Consulta las cuotas del mes y revisa cuánto se descontó y su estado de cobro.</p>
            </div>

            @include('varios.mensaje')
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="GET" action="{{ route('consulta.prestamos-descuentos.index') }}" class="mb-4">
                <input type="hidden" name="consultar" value="1">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-2">
                        <label for="mes">Mes</label>
                        <select id="mes" name="mes" class="form-control" required>
                            @foreach ([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $numero => $nombre)
                                <option value="{{ $numero }}" {{ $mes === $numero ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="anio">Año</label>
                        <input id="anio" name="anio" type="number" min="2000" max="2100" class="form-control" value="{{ $anio }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="tipo_asociado">Tipo de asociado</label>
                        <select id="tipo_asociado" name="tipo_asociado" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="jubilado" {{ $tipoAsociado === 'jubilado' ? 'selected' : '' }}>Jubilados y pensionados (todos juntos)</option>
                            <option value="activo" {{ $tipoAsociado === 'activo' ? 'selected' : '' }}>Afiliado activo</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3" id="institucionGrupo" style="display: {{ $tipoAsociado === 'activo' ? 'block' : 'none' }};">
                        <label for="institucion_id">Municipio / institución municipal</label>
                        <select id="institucion_id" name="institucion_id" class="form-control">
                            <option value="">Seleccione la institución</option>
                            @foreach ($instituciones as $institucion)
                                <option value="{{ $institucion->id }}" {{ (string) $institucionId === (string) $institucion->id ? 'selected' : '' }}>{{ $institucion->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <button class="btn btn-primary btn-block" type="submit">Consultar</button>
                    </div>
                </div>
            </form>

            @if ($consultaEjecutada && $cuotas)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        Cuotas con vencimiento en {{ str_pad((string) $mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}
                        @if ($tipoAsociado === 'activo')
                            · {{ $instituciones->firstWhere('id', (int) $institucionId)?->descripcion }}
                        @else
                            · Jubilados y pensionados
                        @endif
                    </h5>
                    <span class="badge badge-light">{{ $cuotas->total() }} cuotas</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>N.º socio</th>
                                <th>Documento</th>
                                <th>Persona</th>
                                @if ($tipoAsociado === 'activo')<th>Institución</th>@endif
                                <th>Préstamo</th>
                                <th>Cuota</th>
                                <th>Vencimiento</th>
                                <th>Descuento</th>
                                <th>Cobrado</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cuotas as $cuota)
                                <tr>
                                    <td>{{ $cuota->numero_socio }}</td>
                                    <td>{{ $cuota->documento }}</td>
                                    <td>{{ $cuota->apellido }}, {{ $cuota->nombre }}</td>
                                    @if ($tipoAsociado === 'activo')<td>{{ $cuota->institucion ?? '—' }}</td>@endif
                                    <td>{{ $cuota->anio_prestamo }}-{{ str_pad((string) $cuota->numero_prestamo, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cuota->numero_cuota }}/{{ $cuota->total_cuotas }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') }}</td>
                                    <td class="text-right">G. {{ number_format($cuota->monto_total, 0, ',', '.') }}</td>
                                    <td class="text-right">G. {{ number_format($cuota->monto_pagado, 0, ',', '.') }}</td>
                                    <td class="text-right">G. {{ number_format($cuota->saldo_total, 0, ',', '.') }}</td>
                                    <td><span class="badge badge-{{ $cuota->clase_estado }}">{{ $cuota->estado_consulta }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $tipoAsociado === 'activo' ? 11 : 10 }}" class="text-center py-4">No hay cuotas de préstamos de emergencia para los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $cuotas->links() }}
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const tipo = document.getElementById('tipo_asociado');
        const institucionGrupo = document.getElementById('institucionGrupo');
        const institucion = document.getElementById('institucion_id');

        function actualizarInstitucion() {
            const requiereInstitucion = tipo.value === 'activo';
            institucionGrupo.style.display = requiereInstitucion ? '' : 'none';
            institucion.required = requiereInstitucion;
            if (!requiereInstitucion) institucion.value = '';
        }

        tipo.addEventListener('change', actualizarInstitucion);
        actualizarInstitucion();
    })();
</script>
@endsection
