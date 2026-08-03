@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <style>
        .cambio-card {
            position: relative;
            height: 100%;
            padding: 20px;
            border: 1px solid #e1e7ed;
            border-radius: 12px;
            background: #fff;
            transition: .2s ease;
        }

        .cambio-card:hover {
            border-color: #1b6fc2;
            box-shadow: 0 8px 22px rgba(27, 111, 194, .10);
        }

        .cambio-check {
            position: absolute;
            top: 18px;
            right: 18px;
            transform: scale(1.25);
        }

        .cambio-titulo {
            margin-bottom: 15px;
            padding-right: 35px;
            color: #173a5e;
            font-weight: 700;
        }

        .dato-etiqueta {
            display: block;
            margin-bottom: 3px;
            color: #8795a3;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dato-actual,
        .dato-nuevo {
            display: block;
            min-height: 42px;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .dato-actual {
            color: #687786;
            background: #f1f3f5;
        }

        .dato-nuevo {
            color: #116640;
            background: #e1f5ec;
            font-weight: 600;
        }

        .documento-preview {
            width: 100%;
            max-height: 220px;
            border-radius: 8px;
            object-fit: contain;
            background: #f1f3f5;
        }
    </style>
@endsection

@section('content')

    @php
        $fechaActual = $data->fecha_nacimiento_actual ? \Carbon\Carbon::parse($data->fecha_nacimiento_actual)->format('d/m/Y') : 'No registrada';

        $fechaNueva = $data->fecha_nacimiento_nueva ? \Carbon\Carbon::parse($data->fecha_nacimiento_nueva)->format('d/m/Y') : null;

        $cambios = [
            'documento' => [
                'titulo' => 'Número de documento',
                'actual' => $data->documento_actual,
                'nuevo' => $data->documento_nuevo,
                'tipo' => 'texto',
            ],
            'nombre' => [
                'titulo' => 'Nombre',
                'actual' => $data->nombre_actual,
                'nuevo' => $data->nombre_nuevo,
                'tipo' => 'texto',
            ],
            'apellido' => [
                'titulo' => 'Apellido',
                'actual' => $data->apellido_actual,
                'nuevo' => $data->apellido_nuevo,
                'tipo' => 'texto',
            ],
            'fecha_nacimiento' => [
                'titulo' => 'Fecha de nacimiento',
                'actual' => $fechaActual,
                'nuevo' => $fechaNueva,
                'tipo' => 'texto',
            ],
            'institucion_municipal_id' => [
                'titulo' => 'Institución municipal',
                'actual' => $institucionActual?->descripcion
                    ?? 'No registrada',
                'nuevo' => $institucionNueva?->descripcion,
                'tipo' => 'texto',
            ],
            'email' => [
                'titulo' => 'Correo electrónico',
                'actual' => $data->email_actual,
                'nuevo' => $data->email_nuevo,
                'tipo' => 'texto',
            ],
            'celular' => [
                'titulo' => 'Número de celular',
                'actual' => $data->celular_actual
                    ?? 'No registrado',
                'nuevo' => $data->celular_nuevo,
                'tipo' => 'texto',
            ],
            'documento_frente' => [
                'titulo' => 'Documento de identidad - Frente',
                'actual' => $data->documento_frente_actual,
                'nuevo' => $data->documento_frente_nuevo,
                'tipo' => 'archivo',
            ],
            'documento_reverso' => [
                'titulo' => 'Documento de identidad - Reverso',
                'actual' => $data->documento_reverso_actual,
                'nuevo' => $data->documento_reverso_nuevo,
                'tipo' => 'archivo',
            ],
        ];

        /*
         * Mostrar solamente campos que realmente fueron solicitados.
         */
        $cambios = array_filter($cambios,fn ($cambio) => !is_null($cambio['nuevo']));

        $camposSeleccionados = old('campos_aprobados',array_keys($cambios));

        $puedeResolver = in_array((int) $data->estado_solicitud_id,[1, 2],true);
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h3 class="mb-1">
                            Solicitud de actualización de datos
                        </h3>

                        <p class="text-muted mb-0">
                            Solicitud N.º
                            {{ str_pad($data->numero, 5, '0', STR_PAD_LEFT) }}/{{ $data->anio }}
                        </p>
                    </div>

                    <span class="badge badge-{{ $data->estadoSolicitud->color ?? 'secondary' }}">
                        {{ $data->estadoSolicitud->descripcion ?? 'SIN ESTADO' }}
                    </span>
                </div>

                @include('varios.mensaje')

                <div class="alert alert-light border mb-4">
                    <strong>Motivo de la solicitud:</strong><br>
                    {{ $data->motivo }}
                </div>

                <form action="{{ route('actu_datos.store',$data->id) }}" method="POST" id="formAprobarActualizacion" >
                    @csrf
                    @if ($puedeResolver)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                Cambios solicitados
                            </h5>

                            <label class="mb-0">
                                <input
                                    type="checkbox"
                                    id="seleccionarTodos"
                                    checked
                                >
                                Aprobar todos
                            </label>
                        </div>
                    @endif

                    <div class="row">
                        @foreach ($cambios as $clave => $cambio)
                            <div class="col-lg-6 mb-4">
                                <div class="cambio-card">

                                    @if ($puedeResolver)
                                        <input type="checkbox" class="cambio-check campo-aprobacion" name="campos_aprobados[]" value="{{ $clave }}" @checked(in_array($clave,$camposSeleccionados))>
                                    @endif

                                    <div class="cambio-titulo">
                                        {{ $cambio['titulo'] }}
                                    </div>

                                    @if ($cambio['tipo'] === 'texto')
                                        <div class="mb-3">
                                            <span class="dato-etiqueta">
                                                Información actual
                                            </span>

                                            <span class="dato-actual">
                                                {{ $cambio['actual'] ?: 'No registrada' }}
                                            </span>
                                        </div>

                                        <div>
                                            <span class="dato-etiqueta">
                                                Información solicitada
                                            </span>

                                            <span class="dato-nuevo">
                                                {{ $cambio['nuevo'] }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <span class="dato-etiqueta">
                                                    Documento actual
                                                </span>

                                                @include(
                                                    'actualizacion_datos.partials.archivo',
                                                    ['ruta' => $cambio['actual']]
                                                )
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <span class="dato-etiqueta">
                                                    Documento nuevo
                                                </span>

                                                @include(
                                                    'actualizacion_datos.partials.archivo',
                                                    ['ruta' => $cambio['nuevo']]
                                                )
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($puedeResolver)
                        <div class="form-group">
                            <label for="observacion">
                                Observación administrativa
                            </label>

                            <textarea
                                id="observacion"
                                name="observacion"
                                class="form-control"
                                rows="3"
                                maxlength="500"
                            >{{ old('observacion') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button
                                type="submit"
                                class="btn btn-success"
                            >
                                <i class="fas fa-check mr-2"></i>
                                Aprobar cambios seleccionados
                            </button>
                        </div>
                    @endif
                </form>

            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const seleccionarTodos =
                document.getElementById('seleccionarTodos');

            const campos = document.querySelectorAll(
                '.campo-aprobacion'
            );

            if (!seleccionarTodos) {
                return;
            }

            seleccionarTodos.addEventListener('change', function () {
                campos.forEach(function (campo) {
                    campo.checked = seleccionarTodos.checked;
                });
            });

            campos.forEach(function (campo) {
                campo.addEventListener('change', function () {
                    seleccionarTodos.checked = Array.from(campos)
                        .every(function (item) {
                            return item.checked;
                        });
                });
            });
        });
    </script>
@endsection
