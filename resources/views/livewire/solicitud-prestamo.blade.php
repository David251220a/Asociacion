<div>

    <style>
        .prestamo-encabezado {
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e7edf3;
        }

        .prestamo-titulo {
            margin-bottom: 5px;
            color: #193b5c;
            font-weight: 700;
        }

        .prestamo-subtitulo {
            margin-bottom: 0;
            color: #7d8b99;
        }

        .prestamo-persona {
            margin-bottom: 28px;
            padding: 20px 22px;
            border: 1px solid #e2e9ef;
            border-radius: 12px;
            background: #f8fafc;
        }

        .prestamo-persona-etiqueta {
            display: block;
            margin-bottom: 4px;
            color: #8492a0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .prestamo-persona-valor {
            display: block;
            color: #29445c;
            font-size: 15px;
            font-weight: 600;
        }

        .prestamo-seccion-titulo {
            margin-bottom: 6px;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .prestamo-seccion-ayuda {
            margin-bottom: 16px;
            color: #7e8b98;
            font-size: 13px;
        }

        .opcion-prestamo {
            display: block;
            width: 100%;
            margin-bottom: 14px;
            cursor: pointer;
        }

        .opcion-prestamo input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .opcion-prestamo-contenido {
            display: block;
            padding: 17px 12px;
            border: 1px solid #dde5ed;
            border-radius: 11px;
            color: #33485c;
            background: #ffffff;
            text-align: center;
            transition: all .2s ease;
        }

        .opcion-prestamo:hover .opcion-prestamo-contenido {
            border-color: #1b6fc2;
            background: #f5f9fd;
        }

        .opcion-prestamo input:checked + .opcion-prestamo-contenido {
            border-color: #1b6fc2;
            color: #ffffff;
            background: #1b6fc2;
            box-shadow: 0 8px 20px rgba(27, 111, 194, .20);
        }

        .opcion-prestamo-monto {
            display: block;
            font-size: 17px;
            font-weight: 700;
        }

        .opcion-prestamo-detalle {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            opacity: .85;
        }

        .prestamo-resumen {
            padding: 24px;
            border: 1px solid #dce7ef;
            border-radius: 14px;
            background: linear-gradient(
                135deg,
                #ffffff 0%,
                #f1f7fc 100%
            );
        }

        .prestamo-resumen-titulo {
            margin-bottom: 20px;
            color: #173a5e;
            font-size: 17px;
            font-weight: 700;
        }

        .prestamo-resumen-fila {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 13px;
            color: #647485;
            font-size: 14px;
        }

        .prestamo-resumen-fila strong {
            color: #29445c;
            text-align: right;
        }

        .prestamo-resumen-total {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #dce5ec;
        }

        .prestamo-resumen-total strong {
            color: #168552;
            font-size: 22px;
        }

        .prestamo-cuota-destacada {
            margin-top: 20px;
            padding: 18px;
            border-radius: 12px;
            background: #e4f0fb;
            text-align: center;
        }

        .prestamo-cuota-destacada span {
            display: block;
            margin-bottom: 5px;
            color: #51708d;
            font-size: 13px;
        }

        .prestamo-cuota-destacada strong {
            color: #1b6fc2;
            font-size: 25px;
        }

        .prestamo-resumen-vacio {
            padding: 30px 10px;
            color: #8a98a5;
            text-align: center;
        }

        @media (max-width: 767.98px) {
            .prestamo-resumen {
                margin-top: 22px;
            }

            .opcion-prestamo-monto {
                font-size: 15px;
            }
        }
    </style>

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                <div class="prestamo-encabezado">
                    <h3 class="prestamo-titulo">
                        Solicitud de préstamo
                    </h3>

                    <p class="prestamo-subtitulo">
                        Seleccioná el monto y la cantidad de cuotas para
                        conocer las condiciones de tu préstamo.
                    </p>
                </div>

                @include('varios.mensaje')

                <div class="prestamo-persona">
                    <div class="row">

                        <div class="col-md-4 mb-3 mb-md-0">
                            <span class="prestamo-persona-etiqueta">
                                Asociado
                            </span>

                            <span class="prestamo-persona-valor">
                                {{ $persona->nombre }}
                                {{ $persona->apellido }}
                            </span>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <span class="prestamo-persona-etiqueta">
                                Documento
                            </span>

                            <span class="prestamo-persona-valor">
                                {{ $persona->documento }}
                            </span>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <span class="prestamo-persona-etiqueta">
                                Correo electrónico
                            </span>

                            <span class="prestamo-persona-valor">
                                {{ $persona->email ?: 'No registrado' }}
                            </span>
                        </div>

                        <div class="col-md-2">
                            <span class="prestamo-persona-etiqueta">
                                Celular
                            </span>

                            <span class="prestamo-persona-valor">
                                {{ $persona->celular ?: 'No registrado' }}
                            </span>
                        </div>

                    </div>
                </div>

                @error('prestamo')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ $message }}
                    </div>
                @enderror

                <div class="row">

                    <div class="col-lg-8">

                        <div class="mb-4">
                            <h5 class="prestamo-seccion-titulo">
                                ¿Qué monto querés solicitar?
                            </h5>

                            <p class="prestamo-seccion-ayuda">
                                Seleccioná uno de los montos habilitados.
                            </p>

                            <div class="row">
                                @forelse ($montosDisponibles as $monto)
                                    <div class="col-sm-6 col-xl-4">
                                        <label class="opcion-prestamo">

                                            <input type="radio" wire:model.live="montoSeleccionado" value="{{ $monto }}">

                                            <span class="opcion-prestamo-contenido">
                                                <span class="opcion-prestamo-monto">
                                                    G.
                                                    {{ number_format($monto, 0, ',', '.') }}
                                                </span>

                                                <span class="opcion-prestamo-detalle">
                                                    Monto solicitado
                                                </span>
                                            </span>

                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            Actualmente no existen montos
                                            habilitados para solicitar.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="prestamo-seccion-titulo">
                                ¿En cuántas cuotas querés pagar?
                            </h5>

                            <p class="prestamo-seccion-ayuda">
                                Elegí la cantidad de cuotas disponibles.
                            </p>

                            <div class="row">
                                @forelse ($plazosDisponibles as $plazo)
                                    @php
                                        $tasaPlazo = $plazo === 1 ? $config->tasa_cuota_unica : $config->tasa_cuota_mensual;
                                    @endphp

                                    <div class="col-sm-6">
                                        <label class="opcion-prestamo">

                                            <input type="radio" wire:model.live="cantidadCuotas" value="{{ $plazo }}">

                                            <span class="opcion-prestamo-contenido">
                                                <span class="opcion-prestamo-monto">
                                                    {{ $plazo }}
                                                    {{ $plazo === 1 ? 'cuota' : 'cuotas' }}
                                                </span>

                                                <span class="opcion-prestamo-detalle">
                                                    Tasa: {{ number_format($tasaPlazo, 2, ',', '.') }} %
                                                    {{ $plazo > 1 ? 'por cuota' : '' }}
                                                </span>
                                            </span>

                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            Actualmente no existen plazos
                                            habilitados para solicitar.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if (!empty($detalleCuotas))
                            <div class="mt-4">
                                <h5 class="prestamo-seccion-titulo">
                                    Detalle estimado de cuotas
                                </h5>

                                <p class="prestamo-seccion-ayuda">
                                    Las fechas podrán ajustarse al momento
                                    de aprobar la solicitud.
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Cuota</th>

                                                <th>Vencimiento</th>

                                                <th class="text-right">
                                                    Capital
                                                </th>

                                                <th class="text-right">
                                                    Interés
                                                </th>

                                                <th class="text-right">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($detalleCuotas as $cuota)
                                                <tr>
                                                    <td>
                                                        {{ $cuota['numero_cuota'] }}
                                                    </td>

                                                    <td>
                                                        {{ $cuota['fecha_vencimiento'] }}
                                                    </td>

                                                    <td class="text-right">
                                                        G.
                                                        {{ number_format($cuota['monto_capital'], 0, ',', '.' ) }}
                                                    </td>

                                                    <td class="text-right">
                                                        G.
                                                        {{ number_format($cuota['monto_interes'], 0, ',', '.') }}
                                                    </td>

                                                    <td class="text-right">
                                                        <strong>
                                                            G.
                                                            {{ number_format($cuota['monto_total'], 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="col-lg-4">

                        <div class="prestamo-resumen">
                            <h5 class="prestamo-resumen-titulo">
                                Resumen del préstamo
                            </h5>

                            @if ($montoSeleccionado > 0 && $cantidadCuotas > 0)
                                <div class="prestamo-resumen-fila">
                                    <span>Monto solicitado</span>

                                    <strong>
                                        G.
                                        {{ number_format( $montoSeleccionado, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="prestamo-resumen-fila">
                                    <span>Cantidad de cuotas</span>

                                    <strong>
                                        {{ $cantidadCuotas }}
                                    </strong>
                                </div>

                                <div class="prestamo-resumen-fila">
                                    <span>
                                        {{ $cantidadCuotas === 1 ? 'Tasa única' : 'Tasa por Cuota' }}
                                    </span>

                                    <strong>
                                        {{ number_format( $tasaAplicada, 2, ',', '.') }} %
                                    </strong>
                                </div>

                                <div class="prestamo-resumen-fila">
                                    <span>Interés total</span>

                                    <strong>
                                        G.{{ number_format( $montoInteres, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="prestamo-resumen-fila prestamo-resumen-total">
                                    <span>
                                        Total a devolver
                                    </span>

                                    <strong>
                                        G.
                                        {{ number_format($montoTotal, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="prestamo-cuota-destacada">
                                    <span>
                                        {{ $cantidadCuotas === 1  ? 'Pagarás una cuota de' : 'Valor aproximado de cada cuota' }}
                                    </span>

                                    <strong>
                                        G. {{ number_format( $montoCuota, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <button type="button" wire:click="guardar" wire:loading.attr="disabled"  wire:target="guardar" class="btn btn-primary btn-block mt-4">
                                    <span wire:loading.remove wire:target="guardar">
                                        Solicitar préstamo
                                    </span>

                                    <span wire:loading wire:target="guardar">
                                        Enviando solicitud...
                                    </span>
                                </button>
                            @else
                                <div class="prestamo-resumen-vacio">
                                    Seleccioná un monto y la cantidad
                                    de cuotas para ver el cálculo.
                                </div>
                            @endif
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>
