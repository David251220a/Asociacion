@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/alert.css') }}">

    <style>
        .solicitud-card {
            overflow: hidden;
            border: 1px solid #e2e8ef;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 8px 25px rgba(27, 58, 91, 0.08);
        }

        .solicitud-header {
            padding: 24px 30px;
            color: #ffffff;
            background: linear-gradient(135deg, #168552, #176b49);
        }

        .solicitud-header h3 {
            margin-bottom: 5px;
            color: #ffffff;
            font-weight: 700;
        }

        .solicitud-header p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.85);
        }

        .solicitud-body {
            padding: 30px;
        }

        .seccion-titulo {
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e7ecf1;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .persona-dato {
            height: 100%;
            padding: 13px 15px;
            border: 1px solid #e3e9ef;
            border-radius: 10px;
            background-color: #f8fafc;
        }

        .persona-dato small {
            display: block;
            margin-bottom: 3px;
            color: #8795a3;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .persona-dato span {
            color: #34495e;
            font-size: 14px;
            font-weight: 600;
        }

        .archivo-zona {
            padding: 25px;
            border: 2px dashed #c9d5df;
            border-radius: 12px;
            background-color: #f8fafc;
            text-align: center;
            transition: all 0.2s ease;
        }

        .archivo-zona:hover {
            border-color: #168552;
            background-color: #f1faf6;
        }

        .archivo-zona i {
            display: block;
            margin-bottom: 10px;
            color: #168552;
            font-size: 34px;
        }

        .archivo-preview {
            max-width: 280px;
            max-height: 230px;
            margin: 18px auto 0;
            border: 4px solid #ffffff;
            border-radius: 10px;
            object-fit: contain;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.14);
        }

        .pdf-preview {
            margin-top: 18px;
            padding: 14px;
            border-radius: 10px;
            color: #a52a2a;
            background-color: #fff0f0;
            font-weight: 600;
        }

        .campo-obligatorio {
            color: #dc3545;
        }

        @media (max-width: 767.98px) {
            .solicitud-header,
            .solicitud-body {
                padding: 22px 18px;
            }
        }
    </style>
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="solicitud-card">

            <div class="solicitud-header">
                <h3>
                    <i class="fa fa-heart mr-2"></i>
                    Solicitud de ayuda social
                </h3>

                <p>
                    Complete los datos requeridos para presentar su solicitud.
                </p>
            </div>

            <div class="solicitud-body">

                @include('varios.mensaje')

                <form action="{{ route('ayuda_social_store') }}" method="POST" enctype="multipart/form-data" id="formAyudaSocial">
                    @csrf

                    {{-- DATOS DE LA PERSONA --}}
                    <div class="seccion-titulo">
                        <i class="fa fa-user mr-2"></i>
                        Datos del solicitante
                    </div>

                    <div class="row mb-4">

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="persona-dato">
                                <small>Documento</small>
                                <span>
                                    {{ $persona->documento ?: 'No registrado' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-5 col-md-6 mb-3">
                            <div class="persona-dato">
                                <small>Nombre y apellido</small>
                                <span>
                                    {{ $persona->nombre }}
                                    {{ $persona->apellido }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="persona-dato">
                                <small>Fecha de nacimiento</small>
                                <span>
                                    {{ $persona->fecha_nacimiento
                                        ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y')
                                        : 'No registrada' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="persona-dato">
                                <small>Celular</small>
                                <span>
                                    {{ $persona->celular ?: 'No registrado' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-5 col-md-6 mb-3">
                            <div class="persona-dato">
                                <small>Correo electrónico</small>
                                <span>
                                    {{ $persona->email ?: 'No registrado' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="persona-dato">
                                <small>Número de socio</small>
                                <span>
                                    {{ $persona->asociado?->numero_socio
                                        ?: 'No registrado' }}
                                </span>
                            </div>
                        </div>

                    </div>

                    {{-- DATOS DE LA SOLICITUD --}}
                    <div class="seccion-titulo">
                        <i class="fa fa-file-text-o mr-2"></i>
                        Información de la solicitud
                    </div>

                    <div class="row">

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="motivo">
                                    Motivo o razón de la solicitud
                                    <span class="campo-obligatorio">*</span>
                                </label>

                                <textarea
                                    id="motivo"
                                    name="motivo"
                                    rows="6"
                                    maxlength="3000"
                                    class="form-control
                                        @error('motivo') is-invalid @enderror"
                                    placeholder="Explique detalladamente el motivo de la solicitud..."
                                    required
                                >{{ old('motivo') }}</textarea>

                                <small class="text-muted">
                                    Describa la situación y el destino de la ayuda solicitada.
                                </small>

                                @error('motivo')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    {{-- DOCUMENTO RESPALDATORIO --}}
                    <div class="seccion-titulo mt-3">
                        <i class="fa fa-paperclip mr-2"></i>
                        Documento respaldatorio
                    </div>

                    <div class="archivo-zona">
                        <i class="fa fa-cloud-upload"></i>

                        <p class="mb-2">
                            Puede adjuntar una imagen o un documento PDF.
                        </p>

                        <label
                            for="documento_respaldo"
                            class="btn btn-outline-success mb-0"
                        >
                            <i class="fa fa-folder-open d-inline mr-1"></i>
                            Seleccionar archivo
                        </label>

                        <input
                            type="file"
                            id="documento_respaldo"
                            name="documento_respaldo"
                            accept=".jpg,.jpeg,.pdf,image/jpeg,application/pdf"
                            hidden
                        >

                        <small class="d-block text-muted mt-3">
                            Formatos permitidos: JPG, JPEG y PDF.
                            El documento es opcional.
                        </small>

                        <div
                            id="errorArchivo"
                            class="text-danger mt-2 d-none"
                        ></div>

                        <img
                            src=""
                            id="previewImagen"
                            class="archivo-preview d-none"
                            alt="Vista previa del documento"
                        >

                        <div id="previewPdf" class="pdf-preview d-none">
                            <i class="fa fa-file-pdf-o d-inline mr-2"></i>
                            <span id="nombrePdf"></span>
                        </div>

                        @error('documento_respaldo')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a
                            href="{{ route('solicitudes') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fa fa-arrow-left mr-1"></i>
                            Volver
                        </a>

                        <button
                            type="submit"
                            class="btn btn-success"
                            id="btnEnviarSolicitud"
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const inputArchivo = document.getElementById('documento_respaldo');

            const previewImagen = document.getElementById('previewImagen');

            const previewPdf = document.getElementById('previewPdf');

            const nombrePdf = document.getElementById('nombrePdf');

            const errorArchivo = document.getElementById('errorArchivo');

            inputArchivo.addEventListener('change', function () {

                previewImagen.classList.add('d-none');
                previewPdf.classList.add('d-none');
                errorArchivo.classList.add('d-none');
                errorArchivo.textContent = '';

                const archivo = this.files[0];

                if (!archivo) {
                    return;
                }

                const formatosPermitidos = [
                    'image/jpeg',
                    'application/pdf'
                ];

                if (!formatosPermitidos.includes(archivo.type)) {
                    this.value = '';

                    errorArchivo.textContent =
                        'El archivo debe ser JPG, JPEG o PDF.';

                    errorArchivo.classList.remove('d-none');
                    return;
                }

                if (archivo.size > 50 * 1024 * 1024) {
                    this.value = '';

                    errorArchivo.textContent =
                        'El archivo no debe superar los 50 MB.';

                    errorArchivo.classList.remove('d-none');
                    return;
                }

                if (archivo.type === 'application/pdf') {
                    nombrePdf.textContent = archivo.name;
                    previewPdf.classList.remove('d-none');
                    return;
                }

                const lector = new FileReader();

                lector.onload = function (evento) {
                    previewImagen.src = evento.target.result;
                    previewImagen.classList.remove('d-none');
                };

                lector.readAsDataURL(archivo);
            });

            const formulario =
                document.getElementById('formAyudaSocial');

            const botonEnviar =
                document.getElementById('btnEnviarSolicitud');

            formulario.addEventListener('submit', function () {
                botonEnviar.disabled = true;

                botonEnviar.innerHTML =
                    '<i class="fa fa-spinner fa-spin mr-1"></i> Enviando...';
            });
        });
    </script>
@endsection
