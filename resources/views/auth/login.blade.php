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

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                linear-gradient(135deg, rgba(8, 36, 74, 0.92), rgba(22, 95, 168, 0.88)),
                radial-gradient(circle at top right, rgba(255,255,255,0.10), transparent 30%),
                radial-gradient(circle at bottom left, rgba(255,255,255,0.08), transparent 25%);
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
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.28);
        }

        .login-left {
            width: 52%;
            background: linear-gradient(160deg, #0b2f5b 0%, #13539b 100%);
            color: #fff;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }

        .welcome-pill {
            display: inline-block;
            align-self: flex-start;
            padding: 10px 18px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.28);
            background: rgba(255,255,255,0.10);
            font-size: 12px;
            letter-spacing: 1px;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .brand-title {
            font-size: 58px;
            font-weight: 800;
            margin: 0 0 16px 0;
            position: relative;
            z-index: 1;
        }

        .brand-text {
            font-size: 19px;
            line-height: 1.7;
            max-width: 470px;
            color: rgba(255,255,255,0.94);
            margin: 0 0 34px 0;
            position: relative;
            z-index: 1;
        }

        .info-card {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.20);
            border-radius: 20px;
            padding: 22px 24px;
            max-width: 500px;
        }

        .info-card h4 {
            margin: 0 0 10px 0;
            font-size: 17px;
            font-weight: 700;
        }

        .info-card p {
            margin: 0;
            line-height: 1.7;
            color: rgba(255,255,255,0.92);
            font-size: 15px;
        }

        .login-right {
            width: 48%;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #f8fbff;
        }

        .mobile-brand {
            display: none;
            text-align: center;
            margin-bottom: 26px;
        }

        .mobile-brand .mobile-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            color: #123d72;
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
            color: #123d72;
            font-weight: 800;
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
            font-weight: 700;
            color: #1d4270;
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
            border-color: #1a6bc1;
            box-shadow: 0 0 0 4px rgba(26, 107, 193, 0.12);
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
        }

        .forgot-link {
            color: #1d6fcb;
            text-decoration: none;
            font-weight: 600;
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
            background: linear-gradient(90deg, #0f4d8d, #1d73ce);
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
            box-shadow: 0 12px 24px rgba(20, 92, 170, 0.22);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(20, 92, 170, 0.28);
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
            color: #c62828;
            font-size: 13px;
            font-weight: 600;
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
                background: #ffffff;
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
        }

        @media (max-width: 560px) {
            body {
                padding: 14px;
                background:
                    linear-gradient(135deg, rgba(8, 36, 74, 0.96), rgba(22, 95, 168, 0.92));
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

            .field-group input {
                height: 52px;
                border-radius: 14px;
            }

            .btn-login {
                height: 52px;
                font-size: 16px;
                border-radius: 14px;
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