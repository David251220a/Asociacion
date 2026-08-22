@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <link rel="stylesheet" type="text/css"href="{{ asset('assets/css/tables/table-basic.css') }}">

    <style>
        .prestamo-aprobacion-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5ebf0;
        }

        .prestamo-aprobacion-header h3 {
            margin-bottom: 5px;
            color: #193b5c;
            font-weight: 700;
        }

        .prestamo-aprobacion-numero {
            color: #778795;
            font-size: 14px;
        }

        .prestamo-seccion {
            margin-bottom: 30px;
        }

        .prestamo-seccion-titulo {
            margin-bottom: 17px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e8edf2;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .prestamo-dato {
            height: 100%;
            padding: 15px;
            border: 1px solid #e4eaf0;
            border-radius: 10px;
            background: #f9fbfc;
        }

        .prestamo-dato-label {
            display: block;
            margin-bottom: 5px;
            color: #8493a1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .prestamo-dato-valor {
            display: block;
            color: #29445c;
            font-size: 15px;
            font-weight: 600;
        }

        .prestamo-resumen {
            padding: 22px;
            border: 1px solid #dce6ee;
            border-radius: 12px;
            background: linear-gradient(
                135deg,
                #ffffff 0%,
                #f1f7fb 100%
            );
        }

        .prestamo-resumen-fila {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            color: #657585;
        }

        .prestamo-resumen-fila strong {
            color: #29445c;
            text-align: right;
        }

        .prestamo-resumen-total {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #dce6ee;
        }

        .prestamo-resumen-total strong {
            color: #168552;
            font-size: 19px;
        }

        .prestamo-cuota-numero {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: #1b6fc2;
            background: #e5f0fb;
            font-weight: 700;
        }

        .prestamo-aprobar,
        .prestamo-rechazar {
            height: 100%;
            padding: 22px;
            border-radius: 12px;
        }

        .prestamo-aprobar {
            border: 1px solid #c9e8d7;
            background: #f5fbf7;
        }

        .prestamo-rechazar {
            border: 1px solid #f0d1d5;
            background: #fff8f8;
        }

        .prestamo-aprobar h5 {
            color: #168552;
            font-weight: 700;
        }

        .prestamo-rechazar h5 {
            color: #c03945;
            font-weight: 700;
        }

        .prestamo-accion-descripcion {
            min-height: 44px;
            color: #72808d;
            font-size: 13px;
        }

        @media (max-width: 767.98px) {
            .prestamo-aprobacion-header {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $puedeResolver = in_array((int) $data->estado_solicitud_id,[1, 2],true);
        $totalCapital = $data->detalles->sum(fn ($cuota) => (int) $cuota->monto_capital);
        $totalInteres = $data->detalles->sum(fn ($cuota) => (int) $cuota->monto_interes);
        $totalIva = $data->detalles->sum(fn ($cuota) => (int) $cuota->iva);
        $totalPagar = $data->detalles->sum(fn ($cuota) => (int) $cuota->monto_total);
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="prestamo-aprobacion-header">

                    <div>
                        <h3>Solicitud de préstamo de emergencia</h3>
                        <div class="prestamo-aprobacion-numero">
                            Solicitud N.º {{ str_pad($data->numero_solicitud,5,'0',STR_PAD_LEFT) }}/{{ $data->anio }}
                        </div>
                    </div>
                    <span class="badge badge-{{$data->estadoSolicitud->color ?? 'secondary'}}">
                        {{$data->estadoSolicitud->descripcion?? 'SIN ESTADO'}}
                    </span>

                </div>

                @include('varios.mensaje')

                <div class="prestamo-seccion">

                    <h5 class="prestamo-seccion-titulo">
                        Información del asociado
                    </h5>

                    <div class="row">

                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Nombre y apellido
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{ $persona->nombre }}
                                    {{ $persona->apellido }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Documento
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{ $persona->documento }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Número de asociado
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{$persona->asociado?->numero_socio ?? 'No registrado'}}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Celular
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{$persona->celular ?: 'No registrado'}}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Fecha de solicitud
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{\Carbon\Carbon::parse($data->fecha_solicitud)->format('d/m/Y')}}
                                </span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="prestamo-seccion">

                    <h5 class="prestamo-seccion-titulo">
                        Condiciones solicitadas
                    </h5>

                    <div class="row">

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Monto solicitado
                                </span>

                                <span class="prestamo-dato-valor">
                                    G. {{ number_format($data->monto_solicitado,0,',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Cantidad de cuotas
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{ $data->cantidad_cuotas }}
                                    {{(int) $data->cantidad_cuotas === 1 ? 'cuota' : 'cuotas'}}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Tasa aplicada por cuota
                                </span>

                                <span class="prestamo-dato-valor">
                                    {{ number_format($data->tasa_aplicada,2,',','.') }} %
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="prestamo-dato">
                                <span class="prestamo-dato-label">
                                    Total a devolver
                                </span>

                                <span class="prestamo-dato-valor">
                                    G. {{ number_format($totalPagar,0,',','.') }}
                                </span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="prestamo-seccion">

                    <h5 class="prestamo-seccion-titulo">
                        Detalle de cuotas
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover mb-0">

                            <thead>
                                <tr>
                                    <th>
                                        Cuota
                                    </th>

                                    <th>
                                        Vencimiento
                                    </th>

                                    <th class="text-right">
                                        Capital
                                    </th>

                                    <th class="text-right">
                                        Interés
                                    </th>

                                    @if ($totalIva > 0)
                                        <th class="text-right">
                                            IVA
                                        </th>
                                    @endif

                                    <th class="text-right">
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($data->detalles as $cuota)
                                    <tr>
                                        <td>
                                            <span class="prestamo-cuota-numero">
                                                {{ $cuota->numero_cuota }}
                                            </span>
                                        </td>

                                        <td>
                                            {{\Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y')}}
                                        </td>

                                        <td class="text-right">
                                            G. {{ number_format($cuota->monto_capital,0,',','.') }}
                                        </td>

                                        <td class="text-right">
                                            G. {{ number_format( $cuota->monto_interes,0, ',', '.') }}
                                        </td>

                                        @if ($totalIva > 0)
                                            <td class="text-right">
                                                G. {{ number_format($cuota->iva,0, ',','.') }}
                                            </td>
                                        @endif

                                        <td class="text-right">
                                            <strong>
                                                G. {{ number_format( $cuota->monto_total, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $totalIva > 0 ? 6 : 5 }}" class="text-center py-4">
                                            La solicitud no posee cuotas registradas.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                            @if ($data->detalles->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <th colspan="2">
                                            Totales
                                        </th>

                                        <th class="text-right">
                                            G. {{ number_format( $totalCapital, 0, ',', '.' ) }}
                                        </th>

                                        <th class="text-right">
                                            G. {{ number_format($totalInteres, 0, ',','.') }}
                                        </th>

                                        @if ($totalIva > 0)
                                            <th class="text-right">
                                                G. {{ number_format( $totalIva, 0,',', '.') }}
                                            </th>
                                        @endif

                                        <th class="text-right">
                                            G. {{ number_format($totalPagar, 0,',',  '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            @endif

                        </table>

                    </div>

                </div>

                @if ($data->observaciones)
                    <div class="prestamo-seccion">

                        <h5 class="prestamo-seccion-titulo">
                            Observaciones
                        </h5>

                        <div class="alert alert-light border">
                            {{ $data->observaciones }}
                        </div>

                    </div>
                @endif

                @if ($data->motivo_rechazo)
                    <div class="prestamo-seccion">

                        <div class="alert alert-danger">

                            <strong>
                                Motivo del rechazo
                            </strong>

                            <div class="mt-2">
                                {{ $data->motivo_rechazo }}
                            </div>

                        </div>

                    </div>
                @endif

                @if ($data->ordenPago)
                    <div class="prestamo-seccion">

                        <div class="alert alert-success">

                            <strong>
                                Orden de pago generada
                            </strong>

                            <div class="mt-2">
                                Orden N.º {{ str_pad($data->ordenPago->numero,7,'0',STR_PAD_LEFT) }}/{{ $data->ordenPago->anio }}
                            </div>

                            <a href="{{ route('orden.show',$data->ordenPago->id) }}" class="btn btn-success btn-sm mt-3">
                                Ver orden de pago
                            </a>

                        </div>

                    </div>
                @endif

                @if ($puedeResolver)

                    <div class="prestamo-seccion">

                        <h5 class="prestamo-seccion-titulo">
                            Resolución de la solicitud
                        </h5>

                        <div class="row">

                            <div class="col-lg-6 mb-4 mb-lg-0">

                                <div class="prestamo-aprobar">

                                    <h5>
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Aprobar solicitud
                                    </h5>

                                    <p class="prestamo-accion-descripcion">
                                        Se aprobará el préstamo y se generarán los documentos correspondientes.
                                    </p>

                                    <form id="formAprobarPrestamo" action="{{ route('solicitud.prestamo_emergencia_aprobar', $data->id) }}" method="POST">
                                        @csrf

                                        <div class="form-group">
                                            <label for="monto_aprobado">
                                                Monto aprobado <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" id="monto_aprobado" name="monto_aprobado" class="form-control @error('monto_aprobado') is-invalid @enderror"
                                                value="{{old('monto_aprobado',number_format($data->monto_solicitado,0, ',','.' ))}}"
                                                inputmode="numeric"
                                                autocomplete="off"
                                                required
                                            >

                                            @error('monto_aprobado')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <small class="text-muted">
                                                Si el monto cambia, las cuotas deberán recalcularse.
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="observacion_aprobacion">
                                                Observación
                                            </label>

                                            <textarea
                                                id="observacion_aprobacion"
                                                name="observaciones"
                                                class="form-control"
                                                rows="3"
                                                maxlength="255"
                                                placeholder="Observación opcional"
                                            >{{ old('observaciones') }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-block" id="btnAprobarPrestamo">
                                            <i class="fas fa-check mr-2"></i>
                                            Aprobar préstamo
                                        </button>
                                    </form>

                                </div>

                            </div>

                            <div class="col-lg-6">

                                <div class="prestamo-rechazar">

                                    <h5>
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Rechazar solicitud
                                    </h5>

                                    <p class="prestamo-accion-descripcion">
                                        Se rechazará la solicitud y el asociado podrá consultar el motivo.
                                    </p>

                                    <form id="formRechazarPrestamo" action="{{ route('solicitud.prestamo_emergencia_rechazar',$data->id) }}"method="POST">
                                        @csrf

                                        <div class="form-group">
                                            <label for="motivo_rechazo">
                                                Motivo del rechazo
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>

                                            <textarea
                                                id="motivo_rechazo"
                                                name="motivo_rechazo"
                                                class="form-control @error('motivo_rechazo') is-invalid @enderror"
                                                rows="5"
                                                placeholder="Describa el motivo del rechazo"
                                                required
                                            >{{ old('motivo_rechazo') }}</textarea>

                                            @error('motivo_rechazo')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-danger btn-block" id="btnRechazarPrestamo">
                                            <i class="fas fa-times mr-2"></i>
                                            Rechazar préstamo
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                @else

                    <div class="alert alert-light border">

                        Esta solicitud ya fue resuelta.

                        @if ($data->fecha_aprobacion_rechazo)
                            Fecha: {{\Carbon\Carbon::parse($data->fecha_aprobacion_rechazo)->format('d/m/Y')}}.
                        @endif

                    </div>

                @endif

                <div class="text-right mt-4">
                    <a href="{{ route('solicitud.prestamo_emergencia') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const inputMonto = document.getElementById('monto_aprobado');

            if (inputMonto) {
                inputMonto.addEventListener('input', function () {
                    const montoLimpio = this.value.replace(/\D/g,'');
                    if (!montoLimpio) {
                        this.value = '';
                        return;
                    }
                    this.value = Number(montoLimpio).toLocaleString('es-PY');
                });
            }

            const formAprobar = document.getElementById('formAprobarPrestamo');

            if (formAprobar) {
                formAprobar.addEventListener(
                    'submit',
                    function (evento) {
                        evento.preventDefault();
                        const monto = document.getElementById('monto_aprobado').value;
                        Swal.fire({
                            title: '¿Aprobar la solicitud?',
                            text:
                                'Se aprobará el préstamo por G. '
                                + monto
                                + '.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, aprobar',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#168552',
                            cancelButtonColor: '#6c757d',
                        }).then(function (resultado) {
                            if (!resultado.value) {
                                return;
                            }

                            const boton = document.getElementById('btnAprobarPrestamo');
                            boton.disabled = true;
                            boton.innerHTML ='Aprobando...';
                            formAprobar.submit();
                        });
                    }
                );
            }

            const formRechazar = document.getElementById('formRechazarPrestamo');

            if (formRechazar) {
                formRechazar.addEventListener(
                    'submit',
                    function (evento) {
                        evento.preventDefault();

                        Swal.fire({
                            title: '¿Rechazar la solicitud?',
                            text:
                                'El asociado podrá consultar el motivo '
                                + 'del rechazo.',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, rechazar',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                        }).then(function (resultado) {
                            if (!resultado.value) {
                                return;
                            }
                            console.log('hace');
                            const boton = document.getElementById('btnRechazarPrestamo');
                            boton.disabled = true;
                            boton.innerHTML = 'Rechazando...';
                            formRechazar.submit();
                        });
                    }
                );
            }

        });
    </script>
@endsection
