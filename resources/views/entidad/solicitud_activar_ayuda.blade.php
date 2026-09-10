@extends('layouts.admin')

@section('styles')
    <style>
        .config-solicitud {
            overflow: hidden;
            border: 1px solid #e2e8ef;
            border-radius: 16px;
            background-color: #ffffff;
            box-shadow: 0 9px 30px rgba(28, 52, 77, 0.08);
        }

        .config-cabecera {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 27px 32px;
            border-bottom: 1px solid #e7edf2;
            background: linear-gradient(135deg, #f6f9fc 0%, #ffffff 100%);
        }

        .config-cabecera h3 {
            margin-bottom: 5px;
            color: #193b5c;
            font-weight: 700;
        }

        .config-cabecera p {
            margin-bottom: 0;
            color: #748391;
        }

        .estado-indicador {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 24px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .estado-indicador::before {
            width: 8px;
            height: 8px;
            margin-right: 8px;
            border-radius: 50%;
            content: '';
        }

        .estado-indicador.activo {
            color: #137047;
            background-color: #dff5e9;
        }

        .estado-indicador.activo::before {
            background-color: #168552;
        }

        .estado-indicador.inactivo {
            color: #a24646;
            background-color: #fde8e8;
        }

        .estado-indicador.inactivo::before {
            background-color: #dc3545;
        }

        .config-contenido {
            padding: 30px 32px;
        }

        .config-seccion {
            height: 100%;
            padding: 23px;
            border: 1px solid #e4eaf0;
            border-radius: 13px;
            background-color: #fbfcfd;
        }

        .config-seccion-titulo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e5eaf0;
        }

        .config-seccion-icono {
            display: flex;
            width: 39px;
            height: 39px;
            min-width: 39px;
            align-items: center;
            justify-content: center;
            margin-right: 11px;
            border-radius: 11px;
            color: #1b6fc2;
            background-color: #e5f0fb;
        }

        .config-seccion-titulo h5 {
            margin-bottom: 2px;
            color: #294760;
            font-size: 15px;
            font-weight: 700;
        }

        .config-seccion-titulo small {
            color: #8794a0;
        }

        .config-seccion label {
            color: #3d5367;
            font-size: 13px;
            font-weight: 700;
        }

        .config-seccion .form-control {
            min-height: 43px;
            border-color: #d5dee7;
            border-radius: 8px;
        }

        .campo-ayuda {
            display: block;
            margin-top: 7px;
            color: #8995a1;
            font-size: 11px;
            line-height: 1.45;
        }

        .interruptor-fila {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px;
            border: 1px solid #dfe6ed;
            border-radius: 11px;
            background-color: #ffffff;
        }

        .interruptor-fila + .interruptor-fila {
            margin-top: 14px;
        }

        .interruptor-fila strong {
            display: block;
            margin-bottom: 3px;
            color: #344d64;
            font-size: 14px;
        }

        .interruptor-fila small {
            display: block;
            max-width: 470px;
            padding-right: 20px;
            color: #85929e;
            line-height: 1.45;
        }

        .interruptor {
            position: relative;
            display: inline-block;
            width: 52px;
            min-width: 52px;
            height: 28px;
            margin-bottom: 0;
        }

        .interruptor input {
            width: 0;
            height: 0;
            opacity: 0;
        }

        .interruptor-control {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            border-radius: 28px;
            background-color: #bec8d1;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .interruptor-control::before {
            position: absolute;
            bottom: 4px;
            left: 4px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.18);
            content: '';
            transition: 0.2s ease;
        }

        .interruptor input:checked + .interruptor-control {
            background-color: #168552;
        }

        .interruptor input:checked + .interruptor-control::before {
            transform: translateX(24px);
        }

        .limite-anual-contenedor {
            margin-top: 16px;
            padding: 18px;
            border: 1px solid #dce8f2;
            border-radius: 11px;
            background-color: #f5f9fd;
        }

        .config-acciones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 20px 32px;
            border-top: 1px solid #e7edf2;
            background-color: #fbfcfd;
        }

        @media (max-width: 767.98px) {
            .config-cabecera {
                align-items: flex-start;
                flex-direction: column;
                padding: 24px 20px;
            }

            .estado-indicador {
                margin-top: 15px;
            }

            .config-contenido,
            .config-acciones {
                padding-right: 20px;
                padding-left: 20px;
            }

            .interruptor-fila {
                align-items: flex-start;
            }

            .config-acciones {
                flex-direction: column-reverse;
            }

            .config-acciones .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 layout-spacing">
        <div class="config-solicitud">

            <div class="config-cabecera">
                <div>
                    <h3>Configuración de solicitudes</h3>
                    <p>
                        Administrá la disponibilidad, los montos, las tasas y los límites permitidos.
                    </p>
                </div>

                <span
                    id="estadoIndicador"
                    class="estado-indicador {{ (int) old('activo', $data->activo) === 1 ? 'activo' : 'inactivo' }}"
                >
                    {{ (int) old('activo', $data->activo) === 1 ? 'Solicitudes habilitadas' : 'Solicitudes inhabilitadas' }}
                </span>
            </div>

            @include('varios.mensaje')

            <form action="{{ route('entidad_soli.solicitud_ayuda_social_post', $data->id) }}" method="POST" id="formConfigSolicitud">
                @csrf

                <div class="config-contenido">
                    <div class="row">

                        <div class="col-lg-12 mb-4">
                            <div class="config-seccion">
                                <div class="config-seccion-titulo">
                                    <div class="config-seccion-icono">
                                        <i class="fas fa-sliders-h"></i>
                                    </div>

                                    <div>
                                        <h5>Configuración general</h5>
                                        <small>Nombre y disponibilidad de la solicitud.</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>

                                    <input
                                        type="text"
                                        id="descripcion"
                                        name="descripcion"
                                        maxlength="255"
                                        value="{{ old('descripcion', $data->descripcion) }}"
                                        class="form-control @error('descripcion') is-invalid @enderror"
                                        required
                                    >

                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="interruptor-fila">
                                    <div>
                                        <strong>Habilitar recepción de solicitudes</strong>
                                        <small>
                                            Cuando esté desactivado, los asociados no podrán crear nuevas solicitudes.
                                        </small>
                                    </div>

                                    <input type="hidden" name="activo" value="0">

                                    <label class="interruptor" for="activo">
                                        <input
                                            type="checkbox"
                                            id="activo"
                                            name="activo"
                                            value="1"
                                            @checked((int) old('activo', $data->activo) === 1)
                                        >
                                        <span class="interruptor-control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="config-seccion">
                                <div class="config-seccion-titulo">
                                    <div class="config-seccion-icono">
                                        <i class="fas fa-percentage"></i>
                                    </div>

                                    <div>
                                        <h5>Tasas aplicables</h5>
                                        <small>Porcentajes utilizados para calcular el préstamo.</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tasa_cuota_unica">Tasa cuota única</label>
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    id="tasa_cuota_unica"
                                                    name="tasa_cuota_unica"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ old('tasa_cuota_unica', $data->tasa_cuota_unica) }}"
                                                    class="form-control @error('tasa_cuota_unica') is-invalid @enderror"
                                                    required
                                                >
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('tasa_cuota_unica')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tasa_cuota_mensual">Tasa en varias cuotas</label>
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    id="tasa_cuota_mensual"
                                                    name="tasa_cuota_mensual"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ old('tasa_cuota_mensual', $data->tasa_cuota_mensual) }}"
                                                    class="form-control @error('tasa_cuota_mensual') is-invalid @enderror"
                                                    required
                                                >
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('tasa_cuota_mensual')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label for="tasa_mora">Tasa de mora</label>
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    id="tasa_mora"
                                                    name="tasa_mora"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ old('tasa_mora', $data->tasa_mora) }}"
                                                    class="form-control @error('tasa_mora') is-invalid @enderror"
                                                    required
                                                >
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('tasa_mora')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="config-seccion">
                                <div class="config-seccion-titulo">
                                    <div class="config-seccion-icono">
                                        <i class="fas fa-coins"></i>
                                    </div>

                                    <div>
                                        <h5>Montos permitidos</h5>
                                        <small>Rango habilitado para las nuevas solicitudes.</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="monto_minimo">Monto mínimo</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">G.</span>
                                                </div>
                                                <input
                                                    type="number"
                                                    id="monto_minimo"
                                                    name="monto_minimo"
                                                    min="0"
                                                    step="100000"
                                                    value="{{ old('monto_minimo', (int) $data->monto_minimo) }}"
                                                    class="form-control @error('monto_minimo') is-invalid @enderror"
                                                    required
                                                >
                                            </div>
                                            @error('monto_minimo')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="monto_maximo">Monto máximo</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">G.</span>
                                                </div>
                                                <input
                                                    type="number"
                                                    id="monto_maximo"
                                                    name="monto_maximo"
                                                    min="0"
                                                    step="100000"
                                                    value="{{ old('monto_maximo', (int) $data->monto_maximo) }}"
                                                    class="form-control @error('monto_maximo') is-invalid @enderror"
                                                    required
                                                >
                                            </div>
                                            @error('monto_maximo')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <span class="campo-ayuda">
                                            Los montos disponibles se generan dentro de este rango, normalmente en incrementos de G. 100.000.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="config-seccion">
                                <div class="config-seccion-titulo">
                                    <div class="config-seccion-icono">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>

                                    <div>
                                        <h5>Plazos permitidos</h5>
                                        <small>Cantidad mínima y máxima de cuotas disponibles.</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label for="plazo_minimo">Plazo mínimo</label>
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    id="plazo_minimo"
                                                    name="plazo_minimo"
                                                    min="1"
                                                    max="255"
                                                    value="{{ old('plazo_minimo', $data->plazo_minimo) }}"
                                                    class="form-control @error('plazo_minimo') is-invalid @enderror"
                                                    required
                                                >
                                                <div class="input-group-append">
                                                    <span class="input-group-text">cuotas</span>
                                                </div>
                                            </div>
                                            @error('plazo_minimo')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="plazo_maximo">Plazo máximo</label>
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    id="plazo_maximo"
                                                    name="plazo_maximo"
                                                    min="1"
                                                    max="255"
                                                    value="{{ old('plazo_maximo', $data->plazo_maximo) }}"
                                                    class="form-control @error('plazo_maximo') is-invalid @enderror"
                                                    required
                                                >
                                                <div class="input-group-append">
                                                    <span class="input-group-text">cuotas</span>
                                                </div>
                                            </div>
                                            @error('plazo_maximo')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="config-seccion">
                                <div class="config-seccion-titulo">
                                    <div class="config-seccion-icono">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>

                                    <div>
                                        <h5>Límite de solicitudes</h5>
                                        <small>Control de la cantidad permitida por asociado.</small>
                                    </div>
                                </div>

                                <div class="interruptor-fila">
                                    <div>
                                        <strong>Aplicar límite anual</strong>
                                        <small>
                                            Activá esta opción para restringir la cantidad de solicitudes que puede realizar cada asociado por año.
                                        </small>
                                    </div>

                                    <input type="hidden" name="limite_solicitud" value="0">

                                    <label class="interruptor" for="limite_solicitud">
                                        <input
                                            type="checkbox"
                                            id="limite_solicitud"
                                            name="limite_solicitud"
                                            value="1"
                                            @checked((int) old('limite_solicitud', $data->limite_solicitud) === 1)
                                        >
                                        <span class="interruptor-control"></span>
                                    </label>
                                </div>

                                <div
                                    id="limiteAnualContenedor"
                                    class="limite-anual-contenedor {{ (int) old('limite_solicitud', $data->limite_solicitud) === 1 ? '' : 'd-none' }}"
                                >
                                    <div class="form-group mb-0">
                                        <label for="limite_solicitud_anual">
                                            Cantidad máxima por año
                                        </label>

                                        <input
                                            type="number"
                                            id="limite_solicitud_anual"
                                            name="limite_solicitud_anual"
                                            min="1"
                                            max="255"
                                            value="{{ old('limite_solicitud_anual', $data->limite_solicitud_anual) }}"
                                            class="form-control @error('limite_solicitud_anual') is-invalid @enderror"
                                            {{ (int) old('limite_solicitud', $data->limite_solicitud) === 1 ? '' : 'disabled' }}
                                        >

                                        @error('limite_solicitud_anual')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <span class="campo-ayuda">
                                            Esta cantidad se controla de manera independiente para cada año.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="config-acciones">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Guardar configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const activo = document.getElementById('activo');
            const estadoIndicador = document.getElementById('estadoIndicador');
            const limitar = document.getElementById('limite_solicitud');
            const limiteContenedor = document.getElementById('limiteAnualContenedor');
            const limiteAnual = document.getElementById('limite_solicitud_anual');

            function actualizarEstado() {
                estadoIndicador.classList.remove('activo', 'inactivo');

                if (activo.checked) {
                    estadoIndicador.classList.add('activo');
                    estadoIndicador.textContent = 'Solicitudes habilitadas';
                } else {
                    estadoIndicador.classList.add('inactivo');
                    estadoIndicador.textContent = 'Solicitudes inhabilitadas';
                }
            }

            function actualizarLimite() {
                if (limitar.checked) {
                    limiteContenedor.classList.remove('d-none');
                    limiteAnual.disabled = false;
                    limiteAnual.required = true;

                    if (Number(limiteAnual.value) < 1) {
                        limiteAnual.value = 1;
                    }
                } else {
                    limiteContenedor.classList.add('d-none');
                    limiteAnual.disabled = true;
                    limiteAnual.required = false;
                }
            }

            activo.addEventListener('change', actualizarEstado);
            limitar.addEventListener('change', actualizarLimite);

            actualizarEstado();
            actualizarLimite();
        });
    </script>
@endsection
