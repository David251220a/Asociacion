@extends('layouts.web')

@section('contenido')

<section class="subhero-contacto">
    <div class="container text-center">
        <span class="subhero-badge">Canales de atención</span>
        <h1 class="subhero-titulo">Contáctanos</h1>
        <p class="subhero-texto">
            Estamos para orientarte, responder tus consultas y brindarte información sobre la asociación y sus beneficios.
        </p>
    </div>
</section>

<section class="seccion bg-white">
    <div class="container">
        <div class="row g-5 align-items-stretch">

            <div class="col-lg-5">
                <div class="contacto-info h-100">
                    <div class="titulo-seccion-mini">Información de contacto</div>
                    <h2 class="titulo-seccion">Estamos para ayudarte</h2>
                    <p class="texto-seccion mb-4">
                        Podés comunicarte con la asociación a través de nuestros canales de atención.
                        Estamos comprometidos en brindar una respuesta clara, cercana y oportuna.
                    </p>

                    <div class="contacto-item">
                        <div class="contacto-icono">📍</div>
                        <div>
                            <h5>Dirección</h5>
                            <p>Asunción, Paraguay</p>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <div class="contacto-icono">📞</div>
                        <div>
                            <h5>Teléfono</h5>
                            <p>(021) 000 000</p>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <div class="contacto-icono">✉️</div>
                        <div>
                            <h5>Correo electrónico</h5>
                            <p>contacto@asociacion.org.py</p>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <div class="contacto-icono">🕒</div>
                        <div>
                            <h5>Horario de atención</h5>
                            <p>Lunes a viernes de 07:00 a 15:00</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="contacto-form-card">
                    <div class="titulo-seccion-mini">Formulario</div>
                    <h2 class="titulo-seccion">Envíanos tu consulta</h2>
                    <p class="texto-seccion mb-4">
                        Completá el formulario y próximamente este canal estará habilitado para el envío de consultas.
                    </p>

                    <form onsubmit="event.preventDefault(); mostrarMensajeContacto();">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control input-contacto" placeholder="Ingrese su nombre">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control input-contacto" placeholder="Ingrese su apellido">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control input-contacto" placeholder="Ingrese su correo">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control input-contacto" placeholder="Ingrese su teléfono">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Asunto</label>
                                <input type="text" class="form-control input-contacto" placeholder="Ingrese el asunto">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mensaje</label>
                                <textarea class="form-control input-contacto" rows="6" placeholder="Escriba su mensaje"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-login px-4">
                                    Enviar consulta
                                </button>
                            </div>

                            <div class="col-12">
                                <div id="mensajeContacto" class="alert alert-success mt-3 d-none" role="alert">
                                    Gracias por tu interés. Próximamente este formulario estará habilitado.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function mostrarMensajeContacto() {
        document.getElementById('mensajeContacto').classList.remove('d-none');
    }
</script>

@endsection