@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet">
    <style>
        .actualizacion-card {
            overflow: hidden;
            border: 1px solid #e2e8ef;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 8px 25px rgba(27, 58, 91, 0.08);
        }

        .actualizacion-header {
            padding: 25px 30px;
            color: #ffffff;
            background: linear-gradient(135deg, #8055b5, #5e3c8c);
        }

        .actualizacion-header h3 {
            margin-bottom: 5px;
            color: #ffffff;
            font-weight: 700;
        }

        .actualizacion-header p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.85);
        }

        .actualizacion-body {
            padding: 30px;
        }

        .seccion-titulo {
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e4eaf0;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .campo-cambio {
            height: 100%;
            padding: 18px;
            border: 1px solid #e2e8ee;
            border-radius: 12px;
            background-color: #fafcfd;
            transition: all 0.2s ease;
        }

        .campo-cambio.activo {
            border-color: #8055b5;
            background-color: #faf7fd;
            box-shadow: 0 4px 15px rgba(128, 85, 181, 0.10);
        }

        .campo-cambio-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
        }

        .campo-etiqueta {
            display: block;
            margin-bottom: 3px;
            color: #8996a3;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .campo-actual {
            color: #34495e;
            font-size: 14px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .check-modificar {
            display: inline-flex;
            align-items: center;
            margin: 0;
            color: #8055b5;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .check-modificar input {
            margin-right: 6px;
        }

        .campo-nuevo {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #d4dce4;
        }

        .documento-actual {
            width: 100%;
            max-width: 250px;
            height: 160px;
            border: 1px solid #dce4eb;
            border-radius: 10px;
            background-color: #f0f3f5;
            object-fit: contain;
        }

        .documento-vacio {
            height: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px dashed #cbd5df;
            border-radius: 10px;
            color: #8a98a6;
            background-color: #f7f9fb;
        }

        .documento-vacio i {
            margin-bottom: 8px;
            font-size: 30px;
        }

        .preview-documento {
            width: 100%;
            max-width: 250px;
            max-height: 200px;
            margin-top: 15px;
            border: 4px solid #ffffff;
            border-radius: 10px;
            object-fit: contain;
            box-shadow: 0 5px 16px rgba(0, 0, 0, 0.15);
        }

        .mensaje-seleccion {
            padding: 13px 16px;
            border-radius: 10px;
            color: #6c5a17;
            background-color: #fff7d8;
            font-size: 13px;
        }

        @media (max-width: 767.98px) {
            .actualizacion-header,
            .actualizacion-body {
                padding: 22px 18px;
            }

            .campo-cambio-header {
                flex-direction: column;
            }
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }

        .select2-container
        .select2-selection--single
        .select2-selection__rendered {
            line-height: 42px;
            padding-left: 13px;
        }

        .select2-container
        .select2-selection--single
        .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default.select2-container--disabled
        .select2-selection--single {
            cursor: not-allowed;
            background-color: #e9ecef;
        }
    </style>
@endsection

@section('content')

    @php
        $frenteActual = $persona->documento_frente
            ? Storage::disk('public')->url($persona->documento_frente)
            : null;

        $reversoActual = $persona->documento_reverso
            ? Storage::disk('public')->url($persona->documento_reverso)
            : null;

        /*
         * Ajustar esta relación al nombre real utilizado
         * en el modelo Asociado.
         */
        $institucionActual = $persona->asociado?->institucion;
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="actualizacion-card">

            <div class="actualizacion-header">
                <h3>
                    <i class="fa fa-pencil-square-o mr-2"></i>
                    Solicitud de actualización de datos
                </h3>

                <p>
                    Seleccione únicamente los datos que desea modificar.
                </p>
            </div>

            <div class="actualizacion-body">

                @include('varios.mensaje')

                <form
                    action="{{ route('actualizar_datos_post') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="formActualizacionDatos"
                >
                    @csrf

                    <div
                        id="mensajeSeleccion"
                        class="mensaje-seleccion mb-4"
                    >
                        <i class="fa fa-info-circle mr-1"></i>
                        Marque la opción <strong>Modificar</strong> en al
                        menos uno de los datos.
                    </div>

                    <div class="seccion-titulo">
                        <i class="fa fa-id-card-o mr-2"></i>
                        Datos personales
                    </div>

                    <div class="row">

                        {{-- DOCUMENTO --}}
                        <div class="col-lg-6 mb-4" style="display: none">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Documento actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $persona->documento }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_documento"
                                            value="1"
                                            data-target="grupoDocumento"
                                            @checked(old('cambiar_documento'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoDocumento"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="documento_nuevo">
                                        Nuevo documento
                                    </label>

                                    <input
                                        type="text"
                                        id="documento_nuevo"
                                        name="documento_nuevo"
                                        value="{{ old('documento_nuevo') }}"
                                        maxlength="20"
                                        class="form-control
                                            @error('documento_nuevo') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >

                                    @error('documento_nuevo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- NOMBRE --}}
                        <div class="col-lg-6 mb-4">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Nombre actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $persona->nombre }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_nombre"
                                            value="1"
                                            data-target="grupoNombre"
                                            @checked(old('cambiar_nombre'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoNombre"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="nombre_nuevo">
                                        Nuevo nombre
                                    </label>

                                    <input
                                        type="text"
                                        id="nombre_nuevo"
                                        name="nombre_nuevo"
                                        value="{{ old('nombre_nuevo') }}"
                                        maxlength="200"
                                        class="form-control
                                            @error('nombre_nuevo') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >

                                    @error('nombre_nuevo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- APELLIDO --}}
                        <div class="col-lg-6 mb-4">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Apellido actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $persona->apellido }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_apellido"
                                            value="1"
                                            data-target="grupoApellido"
                                            @checked(old('cambiar_apellido'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoApellido"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="apellido_nuevo">
                                        Nuevo apellido
                                    </label>

                                    <input
                                        type="text"
                                        id="apellido_nuevo"
                                        name="apellido_nuevo"
                                        value="{{ old('apellido_nuevo') }}"
                                        maxlength="200"
                                        class="form-control
                                            @error('apellido_nuevo') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >

                                    @error('apellido_nuevo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- FECHA DE NACIMIENTO --}}
                        <div class="col-lg-6 mb-4">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Fecha de nacimiento actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $persona->fecha_nacimiento
                                                ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y')
                                                : 'No registrada' }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_fecha_nacimiento"
                                            value="1"
                                            data-target="grupoFechaNacimiento"
                                            @checked(old('cambiar_fecha_nacimiento'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoFechaNacimiento"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="fecha_nacimiento_nueva">
                                        Nueva fecha de nacimiento
                                    </label>

                                    <input
                                        type="date"
                                        id="fecha_nacimiento_nueva"
                                        name="fecha_nacimiento_nueva"
                                        value="{{ old('fecha_nacimiento_nueva') }}"
                                        max="{{ now()->toDateString() }}"
                                        class="form-control
                                            @error('fecha_nacimiento_nueva') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >

                                    @error('fecha_nacimiento_nueva')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="seccion-titulo mt-2">
                        <i class="fa fa-building-o mr-2"></i>
                        Institución y contacto
                    </div>

                    <div class="row">

                        {{-- INSTITUCIÓN --}}
                        <div class="col-lg-6 mb-4">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Institución municipal actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $institucionActual?->descripcion
                                                ?? 'No registrada' }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_institucion"
                                            value="1"
                                            data-target="grupoInstitucion"
                                            @checked(old('cambiar_institucion'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoInstitucion"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="institucion_municipal_id_nueva">
                                        Nueva institución municipal
                                    </label>

                                    <select
                                        id="institucion_municipal_id_nueva"
                                        name="institucion_municipal_id_nueva"
                                        class="form-control select2-institucion
                                            @error('institucion_municipal_id_nueva') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >
                                        <option value=""></option>

                                        @foreach ($instituciones as $institucion)
                                            <option
                                                value="{{ $institucion->id }}"
                                                @selected(
                                                    old('institucion_municipal_id_nueva')
                                                    == $institucion->id
                                                )
                                            >
                                                {{ $institucion->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('institucion_municipal_id_nueva')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- EMAIL --}}
                        <div class="col-lg-6 mb-4">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Correo electrónico actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $persona->email ?: 'No registrado' }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_email"
                                            value="1"
                                            data-target="grupoEmail"
                                            @checked(old('cambiar_email'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoEmail"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="email_nuevo">
                                        Nuevo correo electrónico
                                    </label>

                                    <input
                                        type="email"
                                        id="email_nuevo"
                                        name="email_nuevo"
                                        value="{{ old('email_nuevo') }}"
                                        maxlength="250"
                                        class="form-control
                                            @error('email_nuevo') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >

                                    @error('email_nuevo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- CELULAR --}}
                        <div class="col-lg-6 mb-4">
                            <div class="campo-cambio">
                                <div class="campo-cambio-header">
                                    <div>
                                        <span class="campo-etiqueta">
                                            Celular actual
                                        </span>

                                        <span class="campo-actual">
                                            {{ $persona->celular ?: 'No registrado' }}
                                        </span>
                                    </div>

                                    <label class="check-modificar">
                                        <input
                                            type="checkbox"
                                            name="cambiar_celular"
                                            value="1"
                                            data-target="grupoCelular"
                                            @checked(old('cambiar_celular'))
                                        >
                                        Modificar
                                    </label>
                                </div>

                                <div
                                    id="grupoCelular"
                                    class="campo-nuevo d-none"
                                >
                                    <label for="celular_nuevo">
                                        Nuevo número de celular
                                    </label>

                                    <input
                                        type="text"
                                        id="celular_nuevo"
                                        name="celular_nuevo"
                                        value="{{ old('celular_nuevo') }}"
                                        maxlength="30"
                                        class="form-control
                                            @error('celular_nuevo') is-invalid @enderror"
                                        data-campo-input
                                        disabled
                                    >

                                    @error('celular_nuevo')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- DOCUMENTOS --}}
                    <div class="seccion-titulo mt-2">
                        <i class="fa fa-picture-o mr-2"></i>
                        Fotografías de la cédula
                    </div>

                    <div class="campo-cambio mb-4">
                        <div class="campo-cambio-header">
                            <div>
                                <span class="campo-etiqueta">
                                    Documento frente y reverso
                                </span>

                                <span class="campo-actual">
                                    Puede solicitar la actualización de las
                                    fotografías de su cédula.
                                </span>
                            </div>

                            <label class="check-modificar">
                                <input
                                    type="checkbox"
                                    name="cambiar_documentos"
                                    value="1"
                                    data-target="grupoDocumentos"
                                    @checked(old('cambiar_documentos'))
                                >
                                Modificar
                            </label>
                        </div>

                        <div
                            id="grupoDocumentos"
                            class="campo-nuevo d-none"
                        >
                            <div class="row">

                                {{-- FRENTE --}}
                                <div class="col-md-6 mb-4">
                                    <label>Documento frente actual</label>

                                    <div class="text-center">
                                        @if ($frenteActual)
                                            <img
                                                src="{{ $frenteActual }}"
                                                class="documento-actual"
                                                alt="Documento frente actual"
                                            >
                                        @else
                                            <div class="documento-vacio">
                                                <i class="fa fa-id-card-o"></i>
                                                Sin imagen registrada
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="documento_frente_nuevo">
                                            Nueva fotografía del frente
                                        </label>

                                        <input
                                            type="file"
                                            id="documento_frente_nuevo"
                                            name="documento_frente_nuevo"
                                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                            class="form-control
                                                @error('documento_frente_nuevo') is-invalid @enderror"
                                            data-campo-input
                                            data-preview="previewFrente"
                                            disabled
                                        >

                                        @error('documento_frente_nuevo')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <img
                                            src=""
                                            id="previewFrente"
                                            class="preview-documento d-none"
                                            alt="Vista previa del frente"
                                        >
                                    </div>
                                </div>

                                {{-- REVERSO --}}
                                <div class="col-md-6 mb-4">
                                    <label>Documento reverso actual</label>

                                    <div class="text-center">
                                        @if ($reversoActual)
                                            <img
                                                src="{{ $reversoActual }}"
                                                class="documento-actual"
                                                alt="Documento reverso actual"
                                            >
                                        @else
                                            <div class="documento-vacio">
                                                <i class="fa fa-id-card-o"></i>
                                                Sin imagen registrada
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="documento_reverso_nuevo">
                                            Nueva fotografía del reverso
                                        </label>

                                        <input
                                            type="file"
                                            id="documento_reverso_nuevo"
                                            name="documento_reverso_nuevo"
                                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                            class="form-control
                                                @error('documento_reverso_nuevo') is-invalid @enderror"
                                            data-campo-input
                                            data-preview="previewReverso"
                                            disabled
                                        >

                                        @error('documento_reverso_nuevo')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <img
                                            src=""
                                            id="previewReverso"
                                            class="preview-documento d-none"
                                            alt="Vista previa del reverso"
                                        >
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- MOTIVO --}}
                    <div class="seccion-titulo">
                        <i class="fa fa-commenting-o mr-2"></i>
                        Motivo de la actualización
                    </div>

                    <div class="form-group">
                        <label for="motivo">
                            Explique brevemente por qué solicita la actualización
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="motivo"
                            name="motivo"
                            rows="5"
                            maxlength="2000"
                            required
                            class="form-control
                                @error('motivo') is-invalid @enderror"
                            placeholder="Describa el motivo de la actualización..."
                        >{{ old('motivo') }}</textarea>

                        @error('motivo')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div
                        id="errorFormulario"
                        class="alert alert-danger d-none"
                    ></div>

                    <div class="d-flex justify-content-between mt-4">
                        <a
                            href="{{ route('nueva_solicitud') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fa fa-arrow-left mr-1"></i>
                            Volver
                        </a>

                        <button
                            type="submit"
                            id="btnEnviarSolicitud"
                            class="btn btn-primary"
                        >
                            <i class="fa fa-paper-plane mr-1"></i>
                            Presentar solicitud
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const checkboxes =
                document.querySelectorAll('[data-target]');

            const formulario =
                document.getElementById('formActualizacionDatos');

            const errorFormulario =
                document.getElementById('errorFormulario');

            const botonEnviar =
                document.getElementById('btnEnviarSolicitud');

            function cambiarEstadoCampo(checkbox) {
                const grupo = document.getElementById(
                    checkbox.dataset.target
                );

                const tarjeta = checkbox.closest('.campo-cambio');

                const controles = grupo.querySelectorAll(
                    '[data-campo-input]'
                );

                if (checkbox.checked) {
                    grupo.classList.remove('d-none');
                    tarjeta.classList.add('activo');

                    controles.forEach(function (control) {
                        control.disabled = false;
                    });
                } else {
                    grupo.classList.add('d-none');
                    tarjeta.classList.remove('activo');

                    controles.forEach(function (control) {
                        control.disabled = true;
                    });
                }
            }

            checkboxes.forEach(function (checkbox) {

                cambiarEstadoCampo(checkbox);

                checkbox.addEventListener('change', function () {
                    cambiarEstadoCampo(this);
                });
            });

            /*
             * Vista previa de las fotografías.
             */
            const archivos = document.querySelectorAll(
                '[data-preview]'
            );

            archivos.forEach(function (input) {
                input.addEventListener('change', function () {

                    const preview = document.getElementById(
                        this.dataset.preview
                    );

                    preview.classList.add('d-none');
                    preview.src = '';

                    const archivo = this.files[0];

                    if (!archivo) {
                        return;
                    }

                    const permitidos = [
                        'image/jpeg',
                        'image/png'
                    ];

                    if (!permitidos.includes(archivo.type)) {
                        this.value = '';

                        alert(
                            'La fotografía debe ser JPG, JPEG o PNG.'
                        );

                        return;
                    }

                    if (archivo.size > 50 * 1024 * 1024) {
                        this.value = '';

                        alert(
                            'La fotografía no debe superar los 50 MB.'
                        );

                        return;
                    }

                    const lector = new FileReader();

                    lector.onload = function (evento) {
                        preview.src = evento.target.result;
                        preview.classList.remove('d-none');
                    };

                    lector.readAsDataURL(archivo);
                });
            });

            formulario.addEventListener('submit', function (evento) {

                errorFormulario.classList.add('d-none');
                errorFormulario.textContent = '';

                const seleccionados =
                    document.querySelectorAll(
                        '[data-target]:checked'
                    );

                if (seleccionados.length === 0) {
                    evento.preventDefault();

                    errorFormulario.textContent =
                        'Debe seleccionar al menos un dato para actualizar.';

                    errorFormulario.classList.remove('d-none');
                    return;
                }

                botonEnviar.disabled = true;

                botonEnviar.innerHTML =
                    '<i class="fa fa-spinner fa-spin mr-1"></i> Enviando...';
            });
        });
    </script>
@endsection
