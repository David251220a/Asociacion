@extends('layouts.web')

@section('contenido')

    <section class="subhero-mision">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-9">
                    <span class="subhero-badge">Nuestra identidad institucional</span>
                    <h1 class="subhero-titulo">Misión y Visión</h1>
                    <p class="subhero-texto">
                        Conocé los principios, objetivos y la proyección que orientan el trabajo de nuestra asociación,
                        con compromiso, cercanía y vocación de servicio.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="seccion bg-white">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="mision-card h-100">
                        <h2>Misión</h2>
                        <p>
                            {{$mision}}
                        </p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mision-card h-100 vision-card">
                        <h2>Visión</h2>
                        <p>
                            {{$vision}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="seccion valores-fondo">
        <div class="container">
            <div class="text-center mb-5">
                <div class="titulo-seccion-mini">Principios que nos guían</div>
                <h2 class="titulo-seccion">Nuestros valores</h2>
                <p class="texto-seccion mx-auto" style="max-width: 760px;">
                    La asociación se fortalece a través de valores que orientan cada acción institucional
                    y cada servicio brindado a la comunidad.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="valor-card h-100">
                        <h4>Compromiso</h4>
                        <p>Trabajamos con dedicación y responsabilidad en beneficio de nuestros asociados.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="valor-card h-100">
                        <h4>Transparencia</h4>
                        <p>Impulsamos una gestión clara, confiable y abierta en nuestras acciones.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="valor-card h-100">
                        <h4>Solidaridad</h4>
                        <p>Fomentamos el acompañamiento y el apoyo mutuo entre todos los miembros.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="valor-card h-100">
                        <h4>Servicio</h4>
                        <p>Priorizamos la atención cercana y la búsqueda de soluciones útiles y oportunas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection