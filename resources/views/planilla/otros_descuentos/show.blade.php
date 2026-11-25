@extends('layouts.admin')

@section('content')
<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><h3 class="mb-0">{{ $otroDescuento->nombre }}</h3><div>{{ $otroDescuento->descripcion }}</div></div>
                <div>
                    <a href="{{ route('planilla.descuentos.index') }}" class="btn btn-secondary">Volver</a>
                    @can('planilla.descuentos.edit')
                        <a href="{{ route('planilla.otros-descuentos.edit', $otroDescuento) }}" class="btn btn-warning">Editar</a>
                    @endcan
                    @can('planilla.descuentos.socios')
                        <a href="{{ route('planilla.otros-descuentos.socios.create', $otroDescuento) }}" class="btn btn-primary">Agregar socios</a>
                    @endcan
                    @can('planilla.descuentos.destroy')
                        <form method="POST" action="{{ route('planilla.otros-descuentos.destroy', $otroDescuento) }}" class="d-inline js-confirm-swal" data-title="¿Eliminar este descuento?" data-text="Se eliminarán también las asignaciones y cuotas pendientes." data-confirm-text="Sí, eliminar">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Eliminar descuento</button>
                        </form>
                    @endcan
                </div>
            </div>
            @include('varios.mensaje')
            <p>Monto total por socio: <strong>G. {{ number_format($otroDescuento->monto_por_socio, 0, ',', '.') }}</strong> · Máximo {{ $otroDescuento->cantidad_cuotas }} cuota(s) por socio</p>
            <div class="table-responsive"><table class="table table-bordered table-hover">
                <thead><tr><th>Socio</th><th>Cuota</th><th>Período</th><th>Monto</th><th>Estado</th><th>Planilla</th><th>Acción</th></tr></thead><tbody>
                @foreach ($otroDescuento->asociados as $asignacion)
                    @foreach ($asignacion->cuotas as $cuota)
                        <tr>
                            <td>{{ $asignacion->asociado->numero_socio }} · {{ $asignacion->asociado->persona->apellido }}, {{ $asignacion->asociado->persona->nombre }}</td>
                            <td>{{ $cuota->numero_cuota }}/{{ $asignacion->cantidad_cuotas }}</td>
                            <td>{{ str_pad($cuota->mes, 2, '0', STR_PAD_LEFT) }}/{{ $cuota->anio }}</td>
                            <td>G. {{ number_format($cuota->monto_esperado, 0, ',', '.') }}</td>
                            <td>{{ [1=>'Pendiente',2=>'Pagado',3=>'Parcial',4=>'Anulado',5=>'En planilla',6=>'No cobrado'][$cuota->estado_pago_id] ?? '—' }}</td>
                            <td>{{ $cuota->planilla_detalle_id ?? '—' }}</td>
                            @if ($loop->first)
                                <td rowspan="{{ $asignacion->cuotas->count() }}">
                                    @can('planilla.descuentos.socios')
                                        <form method="POST" action="{{ route('planilla.otros-descuentos.socios.destroy', [$otroDescuento, $asignacion]) }}" class="js-confirm-swal" data-title="¿Quitar este socio?" data-text="Se eliminarán sus cuotas pendientes de este descuento." data-confirm-text="Sí, quitar">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" type="submit">Quitar socio</button>
                                        </form>
                                    @endcan
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
                @if ($otroDescuento->asociados->isEmpty())<tr><td colspan="7" class="text-center">Todavía no hay socios asignados.</td></tr>@endif
                </tbody></table></div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.querySelectorAll('.js-confirm-swal').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            title: form.dataset.title,
            text: form.dataset.text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmText,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
        }).then(function (result) {
            if (!result.value) return;

            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = true;
            form.submit();
        });
    });
});
</script>
@endsection
