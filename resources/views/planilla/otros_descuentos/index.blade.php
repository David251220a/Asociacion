@extends('layouts.admin')

@section('content')
<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Otros descuentos para socios</h3>
                @can('planilla.descuentos.create')
                    <a href="{{ route('planilla.otros-descuentos.create') }}" class="btn btn-primary">Crear descuento</a>
                @endcan
            </div>
            @include('varios.mensaje')
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead><tr><th>Concepto</th><th>Por socio</th><th>Cuotas</th><th>Inicio</th><th>Socios</th><th>Asignado</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($descuentos as $item)
                        <tr>
                            <td>{{ $item->nombre }}</td>
                            <td>G. {{ number_format($item->monto_por_socio, 0, ',', '.') }}</td>
                            <td>{{ $item->cantidad_cuotas }}</td>
                            <td>{{ str_pad($item->mes_inicio, 2, '0', STR_PAD_LEFT) }}/{{ $item->anio_inicio }}</td>
                            <td>{{ $item->asociados_count }}</td>
                            <td>G. {{ number_format($item->monto_asignado ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @can('planilla.descuentos.show')
                                    <a href="{{ route('planilla.otros-descuentos.show', $item) }}" class="btn btn-info btn-sm">Ver detalle</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Todavía no hay otros descuentos registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $descuentos->links() }}
        </div>
    </div>
</div>
@endsection
