<div>
    <section class="asociarse-section py-5">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
                <div class="col-lg-6 col-md-8">

                    @if ($paso == 1)
                        <div class="card asociarse-card fade-step border-0 shadow-lg">
                            <div class="card-body p-5">

                                <div class="text-center mb-4">
                                    <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold">
                                        1
                                    </div>
                                    <p class="mt-2 mb-0 fw-semibold asociarse-subtitulo">
                                        Verificación de documento
                                    </p>
                                </div>

                                <div class="text-center mb-4">
                                    <h2 class="fw-bold asociarse-titulo">
                                        Solicitud de asociación
                                    </h2>

                                    <p class="text-muted">
                                        Ingresá tu documento para verificar tu estado.
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Documento</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-lg asociarse-input"
                                        wire:model.defer="documento"
                                        placeholder="Ej: 1234567"
                                    >
                                </div>

                                <div class="d-grid">
                                    <button
                                        type="button"
                                        class="btn btn-lg asociarse-btn"
                                        wire:click="verificarDocumento"
                                        wire:loading.attr="disabled"
                                        wire:target="verificarDocumento">

                                        <span wire:loading.remove wire:target="verificarDocumento">
                                            Siguiente
                                        </span>

                                        <span wire:loading wire:target="verificarDocumento" style="display: none;">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            Verificando...
                                        </span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if($paso == 2)
                        <div class="card asociarse-card border-0 shadow-lg fade-step">
                            <div class="card-body p-5">

                                <div class="text-center mb-4">
                                    <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold">
                                        2
                                    </div>
                                    <p class="mt-2 mb-0 fw-semibold asociarse-subtitulo">
                                        Datos personales
                                    </p>
                                </div>

                                <div class="text-center mb-4">
                                    <h2 class="fw-bold asociarse-titulo">
                                        Completá tus datos
                                    </h2>
                                    <p class="text-muted">
                                        Ingresá tus datos personales para continuar la solicitud.
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Nombre</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="nombre">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Apellido</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="apellido">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Fecha de nacimiento</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" id="fecha_nacimiento" wire:model.defer="fecha_nacimiento" placeholder="dd/mm/aaaa" maxlength="10" autocomplete="off">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Sexo</label>
                                        <select class="form-control form-control-lg asociarse-input" wire:model.defer="sexo_id">
                                            @foreach ($sexos as $item)
                                                <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label class="form-label fw-semibold">Estado civil</label>
                                        <select class="form-control form-control-lg asociarse-input" wire:model.defer="estado_civil_id">
                                            @foreach ($estadosCivils as $item)
                                                <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label class="form-label fw-semibold">Tipo Asociado</label>
                                        <select class="form-control form-control-lg asociarse-input" wire:model.defer="tipo_id">
                                            @foreach ($tipos as $item)
                                                <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Correo Electronico</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="email">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Celular</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="celular">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-lg asociarse-btn-back w-50" wire:click="volverPasoUno">
                                        Retroceder
                                    </button>

                                    <button type="button" class="btn btn-lg asociarse-btn w-50" wire:click="pasarPasoTres">
                                        Siguiente
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if($paso == 3)
                        <div class="card asociarse-card border-0 shadow-lg fade-step">
                            <div class="card-body p-5">

                                <div class="text-center mb-4">
                                    <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold">
                                        3
                                    </div>
                                    <p class="mt-2 mb-0 fw-semibold asociarse-subtitulo">
                                        Datos de domicilio
                                    </p>
                                </div>

                                <div class="text-center mb-4">
                                    <h2 class="fw-bold asociarse-titulo">
                                        Completá tu domicilio
                                    </h2>
                                    <p class="text-muted">
                                        Ingresá los datos de ubicación y vivienda.
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Insitución Municipal</label>
                                        <select class="form-control form-control-lg asociarse-input" wire:model.defer="institucion_id">
                                            @foreach ($instituciones as $item)
                                                <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Departamento</label>
                                        <select
                                            class="form-control form-control-lg asociarse-input"
                                            wire:model="departamento_id"
                                            wire:change="cambiarDepartamento">

                                            @foreach ($departamentos as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Distrito</label>
                                        <select
                                            class="form-control form-control-lg asociarse-input"
                                            wire:key="distrito-{{ $departamento_id }}"
                                            wire:model="distrito_id"
                                            wire:change="cambiarDistrito">

                                            @foreach ($distritos as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Ciudad</label>
                                        <select
                                            class="form-control form-control-lg asociarse-input"
                                            wire:key="ciudad-{{ $distrito_id }}"
                                            wire:model="ciudad_id">

                                            @foreach ($ciudades as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Barrio</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="barrio">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Domicilio</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="domicilio">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold">Descripción de vivienda</label>
                                        <input type="text" class="form-control asociarse-input" wire:model.defer="descripcion_vivienda">
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label class="form-label fw-semibold">Tipo de vivienda</label>
                                        <select class="form-control form-control-lg asociarse-input" wire:model.defer="tipo_vivienda">
                                            <option value="0">Seleccione</option>
                                            <option value="1">Propia</option>
                                            <option value="2">Alquilada</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-lg asociarse-btn-back w-50" wire:click="volverPasoDos">
                                        Retroceder
                                    </button>

                                    <button type="button" class="btn btn-lg asociarse-btn w-50" wire:click="pasarPasoCuatro">
                                        Siguiente
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if($paso == 4)
                        <div class="card asociarse-card border-0 shadow-lg fade-step">
                            <div class="card-body p-5">

                                <div class="text-center mb-4">
                                    <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold">
                                        4
                                    </div>
                                    <p class="mt-2 mb-0 fw-semibold asociarse-subtitulo">
                                        Datos familiares
                                    </p>
                                </div>

                                @foreach($familiares as $index => $f)
                                    <div class="border p-3 mb-3 rounded">

                                        <div class="mb-2">
                                            <label>Tipo</label>
                                            <select wire:model="familiares.{{ $index }}.tipo" class="form-control">
                                                <option value="1">Cónyuge</option>
                                                <option value="2">Hijo</option>
                                                <option value="3">Hijo</option>
                                                <option value="4">HERMANO/A</option>
                                                <option value="5">PADRE</option>
                                                <option value="6">MADRE</option>
                                                <option value="7">OTRO</option>
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label>Nombre</label>
                                            <input type="text" wire:model="familiares.{{ $index }}.nombre" class="form-control">
                                        </div>

                                        <div class="mb-2">
                                            <label>Apellido</label>
                                            <input type="text" wire:model="familiares.{{ $index }}.apellido" class="form-control">
                                        </div>

                                        <div class="mb-2">
                                            <label>CI</label>
                                            <input type="text" wire:model="familiares.{{ $index }}.ci" class="form-control">
                                        </div>

                                        <div class="mb-2">
                                            <label>Teléfono</label>
                                            <input type="text" wire:model="familiares.{{ $index }}.telefono" class="form-control">
                                        </div>

                                        <button type="button" wire:click="eliminarFamiliar({{ $index }})" class="btn btn-sm btn-danger">
                                            Eliminar
                                        </button>

                                    </div>
                                @endforeach

                                <button type="button" wire:click="agregarFamiliar" class="btn btn-outline-primary mb-3">
                                    + Agregar familiar
                                </button>

                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-lg asociarse-btn-back w-50" wire:click="volverPasoTres">
                                        Retroceder
                                    </button>

                                    <button type="button" class="btn btn-lg asociarse-btn w-50" wire:click="pasarPasoCinco">
                                        Siguiente
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if($paso == 5)
                        <div class="card asociarse-card border-0 shadow-lg fade-step">
                            <div class="card-body p-5">

                                <div class="text-center mb-4">
                                    <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold">
                                        4
                                    </div>
                                    <p class="mt-2 mb-0 fw-semibold asociarse-subtitulo">
                                        Ficha médica
                                    </p>
                                </div>

                                <div class="text-center mb-4">
                                    <h2 class="fw-bold asociarse-titulo">
                                        Datos médicos
                                    </h2>
                                    <p class="text-muted">
                                        Completá la información médica básica.
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold d-block">Enfermedad que padece</label>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="enfermedades" value="CANCER" id="cancer">
                                        <label class="form-check-label" for="cancer">Cáncer</label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="enfermedades" value="DIABETES" id="diabetes">
                                        <label class="form-check-label" for="diabetes">Diabetes</label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="enfermedades" value="PRESION ALTA" id="presion_alta">
                                        <label class="form-check-label" for="presion_alta">Presión alta</label>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Otra enfermedad</label>
                                    <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="otra_enfermedad" placeholder="Especifique si padece otra enfermedad">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Medicamentos que consume</label>
                                    <input type="text" class="form-control form-control-lg asociarse-input" wire:model.defer="medicamentos" placeholder="Ej: Losartán, Metformina, etc.">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Seguro médico</label>
                                    <select class="form-control form-control-lg asociarse-input" wire:model.defer="seguro_medico">
                                        <option value="NINGUNO">Ninguno</option>
                                        <option value="PARTICULAR">Particular</option>
                                        <option value="IPS">IPS</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-semibold">Observaciones</label>
                                    <input type="text" class="form-control asociarse-input" wire:model.defer="observacion_medica" placeholder="Ingrese alguna observación si corresponde">
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-lg asociarse-btn-back w-50" wire:click="volverPasoCuatro">
                                        Retroceder
                                    </button>

                                    <button type="button" class="btn btn-lg asociarse-btn w-50" wire:click="pasarPasoSeis">
                                        Siguiente
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if($paso == 6)
                        <div class="card asociarse-card border-0 shadow-lg fade-step">
                            <div class="card-body p-5">

                                <div class="text-center mb-4">
                                    <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold">
                                        6
                                    </div>
                                    <p class="mt-2 mb-0 fw-semibold asociarse-subtitulo">
                                        Documentación
                                    </p>
                                </div>

                                <div class="text-center mb-4">
                                    <h2 class="fw-bold asociarse-titulo">
                                        Adjuntar documentos
                                    </h2>
                                    <p class="text-muted">
                                        Subí foto del documento frente, reverso y una selfie para validar la solicitud.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Documento frente</label>
                                    <input type="file"
                                        wire:key="documento_frente"
                                        wire:model="documento_frente"
                                        class="form-control form-control-lg asociarse-input"
                                        accept=".jpg,.jpeg"
                                    >

                                    <div wire:loading wire:target="documento_frente" class="mt-1 text-primary">
                                        Cargando archivo...
                                    </div>

                                    <div wire:loading.remove wire:target="documento_frente">
                                        @if($documento_frente)
                                            <small class="text-success d-block mt-1">
                                                Archivo: {{ $documento_frente->getClientOriginalName() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Documento reverso</label>
                                    <input type="file"
                                        wire:key="documento_reverso"
                                        wire:model="documento_reverso"
                                        class="form-control form-control-lg asociarse-input"
                                        accept=".jpg,.jpeg"
                                    >

                                    <div wire:loading wire:target="documento_reverso" class="mt-1 text-primary">
                                        Cargando archivo...
                                    </div>


                                    <div wire:loading.remove wire:target="documento_reverso">
                                        @if($documento_reverso)
                                            <small class="text-success d-block mt-1">
                                                Archivo: {{ $documento_reverso->getClientOriginalName() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Selfie</label>
                                    <input type="file"
                                        wire:key="selfie"
                                        wire:model="selfie"
                                        class="form-control form-control-lg asociarse-input"
                                        accept=".jpg,.jpeg"
                                    >

                                    <div wire:loading wire:target="selfie" class="mt-1 text-primary">
                                        Cargando archivo...
                                    </div>

                                    <div wire:loading.remove wire:target="selfie">
                                        @if($selfie)
                                            <small class="text-success d-block mt-1">
                                                Archivo: {{ $selfie->getClientOriginalName() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="acepta_terminos" wire:model="acepta_terminos">
                                    <label class="form-check-label fw-semibold" for="acepta_terminos">
                                        Acepto los términos y condiciones de asociación.
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-lg asociarse-btn-back w-50" wire:click="volverPasoCinco">
                                        Retroceder
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-lg asociarse-btn w-50"
                                        wire:click="finalizarSolicitud"
                                        wire:loading.attr="disabled"
                                        wire:target="finalizarSolicitud">

                                        <span wire:loading.remove wire:target="finalizarSolicitud">
                                            Enviar solicitud
                                        </span>

                                        <span wire:loading wire:target="finalizarSolicitud" style="display:none;">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            Enviando...
                                        </span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if($paso == 7)
                        <div class="card asociarse-card border-0 shadow-lg fade-step">
                            <div class="card-body p-5 text-center">

                                <div class="asociarse-paso d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mb-3">
                                    ✓
                                </div>

                                <h2 class="fw-bold asociarse-titulo mb-3">
                                    Solicitud enviada correctamente
                                </h2>

                                <p class="text-muted mb-4">
                                    Su solicitud fue registrada y será revisada por la administración.
                                </p>

                                <div class="alert alert-success">
                                    <h5 class="fw-bold mb-1">Número de solicitud</h5>
                                    <h3 class="mb-0">
                                        {{ $solicitud_final_numero }}/{{ $solicitud_final_anio }}
                                    </h3>
                                </div>

                                <div class="mt-4 p-3 rounded" style="background:#f8f9fa; border:1px dashed #ced4da;">
                                    <h6 class="fw-bold mb-2">Envío por correo</h6>

                                    @if($correo_enviado)
                                        <p class="text-success mb-0">
                                            El PDF de la solicitud fue enviado al correo registrado.
                                        </p>
                                    @else
                                        <p class="text-muted mb-0">
                                            La solicitud fue registrada. El envío del PDF por correo se realizará posteriormente.
                                        </p>
                                    @endif
                                </div>

                                <div class="alert alert-info mt-4 mb-0">
                                    ¡Gracias por unirse! 😊 Su solicitud está en revisión.
                                    Muy pronto recibirá información en su correo sobre el estado, beneficios y novedades de la asociación.
                                </div>

                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</div>
