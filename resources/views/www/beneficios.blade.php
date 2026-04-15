@extends('layouts.web')

@section('contenido')

<section class="subhero-beneficios">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <span class="subhero-badge">Servicios y acompañamiento</span>
                <h1 class="subhero-titulo">Beneficios para nuestros asociados</h1>
                <p class="subhero-texto">
                    La asociación brinda apoyo a sus miembros a través de servicios, asistencia y gestiones
                    orientadas al bienestar y a la atención de necesidades concretas.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="seccion bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <div class="titulo-seccion-mini">Nuestros beneficios</div>
            <h2 class="titulo-seccion">Acompañamos a nuestros asociados en diferentes necesidades</h2>
            <p class="texto-seccion mx-auto" style="max-width: 780px;">
                Estos beneficios están pensados para brindar respaldo, orientación y apoyo práctico a los asociados,
                facilitando el acceso a servicios y soluciones en distintas situaciones.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="beneficio-card h-100">
                    <div class="beneficio-icono">A</div>
                    <h3>Servicio de ambulancia en zona Asunción</h3>
                    <p>
                        Se brinda apoyo mediante servicio de ambulancia en la zona de Asunción,
                        permitiendo una respuesta más rápida y segura ante situaciones de urgencia
                        o necesidad de traslado.
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="beneficio-card h-100">
                    <div class="beneficio-icono">M</div>
                    <h3>Provisión de medicamentos</h3>
                    <p>
                        La asociación acompaña a asociados con enfermedades o situaciones de necesidad,
                        gestionando apoyo para el acceso a medicamentos cuando las circunstancias lo requieren.
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="beneficio-card h-100">
                    <div class="beneficio-icono">P</div>
                    <h3>Pequeños préstamos</h3>
                    <p>
                        Se contempla el otorgamiento de pequeños préstamos como forma de apoyo económico,
                        de acuerdo con las condiciones y posibilidades establecidas por la asociación.
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="beneficio-card h-100">
                    <div class="beneficio-icono">I</div>
                    <h3>Intermediación con proveedores</h3>
                    <p>
                        La asociación actúa como intermediaria en el contacto con proveedores para facilitar
                        la adquisición de bienes, como televisores u otros artículos, acercando mejores opciones
                        a los asociados.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="seccion beneficios-fondo">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="bloque-imagen-beneficios">
                    <img src="{{ Storage::url('iconos/beneficios.jpg') }}" alt="Beneficios de la asociación" class="img-fluid">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="titulo-seccion-mini">Compromiso social</div>
                <h2 class="titulo-seccion">Beneficios pensados para acompañar y asistir</h2>
                <p class="texto-seccion mb-4">
                    Más que servicios, estos beneficios representan una forma concreta de respaldo para los asociados.
                    La asociación busca acompañar de manera cercana, humana y útil, respondiendo a necesidades reales
                    con gestión y compromiso institucional.
                </p>

                <a href="{{ route('contacto') }}" class="btn btn-login">
                    Solicitar más información
                </a>
            </div>
        </div>
    </div>
</section>

<section class="seccion bg-white">
    <div class="container">
        <div class="info-box-beneficios text-center">
            <div class="titulo-seccion-mini">Importante</div>
            <h2 class="titulo-seccion mb-3">Los beneficios están sujetos a evaluación y disponibilidad</h2>
            <p class="texto-seccion mx-auto" style="max-width: 820px;">
                La prestación de estos beneficios se realiza conforme a las condiciones, posibilidades y procedimientos
                definidos por la asociación. Para más detalles, los asociados pueden comunicarse a través de los canales oficiales.
            </p>
        </div>
    </div>
</section>

@endsection