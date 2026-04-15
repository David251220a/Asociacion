@extends('layouts.web')

@section('contenido')

<section class="subhero-noticias">
    <div class="container text-center">
        <h1 class="subhero-titulo">Detalle de noticia</h1>
        <p class="subhero-texto">
            Conocé más sobre las actividades y novedades de la asociación.
        </p>
    </div>
</section>

<section class="seccion bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="detalle-noticia-card">
                    <div class="detalle-noticia-fecha">Abril 2026</div>
                    <h1 class="detalle-noticia-titulo">
                        Asamblea general y presentación de avances institucionales
                    </h1>

                    <div class="detalle-noticia-imagen-principal">
                        <img src="{{ asset('inicio/img/noticia1.jpg') }}" alt="Noticia principal">
                    </div>

                    <div class="detalle-noticia-texto">
                        <p>
                            La asociación llevó a cabo una asamblea general en la que se expusieron los principales
                            avances institucionales alcanzados durante el periodo, así como los proyectos y acciones
                            previstas para fortalecer la atención y el acompañamiento a los asociados.
                        </p>

                        <p>
                            Durante la jornada se abordaron distintos temas relacionados con el crecimiento de la
                            institución, la mejora de los servicios disponibles y la importancia de seguir impulsando
                            espacios de participación y comunicación permanente con los miembros de la asociación.
                        </p>

                        <p>
                            Asimismo, se destacó el compromiso de la organización en continuar promoviendo beneficios,
                            asistencia y gestiones orientadas al bienestar de los asociados, manteniendo una visión
                            de trabajo transparente, cercana y responsable.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<section class="seccion galeria-fondo">
    <div class="container">
        <div class="text-center mb-5">
            <div class="titulo-seccion-mini">Galería</div>
            <h2 class="titulo-seccion">Más imágenes de la actividad</h2>
            <p class="texto-seccion mx-auto" style="max-width: 760px;">
                Compartimos algunas fotografías relacionadas con esta noticia y la participación institucional.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="galeria-noticia-card">
                    <img src="{{ asset('inicio/img/noticia1.jpg') }}" alt="Galería 1">
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="galeria-noticia-card">
                    <img src="{{ asset('inicio/img/noticia2.jpg') }}" alt="Galería 2">
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="galeria-noticia-card">
                    <img src="{{ asset('inicio/img/noticia3.jpg') }}" alt="Galería 3">
                </div>
            </div>

            <div class="col-md-6 col-lg-6">
                <div class="galeria-noticia-card galeria-alta">
                    <img src="{{ asset('inicio/img/noticia4.jpg') }}" alt="Galería 4">
                </div>
            </div>

            <div class="col-md-6 col-lg-6">
                <div class="galeria-noticia-card galeria-alta">
                    <img src="{{ asset('inicio/img/noticia5.jpg') }}" alt="Galería 5">
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('noticias') }}" class="btn btn-login">
                Volver a noticias
            </a>
        </div>
    </div>
</section>

@endsection