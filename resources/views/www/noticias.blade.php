@extends('layouts.web')

@section('contenido')

<section class="subhero-noticias">
    <div class="container text-center">
        <h1 class="subhero-titulo">Noticias y novedades</h1>
        <p class="subhero-texto">
            Enterate de las últimas actividades, comunicados y novedades de la asociación.
        </p>
    </div>
</section>

<section class="seccion noticias-fondo">
    <div class="container">

        <div class="row g-4">

            {{-- NOTICIA 1 --}}
            <div class="col-lg-4 col-md-6">
                <div class="noticia-card h-100">

                    <div class="noticia-imagen">
                        <img src="{{ asset('inicio/img/noticia1.jpg') }}" alt="noticia">
                    </div>

                    <div class="noticia-contenido">
                        <div class="noticia-fecha">Abril 2026</div>

                        <h4>Asamblea general de la asociación</h4>

                        <p>
                            Se llevó a cabo la asamblea general donde se presentaron avances y nuevos proyectos institucionales.
                        </p>

                        <a href="{{route('noticias.show', 1)}}" class="btn-enlace">
                            Leer más →
                        </a>
                    </div>

                </div>
            </div>

            {{-- NOTICIA 2 --}}
            <div class="col-lg-4 col-md-6">
                <div class="noticia-card h-100">

                    <div class="noticia-imagen">
                        <img src="{{ asset('inicio/img/noticia2.jpg') }}" alt="noticia">
                    </div>

                    <div class="noticia-contenido">
                        <div class="noticia-fecha">Marzo 2026</div>

                        <h4>Jornada de atención al asociado</h4>

                        <p>
                            Se realizó una jornada especial de orientación y acompañamiento para asociados.
                        </p>

                        <a href="#" class="btn-enlace">
                            Leer más →
                        </a>
                    </div>

                </div>
            </div>

            {{-- NOTICIA 3 --}}
            <div class="col-lg-4 col-md-6">
                <div class="noticia-card h-100">

                    <div class="noticia-imagen">
                        <img src="{{ asset('inicio/img/noticia3.jpg') }}" alt="noticia">
                    </div>

                    <div class="noticia-contenido">
                        <div class="noticia-fecha">Febrero 2026</div>

                        <h4>Implementación de nuevos beneficios</h4>

                        <p>
                            La asociación incorpora nuevos servicios para mejorar la atención y el bienestar de los asociados.
                        </p>

                        <a href="#" class="btn-enlace">
                            Leer más →
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

@endsection