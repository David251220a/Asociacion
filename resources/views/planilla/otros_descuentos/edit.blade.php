@extends('layouts.admin')

@section('content')
<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Editar descuento</h3>
                <a href="{{ route('planilla.otros-descuentos.show', $otroDescuento) }}" class="btn btn-secondary">Volver</a>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('planilla.otros-descuentos.update', $otroDescuento) }}">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group col-md-6"><label>Concepto / evento</label><input name="nombre" class="form-control" value="{{ old('nombre', $otroDescuento->nombre) }}" required maxlength="150"></div>
                    <div class="form-group col-md-6"><label>Descripción</label><input name="descripcion" class="form-control" value="{{ old('descripcion', $otroDescuento->descripcion) }}" maxlength="2000"></div>
                    <div class="form-group col-md-3"><label>Monto total por socio (G.)</label><input name="monto_por_socio" type="number" min="1" step="1" class="form-control" value="{{ old('monto_por_socio', $otroDescuento->monto_por_socio) }}" required></div>
                    <div class="form-group col-md-6"><label>Máximo de cuotas por socio</label><input name="cantidad_cuotas" type="number" min="1" max="60" class="form-control" value="{{ old('cantidad_cuotas', $otroDescuento->cantidad_cuotas) }}" required></div>
                </div>
                <div class="alert alert-info">Al guardar, se actualizará el monto total de las asignaciones. El máximo no puede quedar por debajo de las cuotas ya elegidas para un socio. Los períodos individuales se mantienen.</div>
                <button class="btn btn-success" type="submit">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
@endsection
