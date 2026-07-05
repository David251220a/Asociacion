@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/scrollspyNav.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/components/tabs-accordian/custom-tabs.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')


    <div class="col-lg-12 col-12 layout-spacing">

        @include('varios.mensaje')

        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>Solicitud:{{ str_pad($data->numero_solicitud, 7, '0', STR_PAD_LEFT) }}/{{$data->anio}}</h4>
                        @if($data->aprobado == 1)
                            <div class="alert alert-success mt-3 text-center fw-bold">
                                ✔ SOLICITUD APROBADA
                            </div>

                        @elseif($data->aprobado == 2)
                            <div class="alert alert-danger mt-3 text-center fw-bold">
                                ✖ SOLICITUD RECHAZADA
                            </div>

                        @else
                            <div class="alert alert-warning mt-3 text-center fw-bold">
                                ⏳ SOLICITUD PENDIENTE
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="widget-content widget-content-area border-tab">

                <ul class="nav nav-tabs mt-3" id="border-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="border-home-tab" data-toggle="tab" href="#border-home" role="tab" aria-controls="border-home" aria-selected="true">
                            <svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            Datos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="border-profile-tab" data-toggle="tab" href="#border-profile" role="tab" aria-controls="border-profile" aria-selected="false">
                            <svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2">
                                </path><circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Familiares
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="border-contact-tab" data-toggle="tab" href="#border-contact" role="tab" aria-controls="border-contact" aria-selected="false">
                            <svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            Ficha Medica
                        </a>
                    </li>
                </ul>

                <div class="tab-content mb-4" id="border-tabsContent">
                    <div class="tab-pane fade show active" id="border-home" role="tabpanel" aria-labelledby="border-home-tab">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Documento</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->documento }}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Nombre y Apellido</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->nombre . " " . $data->apellido}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Fecha Nacimiento</label>
                                <input type="date" class="form-control form-control-lg asociarse-input" value="{{ $data->fecha_nacimiento}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Sexo</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->sexo->descripcion}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Estado Civil</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->estado_civil->descripcion}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Departamento</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->departamento->descripcion}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Distrito</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->distrito->descripcion}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Ciudad</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->ciudad->descripcion}}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Direccion</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->direccion}}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Barrio</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->barrio}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Tipo Vivienda</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->tipo_vivienda->descripcion}}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Descripcion Vivienda</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->vivienda}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Tipo Asociado</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->tipo_asociado->descripcion}}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Insitucion</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->institucion->descripcion}}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->email}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $data->celular}}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Fecha Solicitud</label>
                                <input type="date" class="form-control form-control-lg asociarse-input" value="{{ $data->fecha_solicitud }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label for="documento_frente">Documento Frente</label>
                                <div class="card">
                                    <img src="{{ ($data->documento_frente && Storage::exists($data->documento_frente))
                                    ? Storage::url($data->documento_frente)
                                    : Storage::url('iconos/user.jpg') }}"
                                    class="card-img-top img-doc" alt="widget-card-2">
                                </div>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="documento_reverso">Documento Reverso</label>
                                <div class="card">
                                    <img src="{{ ($data->documento_reverso && Storage::exists($data->documento_reverso))
                                    ? Storage::url($data->documento_reverso)
                                    : Storage::url('iconos/user.jpg') }}"
                                    class="card-img-top img-doc" alt="widget-card-2">
                                </div>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="selfi">Selfi</label>
                                <div class="card">
                                    <img src="{{ ($data->selfi && Storage::exists($data->selfi))
                                    ? Storage::url($data->selfi)
                                    : Storage::url('iconos/user.jpg') }}"
                                    class="card-img-top img-doc" alt="widget-card-2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="border-profile" role="tabpanel" aria-labelledby="border-profile-tab">
                        <div class="row">
                            @if ($data->familiares->count() > 0)
                                @foreach ($data->familiares as $item)
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label fw-semibold">Tipo Familiar</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $item->tipo_familia->descripcion }}" readonly>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label fw-semibold">Documento</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $item->documento }}" readonly>
                                    </div>
                                    <div class="col-md-5 mb-5">
                                        <label class="form-label fw-semibold">Nombre y Apellido</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $item->nombre . " " . $item->apellido }}" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-semibold">Celular</label>
                                        <input type="text" class="form-control form-control-lg asociarse-input" value="{{ $item->celular }}" readonly>
                                    </div>
                                @endforeach
                            @else
                                <p>No se registro familiares</p>
                            @endif


                        </div>
                    </div>

                    <div class="tab-pane fade" id="border-contact" role="tabpanel" aria-labelledby="border-contact-tab">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <h4>Observacion</h4>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="">Enfermedad que padece</label>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="cancer">Cancer</label>
                                        <input type="checkbox" name="cancer" id="cancer" {{( $data->ficha_medica?->cancer == 1 ? 'checked' : '' )}} @readonly(true)>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="diabetes">Diabetes</label>
                                        <input type="checkbox" name="diabetes" id="diabetes" {{( $data->ficha_medica?->diabetes == 1 ? 'checked' : '' )}} @readonly(true)>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="presion_alta">Presion Alta</label>
                                        <input type="checkbox" name="presion_alta" id="presion_alta" {{( $data->ficha_medica?->presion_alta == 1 ? 'checked' : '' )}} @readonly(true) >
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="otro_enfermedad">Otra Enfermedad</label>
                                        <input type="text" name="otro_enfermedad" id="otro_enfermedad" class="form-control" value="{{$data->ficha_medica?->otro}}" readonly>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="otro_enfermedad">Medicamento que consume</label>
                                        <input type="text" name="medicamentos" id="medicamentos" class="form-control" value="{{$data->ficha_medica?->medicamentos}}" readonly>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="documento_conyuge">Seguro Medico</label>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="seguro_particular">Particular</label>
                                        <input type="checkbox" name="seguro_particular" id="seguro_particular" {{( $data->ficha_medica?->seguro_particular == 1 ? 'checked' : '' )}} @readonly(true)>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="seguro_ips">Diabetes</label>
                                        <input type="checkbox" name="seguro_ips" id="seguro_ips" {{( $data->ficha_medica?->seguro_ips == 1 ? 'checked' : '' )}} @readonly(true)>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="seguro_ninguno">Ninguno</label>
                                        <input type="checkbox" name="seguro_ninguno" id="seguro_ninguno" {{( $data->ficha_medica?->seguro_ninguno == 1 ? 'checked' : '' )}}  @readonly(true)>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="observacion">Observación</label>
                                        <input type="text" name="observacion" id="observacion" class="form-control" value="{{$data->ficha_medica?->observacion}}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($data->aprobado == 0)
                    <form action="{{ route('solicitud.store', $data) }}" method="POST"
                        onsubmit="
                            if (!this.checkValidity()) return true;

                            if (this.dataset.enviando === '1') return false;
                            this.dataset.enviando = '1';

                            document.getElementById('btnEnviar').disabled = true;
                            document.getElementById('btnEnviar').innerText = 'Enviando...';
                        "
                    >
                        @csrf
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="1" {{ old('estado') == 1 ? 'selected' : '' }}>APROBACION</option>
                                        <option value="2" {{ old('estado') == 2 ? 'selected' : '' }}>RECHAZO</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="desde">Fecha Inicio Descuento</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="desde">Acta</label>
                                    <input type="number" name="acta" id="acta" value="{{ $entidad->acta }}" class="form-control">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="motivo">Motivo Rechazo</label>
                                    <input type="text" name="motivo" id="motivo" class="form-control">
                                </div>

                            </div>
                            <h4>Miembros Presentes</h4>
                            <div class="row">
                                @foreach ($miembros as $item)
                                    <div class="form-group col-md-4">
                                        <label for="miembros">{{ $tipos[$item->tipo] }}</label>
                                        <input type="text" value="{{ $item->nombre . " " . $item->apellido }}" class="form-control" @readonly(true)>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <button id="btnEnviar" type="submit" class="btn btn-success">
                                    Grabar
                                </button>
                            </div>
                        </div>
                    </form>
                @endif



            </div>

        </div>
    </div>


@endsection


@section('js')
@endsection
