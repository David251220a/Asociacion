<div>
    <section class="py-5" style="min-height: 80vh; background: linear-gradient(135deg, #5f846f 0%, #89a992 100%);">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
                <div class="col-lg-6 col-md-8">

                    <div class="card border-0 shadow-lg" style="border-radius: 25px;">
                        <div class="card-body p-5">

                            {{-- Paso --}}
                            <div class="text-center mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold"
                                     style="width: 55px; height: 55px; background: #4d7a61; color: white;">
                                    1
                                </div>
                                <p class="mt-2 mb-0 fw-semibold" style="color: #355b46;">
                                    Verificación de documento
                                </p>
                            </div>

                            {{-- Título --}}
                            <div class="text-center mb-4">
                                <h2 class="fw-bold" style="color: #355b46;">
                                    Solicitud de asociación
                                </h2>

                                <p class="text-muted">
                                    Ingresá tu documento para verificar tu estado.
                                </p>
                            </div>

                            {{-- Input --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Documento</label>
                                <input type="text" class="form-control form-control-lg" wire:model.defer="documento" placeholder="Ej: 1234567" style="border-radius: 12px; height: 50px;">

                            {{-- Botón --}}
                            <div class="d-grid">
                                <button class="btn btn-success btn-lg" wire:click="verificarDocumento" style="border-radius: 12px;">
                                    Siguiente
                                </button>
                            </div>

                            {{-- Mensaje --}}
                            @if($mensajeValidacion)
                                <div class="alert alert-warning mt-4">
                                    {{ $mensajeValidacion }}
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
