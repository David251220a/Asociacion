<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asociación - Inicio</title>
    <link rel="stylesheet" href="{{ asset('inicio/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/inicio/css/inicio.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-principal sticky-top py-3">
        <div class="container">
            <a class="navbar-brand" href="{{route('inicio')}}">Asociación</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="{{route('inicio')}}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{route('mision')}}">Misión y Visión</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{route('beneficios')}}">Beneficios</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{route('noticias')}}">Noticias</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{route('contacto')}}">Contacto</a></li>
                </ul>

                <a href="{{route('login')}}" class="btn btn-login">Iniciar sesión</a>
            </div>
        </div>
    </nav>

    @yield('contenido')

    <footer class="footer-principal">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0">© 2026 Asociación. Todos los derechos reservados.</p>
            <p class="mb-0">Diseño principal institucional.</p>
        </div>
    </footer>

    <script src="{{ asset('inicio/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
