<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - AJUPEM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --rojo: #c8102e;
            --azul: #003f87;
            --azul-oscuro: #08244a;
            --blanco: #ffffff;
            --gris: #f8f9fc;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 50% 45%, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.75) 26%, transparent 42%),
                linear-gradient(160deg, #c8102e 0%, #c8102e 32%, #ffffff 32%, #ffffff 58%, #003f87 58%, #003f87 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-shell {
            width: 100%;
            max-width: 1100px;
            min-height: 650px;
            display: flex;
            border-radius: 28px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
        }

        .login-left {
            width: 52%;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,0.16), transparent 28%),
                linear-gradient(145deg, #002b63 0%, #003f87 55%, #004fa8 100%);
            color: #fff;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* curva blanca y roja arriba */
        .login-left::before {
            content: '';
            position: absolute;
            top: -150px;
            right: -150px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            border: 22px solid #ffffff;
            background: #c8102e;
        }

        /* curva inferior tipo bandera */
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -120px;
            left: -90px;
            width: 420px;
            height: 210px;
            background: #ffffff;
            border-top: 28px solid #c8102e;
            border-radius: 50% 50% 0 0;
            transform: rotate(12deg);
        }

        .login-right {
            width: 48%;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .welcome-pill {
            display: inline-block;
            align-self: flex-start;
            padding: 10px 18px;
            border-radius: 999px;
            background: linear-gradient(90deg, #c8102e, #e51b35);
            color: #ffffff;
            font-size: 12px;
            letter-spacing: 1px;
            margin-bottom: 28px;
            position: relative;
            z-index: 2;
            font-weight: 700;
        }

        .brand-title {
            font-size: 58px;
            font-weight: 900;
            margin: 0 0 16px 0;
            position: relative;
            z-index: 2;
            color: #ffffff;
            text-shadow: 0 5px 14px rgba(0,0,0,0.35);
        }

        .brand-text {
            font-size: 19px;
            line-height: 1.7;
            max-width: 470px;
            color: rgba(255,255,255,0.96);
            margin: 0 0 34px 0;
            position: relative;
            z-index: 2;
        }

        .info-card {
            position: relative;
            z-index: 2;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.30);
            border-radius: 20px;
            padding: 22px 24px;
            max-width: 500px;
        }

        .form-header h2 {
            margin: 0 0 10px 0;
            font-size: 42px;
            color: #08244a;
            font-weight: 900;
        }

        .field-group input:focus {
            border-color: #003f87;
            box-shadow: 0 0 0 4px rgba(0, 63, 135, 0.13);
        }

        .forgot-link {
            color: #0056b8;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
        }

        .btn-login {
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(90deg, #c8102e 0%, #e51b35 45%, #003f87 100%);
            color: #fff;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s ease;
            box-shadow: 0 14px 28px rgba(0,63,135,0.28);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(200,16,46,0.30);
        }

        .mobile-brand {
            display: none;
            text-align: center;
            margin-bottom: 26px;
        }

        .mobile-brand .mobile-title {
            margin: 0;
            font-size: 34px;
            font-weight: 900;
            color: var(--azul);
        }

        .mobile-brand .mobile-subtitle {
            margin: 8px 0 0 0;
            color: #6f7d8d;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            margin: 0 0 10px 0;
            font-size: 42px;
            color: var(--azul-oscuro);
            font-weight: 900;
        }

        .form-header h2::after {
            content: '';
            display: block;
            width: 90px;
            height: 5px;
            margin-top: 10px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--rojo), var(--azul));
        }

        .form-header p {
            margin: 0;
            color: #6f7d8d;
            font-size: 16px;
        }

        .field-group {
            margin-bottom: 22px;
        }

        .field-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 800;
            color: var(--azul-oscuro);
            font-size: 15px;
        }

        .field-group input {
            width: 100%;
            height: 56px;
            border: 1px solid #d4dfeb;
            border-radius: 16px;
            background: #ffffff;
            padding: 0 16px;
            font-size: 15px;
            outline: none;
            transition: 0.2s ease;
        }

        .field-group input:focus {
            border-color: var(--rojo);
            box-shadow: 0 0 0 4px rgba(200, 16, 46, 0.13);
        }

        .row-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
            flex-wrap: wrap;
        }

        .remember-box {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #29486d;
            font-size: 15px;
        }

        .remember-box input {
            width: 16px;
            height: 16px;
            accent-color: var(--rojo);
        }

        .forgot-link {
            color: var(--rojo);
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(90deg, var(--rojo), var(--azul));
            color: #fff;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s ease;
            box-shadow: 0 12px 24px rgba(0,63,135,0.25);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(200,16,46,0.28);
        }

        .footer-note {
            margin-top: 24px;
            text-align: center;
            font-size: 13px;
            color: #7e8894;
        }

        .invalid-feedback {
            display: block;
            margin-top: 8px;
            color: var(--rojo);
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 920px) {
            .login-shell {
                flex-direction: column;
                max-width: 560px;
                min-height: auto;
            }

            .login-left {
                display: none;
            }

            .login-right {
                width: 100%;
                padding: 36px 28px;
            }

            .login-right::before {
                width: 100%;
                height: 8px;
                background: linear-gradient(90deg, var(--rojo), #ffffff, var(--azul));
            }

            .mobile-brand {
                display: block;
            }

            .form-header {
                text-align: center;
            }

            .form-header h2 {
                font-size: 34px;
            }

            .form-header h2::after {
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 14px;
            }

            .login-shell {
                border-radius: 22px;
                max-width: 100%;
            }

            .login-right {
                padding: 28px 20px;
            }

            .mobile-brand .mobile-title {
                font-size: 30px;
            }

            .mobile-brand .mobile-subtitle {
                font-size: 13px;
            }

            .form-header h2 {
                font-size: 30px;
            }

            .form-header p {
                font-size: 15px;
            }

            .field-group input,
            .btn-login {
                height: 52px;
                border-radius: 14px;
            }

            .btn-login {
                font-size: 16px;
            }

            .row-actions {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-left">
            <div class="welcome-pill">BIENVENIDO AL SISTEMA</div>

            <h1 class="brand-title">AJUPEM</h1>

            <p class="brand-text">
                Asociación de Jubilados y Pensionados Municipales del Paraguay.
                Plataforma de acceso institucional para la gestión administrativa
                y operativa del sistema.
            </p>

            <div class="info-card">
                <h4>Acceso institucional</h4>
                <p>
                    Ingrese con su <strong>usuario</strong> y contraseña para acceder
                    al panel principal del sistema.
                </p>
            </div>
        </div>

        <div class="login-right">
            <div class="mobile-brand">
                <h1 class="mobile-title">AJUPEM</h1>
                <p class="mobile-subtitle">
                    Asociación de Jubilados y Pensionados Municipales del Paraguay
                </p>
            </div>

            <div class="form-header">
                <h2>Iniciar sesión</h2>
                <p>Ingrese sus credenciales para continuar</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field-group">
                    <label for="usuario">Usuario</label>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    @error('username')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field-group">
                    <label for="password">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row-actions">
                    <label class="remember-box" for="remember">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span>Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">
                            ¿Olvidó su contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    Ingresar al sistema
                </button>
            </form>

            <div class="footer-note">
                © 2026 AJUPEM - Todos los derechos reservados
            </div>
        </div>
    </div>
</body>
</html>
