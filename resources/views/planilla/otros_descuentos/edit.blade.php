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
                    <div class="form-group col-md-3"><label>Cantidad de cuotas</label><input name="cantidad_cuotas" type="number" min="1" max="60" class="form-control" value="{{ old('cantidad_cuotas', $otroDescuento->cantidad_cuotas) }}" required></div>
                    <div class="form-group col-md-3"><label>Mes de inicio</label><select name="mes_inicio" class="form-control" required>@foreach ([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $numero => $nombre)<option value="{{ $numero }}" {{ old('mes_inicio', $otroDescuento->mes_inicio) == $numero ? 'selected' : '' }}>{{ $nombre }}</option>@endforeach</select></div>
                    <div class="form-group col-md-3"><label>Año de inicio</label><input name="anio_inicio" type="number" min="2020" max="2100" class="form-control" value="{{ old('anio_inicio', $otroDescuento->anio_inicio) }}" required></div>
                </div>
                <div class="alert alert-info">Al guardar, se actualizará el calendario de cuotas de todos los socios asignados. Solo se puede editar si ninguna cuota está en una planilla activa o pagada.</div>
                <button class="btn btn-success" type="submit">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
@endsection
