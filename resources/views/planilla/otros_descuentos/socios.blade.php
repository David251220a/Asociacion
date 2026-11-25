@extends('layouts.admin')

@section('content')
<div class="col-lg-12 layout-spacing">
    <div class="statbox widget box box-shadow">
        <div class="widget-content widget-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><h3 class="mb-0">Agregar socios</h3><div>{{ $otroDescuento->nombre }}</div></div>
                <a href="{{ route('planilla.otros-descuentos.show', $otroDescuento) }}" class="btn btn-secondary">Volver</a>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="alert alert-info">El descuento total por socio es G. {{ number_format($otroDescuento->monto_por_socio, 0, ',', '.') }}. Cada socio puede elegir entre 1 y {{ $otroDescuento->cantidad_cuotas }} cuota(s), además de su propio mes y año de inicio.</div>
            <form method="POST" action="{{ route('planilla.otros-descuentos.socios.store', $otroDescuento) }}">
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Socios activos disponibles</h5><input id="buscarSocio" class="form-control" style="max-width:300px" placeholder="Buscar por socio, nombre o documento"></div>
                <div class="table-responsive" style="max-height:450px;overflow:auto">
                    <table class="table table-sm table-bordered" id="tablaSocios"><thead><tr><th><input type="checkbox" id="seleccionarTodos"></th><th>N.º socio</th><th>Documento</th><th>Socio</th><th>Cuotas</th><th>Mes de inicio</th><th>Año de inicio</th></tr></thead><tbody>
                    @foreach ($asociados as $socio)
                        <tr>
                            <td><input type="checkbox" name="asociados[]" value="{{ $socio->id }}" {{ in_array($socio->id, old('asociados', [])) ? 'checked' : '' }}></td>
                            <td>{{ $socio->getRawOriginal('numero_socio') }}</td>
                            <td>{{ $socio->documento }}</td>
                            <td>{{ $socio->apellido }}, {{ $socio->nombre }}</td>
                            <td>
                                <select name="configuracion[{{ $socio->id }}][cantidad_cuotas]" class="form-control">
                                    @for ($cuotas = 1; $cuotas <= $otroDescuento->cantidad_cuotas; $cuotas++)
                                        <option value="{{ $cuotas }}" {{ old("configuracion.{$socio->id}.cantidad_cuotas", 1) == $cuotas ? 'selected' : '' }}>{{ $cuotas }} {{ $cuotas === 1 ? 'pago' : 'cuotas' }}</option>
                                    @endfor
                                </select>
                            </td>
                            <td>
                                <select name="configuracion[{{ $socio->id }}][mes_inicio]" class="form-control">
                                    @foreach ([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $numero => $nombre)
                                        <option value="{{ $numero }}" {{ old("configuracion.{$socio->id}.mes_inicio", now()->month) == $numero ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="configuracion[{{ $socio->id }}][anio_inicio]" min="2020" max="2100" class="form-control" value="{{ old("configuracion.{$socio->id}.anio_inicio", now()->year) }}"></td>
                        </tr>
                    @endforeach
                    @if ($asociados->isEmpty())<tr><td colspan="7" class="text-center">Todos los socios activos ya fueron agregados.</td></tr>@endif
                    </tbody></table>
                </div>
                @if ($asociados->isNotEmpty())<button class="btn btn-success mt-3" type="submit">Agregar y programar cuotas</button>@endif
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.getElementById('buscarSocio').addEventListener('input', function () {
    const filtro = this.value.toLocaleLowerCase();
    document.querySelectorAll('#tablaSocios tbody tr').forEach(row => {
        row.style.display = row.textContent.toLocaleLowerCase().includes(filtro) ? '' : 'none';
    });
});
document.getElementById('seleccionarTodos').addEventListener('change', function () {
    document.querySelectorAll('#tablaSocios tbody tr').forEach(row => {
        const checkbox = row.querySelector('input[type=checkbox]');
        if (checkbox && row.style.display !== 'none') checkbox.checked = this.checked;
    });
});
</script>
@endsection
