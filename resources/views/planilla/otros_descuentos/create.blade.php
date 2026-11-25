@extends('layouts.admin')

@section('content')
<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Crear otro descuento</h3>
                <a href="{{ route('planilla.descuentos.index') }}" class="btn btn-secondary">Volver</a>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('planilla.otros-descuentos.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-6"><label>Concepto / evento</label><input name="nombre" class="form-control" value="{{ old('nombre') }}" required maxlength="150" placeholder="Cena de fin de año"></div>
                    <div class="form-group col-md-6"><label>Descripción</label><input name="descripcion" class="form-control" value="{{ old('descripcion') }}" maxlength="2000"></div>
                    <div class="form-group col-md-3"><label>Monto total por socio (G.)</label><input name="monto_por_socio" type="number" min="1" step="1" class="form-control" value="{{ old('monto_por_socio') }}" required></div>
                    <div class="form-group col-md-6"><label>Máximo de cuotas por socio</label><input name="cantidad_cuotas" type="number" min="1" max="60" class="form-control" value="{{ old('cantidad_cuotas', 1) }}" required></div>
                </div>
                <div class="alert alert-info">El monto total y el máximo de cuotas se definen para el concepto. Al agregar cada socio podrá elegir si paga en una sola cuota o en varias, además del mes y año de inicio.</div>
                <button class="btn btn-success" type="submit">Guardar concepto</button>
            </form>
        </div>
    </div>
</div>
@endsection

