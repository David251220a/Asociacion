@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .perfil-card {
            overflow: hidden;
            border: 1px solid #e4eaf0;
            border-radius: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f3f8fd 100%);
            box-shadow: 0 8px 28px rgba(26, 58, 91, 0.08);
        }

        .perfil-contenido {
            display: flex;
            align-items: center;
            padding: 35px 45px;
        }

        .perfil-foto-zona {
            width: 210px;
            min-width: 210px;
            text-align: center;
        }

        .perfil-foto {
            width: 155px;
            height: 155px;
            margin: 0 auto 18px;
            overflow: hidden;
            border: 5px solid #ffffff;
            border-radius: 50%;
            background-color: #e9eef3;
            box-shadow: 0 10px 28px rgba(27, 111, 194, 0.22);
        }

        .perfil-foto img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
        }

        .perfil-datos {
            padding-left: 40px;
        }

        .perfil-etiqueta {
            display: inline-block;
            margin-bottom: 10px;
            padding: 5px 14px;
            border-radius: 20px;
            color: #1b6fc2;
            background-color: #e4f0fb;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .perfil-datos h2 {
            margin-bottom: 6px;
            color: #173a5e;
            font-size: 28px;
            font-weight: 700;
        }

        .perfil-datos h4 {
            margin-bottom: 12px;
            color: #44576a;
            font-size: 18px;
            font-weight: 500;
        }

        .perfil-datos p {
            max-width: 700px;
            margin-bottom: 18px;
            color: #6c7b8a;
            font-size: 15px;
            line-height: 1.7;
        }

        .btn-seleccionar-foto,
        .btn-guardar-foto {
            display: block;
            width: 175px;
            margin: 8px auto 0;
            padding: 9px 15px;
            border: none;
            border-radius: 24px;
            color: #ffffff !important;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-seleccionar-foto {
            background-color: #1b6fc2;
        }

        .btn-guardar-foto {
            background-color: #168552;
        }

        .btn-seleccionar-foto:hover,
        .btn-guardar-foto:hover {
            color: #ffffff;
            transform: translateY(-1px);
            filter: brightness(0.92);
        }

        .foto-ayuda {
            display: block;
            margin-top: 12px;
            color: #8795a3;
            font-size: 11px;
        }

        .foto-registrada,
        .foto-pendiente {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .foto-registrada {
            color: #137047;
            background-color: #dff5ea;
        }

        .foto-pendiente {
            color: #986900;
            background-color: #fff3cd;
        }

        @media (max-width: 767.98px) {
            .perfil-contenido {
                flex-direction: column;
                padding: 28px 20px;
                text-align: center;
            }

            .perfil-foto-zona {
                width: 100%;
                min-width: auto;
            }

            .perfil-datos {
                padding-top: 25px;
                padding-left: 0;
            }

            .perfil-datos h2 {
                font-size: 23px;
            }
        }
    </style>
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="perfil-card">

            @php
                $tieneSelfi = !empty($persona->selfi);
                $fotoPerfil = $tieneSelfi ? Storage::disk('public')->url($persona->selfi) : asset('iconos/user.jpg');
            @endphp

            <form action="{{ route('persona.foto.guardar', $persona->id) }}" method="POST" enctype="multipart/form-data" id="formSelfi">
                @csrf

                <div class="perfil-contenido">

                    <div class="perfil-foto-zona">
                        <div class="perfil-foto">
                            <img src="{{ $fotoPerfil }}" id="previewSelfi" alt="Fotografía de {{ $persona->nombre }}">
                        </div>

                        @if (!$tieneSelfi)
                            <label for="selfi" class="btn-seleccionar-foto">
                                <i class="fas fa-camera mr-2"></i>
                                <span id="textoSeleccionar">
                                    Agregar foto
                                </span>
                            </label>

                            <input type="file" id="selfi" name="selfi" accept="image/jpeg,image/png" hidden>

                            <button type="submit" id="btnGuardarSelfi" class="btn-guardar-foto d-none">
                                <i class="fas fa-save mr-2"></i>
                                Guardar foto
                            </button>

                            <small class="foto-ayuda">
                                Formatos JPG o PNG. Máximo 2 MB.
                            </small>

                            <div id="errorSelfi" class="text-danger mt-2 d-none"></div>

                            @error('selfi')
                                <div class="text-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        @endif
                    </div>

                    <div class="perfil-datos">
                        <span class="perfil-etiqueta">
                            Sistema institucional
                        </span>

                        <h2>
                            Bienvenido, {{ $persona->nombre }}
                        </h2>

                        <h4>
                            {{ $persona->nombre }}
                            {{ $persona->apellido }}
                        </h4>

                        <p>
                            Un espacio diseñado para facilitar la gestión
                            institucional y brindar un mejor servicio a nuestros
                            asociados.
                        </p>

                        @if ($tieneSelfi)
                            <div class="foto-registrada">
                                <i class="fas fa-check-circle mr-2"></i>
                                Fotografía registrada
                            </div>
                        @else
                            <div class="foto-pendiente">
                                <i class="fas fa-info-circle mr-2"></i>
                                Seleccioná una fotografía para completar tu perfil.
                            </div>
                        @endif
                    </div>

                </div>
            </form>

        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const inputSelfi = document.getElementById('selfi');

            if (!inputSelfi) {
                return;
            }

            const preview = document.getElementById('previewSelfi');
            const botonGuardar = document.getElementById('btnGuardarSelfi');
            const textoSeleccionar = document.getElementById('textoSeleccionar');
            const error = document.getElementById('errorSelfi');

            inputSelfi.addEventListener('change', function () {

                error.classList.add('d-none');
                error.textContent = '';
                botonGuardar.classList.add('d-none');

                const archivo = this.files[0];

                if (!archivo) {
                    return;
                }

                const formatosPermitidos = [
                    'image/jpeg',
                    'image/jpg'
                ];

                if (!formatosPermitidos.includes(archivo.type)) {
                    this.value = '';

                    error.textContent =
                        'La fotografía debe ser JPG.';

                    error.classList.remove('d-none');
                    return;
                }

                if (archivo.size > 50 * 1024 * 1024) {
                    this.value = '';

                    error.textContent =
                        'La fotografía no debe superar los 2 MB.';

                    error.classList.remove('d-none');
                    return;
                }

                const lector = new FileReader();

                lector.onload = function (evento) {
                    preview.src = evento.target.result;
                    textoSeleccionar.textContent = 'Cambiar foto';
                    botonGuardar.classList.remove('d-none');
                };

                lector.readAsDataURL(archivo);
            });
        });
    </script>
@endsection
