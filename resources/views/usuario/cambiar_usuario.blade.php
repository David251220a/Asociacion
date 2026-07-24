@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/alert.css') }}">

    <style>
        .password-card {
            overflow: hidden;
            border: 1px solid #e4eaf0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 28px rgba(26, 58, 91, 0.08);
        }

        .password-header {
            padding: 25px 30px;
            color: #ffffff;
            background: linear-gradient(135deg, #1b6fc2, #173a5e);
        }

        .password-header h3 {
            margin-bottom: 5px;
            color: #ffffff;
            font-weight: 700;
        }

        .password-header p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.80);
        }

        .password-contenido {
            padding: 30px;
        }

        .password-icono {
            width: 75px;
            height: 75px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #1b6fc2;
            background-color: #e4f0fb;
            font-size: 32px;
        }

        .password-requisitos {
            padding: 15px 18px;
            border-radius: 10px;
            color: #6c5a17;
            background-color: #fff7d8;
            font-size: 13px;
        }

        .password-requisitos ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .btn-ver-password {
            border: 1px solid #ced4da;
            color: #6c757d;
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10">

                <div class="password-card">

                    <div class="password-header">
                        <h3>
                            <i class="fa fa-lock mr-2"></i>
                            Cambiar contraseña
                        </h3>

                        <p>
                            Actualizá la contraseña utilizada para ingresar al sistema.
                        </p>
                    </div>

                    <div class="password-contenido">

                        @include('varios.mensaje')

                        <div class="password-icono">
                            <i class="fa fa-key"></i>
                        </div>

                        <form action="{{ route('user.cambiar_contrase_post') }}" method="POST" id="formCambiarPassword">
                            @csrf

                            <div class="form-group">
                                <label for="current_password">
                                    Contraseña actual
                                </label>

                                <div class="input-group">
                                    <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
                                        autocomplete="current-password"
                                        required
                                    >

                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-ver-password" data-password="current_password" title="Mostrar contraseña">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>

                                    @error('current_password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password">
                                    Nueva contraseña
                                </label>

                                <div class="input-group">
                                    <input type="password" id="password" name="password"class="form-control @error('password') is-invalid @enderror"
                                        autocomplete="new-password"
                                        required
                                    >

                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-ver-password" data-password="password" title="Mostrar contraseña">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>

                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">
                                    Confirmar nueva contraseña
                                </label>

                                <div class="input-group">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>

                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-ver-password" data-password="password_confirmation" title="Mostrar contraseña">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="password-requisitos mb-4">
                                <strong>
                                    <i class="fa fa-info-circle mr-1"></i>
                                    Recomendaciones
                                </strong>

                                <ul>
                                    <li>Utilizá al menos 8 caracteres.</li>
                                    <li>Combiná letras, números y símbolos.</li>
                                    <li>No utilices tu nombre o documento.</li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary" id="btnGuardarPassword">
                                    <i class="fa fa-save mr-1"></i>
                                    Cambiar contraseña
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const botones = document.querySelectorAll('[data-password]');

            botones.forEach(function (boton) {
                boton.addEventListener('click', function () {

                    const inputId = this.getAttribute('data-password');
                    const input = document.getElementById(inputId);
                    const icono = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icono.classList.remove('fa-eye');
                        icono.classList.add('fa-eye-slash');
                        this.title = 'Ocultar contraseña';
                    } else {
                        input.type = 'password';
                        icono.classList.remove('fa-eye-slash');
                        icono.classList.add('fa-eye');
                        this.title = 'Mostrar contraseña';
                    }
                });
            });

            const formulario = document.getElementById('formCambiarPassword');
            const botonGuardar = document.getElementById('btnGuardarPassword');

            formulario.addEventListener('submit', function () {
                botonGuardar.disabled = true;
                botonGuardar.innerHTML =
                    '<i class="fa fa-spinner fa-spin mr-1"></i> Guardando...';
            });
        });
    </script>
@endsection
