@extends('layouts.web')

@section('contenido')

    <section class="hero">
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="hero-badge">Portal institucional de la asociación</span>
                    <h1>Comprometidos con el bienestar y la representación de nuestros asociados</h1>
                    <p>
                        Un espacio moderno, cercano y confiable para informar, acompañar y fortalecer
                        el vínculo con cada asociado de nuestra comunidad.
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="noticias.html" class="btn btn-principal">Ver noticias</a>
                        <a href="contacto.html" class="btn btn-secundario">Contactar</a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="hero-panel">
                        <div class="hero-imagen"></div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mini-card verde">
                                    <h5>Gestión</h5>
                                    <p>Procesos organizados y una atención más clara para servir mejor a los asociados.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mini-card">
                                    <h5>Compromiso</h5>
                                    <p>Información actualizada, acompañamiento constante y cercanía institucional.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="estadisticas">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="estadistica-box">
                        <h3>Solicitud abierta</h3>
                        <p>Podés solicitar formar parte de la asociación.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="estadistica-box">
                        <h3>24/7</h3>
                        <p class="mb-0">Información institucional disponible en línea.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="estadistica-box">
                        <h3>100%</h3>
                        <p class="mb-0">Compromiso con la transparencia y la atención.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="seccion bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <div class="titulo-seccion-mini">Accesos principales</div>
                <h2 class="titulo-seccion">Conocé más sobre nuestra asociación</h2>
                <p class="texto-seccion mx-auto" style="max-width: 760px;">
                    Accedé fácilmente a la información institucional, beneficios, servicios y canales de contacto.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="acceso-card">
                        <div class="icono-circulo">MV</div>
                        <h4>Misión y Visión</h4>
                        <p>Conocé los valores, objetivos y propósito que guían el trabajo de nuestra asociación.</p>
                        <a href="mision.html" class="btn-enlace">Ver sección →</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="acceso-card">
                        <div class="icono-circulo">B</div>
                        <h4>Beneficios</h4>
                        <p>Descubrí los servicios, programas y acompañamientos que ofrecemos a nuestros asociados.</p>
                        <a href="beneficios.html" class="btn-enlace">Ver sección →</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="acceso-card">
                        <div class="icono-circulo">C</div>
                        <h4>Contacto</h4>
                        <p>Encontrá los canales de atención para consultas, orientación y asistencia institucional.</p>
                        <a href="contacto.html" class="btn-enlace">Ver sección →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="seccion noticias-fondo">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
                <div>
                    <div class="titulo-seccion-mini">Actualidad institucional</div>
                    <h2 class="titulo-seccion mb-0">Noticias destacadas</h2>
                </div>
                <a href="noticias.html" class="btn btn-outline-secondary rounded-pill px-4 py-2">Ver todas</a>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="noticia-card">
                        <div class="noticia-imagen">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80" alt="noticias">
                        </div>
                        <div class="noticia-contenido">
                            <div class="noticia-fecha">Abril 2026</div>
                            <h4>Asamblea general y presentación de avances institucionales</h4>
                            <p>Compartimos los principales logros, proyectos en curso y próximos objetivos de la asociación.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="noticia-card">
                        <div class="noticia-imagen">
                            <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80" alt="noticias">
                        </div>
                        <div class="noticia-contenido">
                            <div class="noticia-fecha">Marzo 2026</div>
                            <h4>Jornada especial de atención y acompañamiento al asociado</h4>
                            <p>Se realizó una jornada con orientación personalizada y asistencia para distintos trámites.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="noticia-card">
                        <div class="noticia-imagen">
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80" alt="noticias">
                        </div>
                        <div class="noticia-contenido">
                            <div class="noticia-fecha">Febrero 2026</div>
                            <h4>Nuevos programas de apoyo y fortalecimiento institucional</h4>
                            <p>La asociación impulsa nuevas acciones orientadas al bienestar, representación y servicio.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

