@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/select2/select2.min.css')}}">
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Editar Persona</h3>
                    </div>
                    <div class="alert alert-warning mb-2" role="alert">
                        <strong>Importante:</strong> Las personas registradas desde este módulo son para la gestión de
                        <strong>órdenes de pago</strong> a terceros (personas físicas o jurídicas). Estos registros
                        <strong>no serán considerados socios ni asociados</strong> y no podrán realizar aportes ni acceder
                        a los beneficios destinados a los asociados.
                    </div>
                </div>
                @include('varios.mensaje')
                <form id="form_general" action="{{route('persona.update', $persona)}}" method="post"
                    onsubmit="
                        if (!this.checkValidity()) return true;

                        if (this.dataset.enviando === '1') return false;
                        this.dataset.enviando = '1';

                        document.getElementById('btnEnviar').disabled = true;
                        document.getElementById('btnEnviar').innerText = 'Enviando...';
                    "
                >

                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">

                                <div class="form-group col-md-3">
                                    <label for="documento">Documento</label>
                                    <input name="documento" id="documento" type="text" class="form-control" value="{{old('documento', $persona->documento)}}" onkeyup="punto_decimal(this)" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="ruc">RUC</label>
                                    <input name="ruc" id="ruc" type="text" class="form-control" value="{{old('ruc', $persona->ruc)}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Nombre</label>
                                    <input name="nombre" id="nombre" type="text" class="form-control" value="{{old('nombre', $persona->nombre)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Apellido</label>
                                    <input name="apellido" id="apellido" type="text" class="form-control" value="{{old('apellido', $persona->apellido)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="tipo_persona_id">Tipo</label>
                                    <select name="tipo_persona_id" id="tipo_persona_id" class="form-control basic">
                                        @foreach ($tipoPersona as $item)
                                            <option {{ $persona->tipo_persona_id == $item->id ? 'selected' : '' }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="departamento_id">Departamento</label>
                                    <select name="departamento_id" id="departamento_id" class="form-control basic">
                                        @foreach ($departamento as $item)
                                            <option {{ (old('departamento_id', $persona->departamento_id) == $item->id ? 'selected' : '' ) }}  value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="distrito_id">Distrito</label>
                                    <select name="distrito_id" id="distrito_id" class="form-control basic">
                                        @foreach ($distrito as $item)
                                            <option {{ (old('distrito_id', $persona->distrito_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="ciudad_id">Ciudad</label>
                                    <select name="ciudad_id" id="ciudad_id" class="form-control basic">
                                        @foreach ($ciudad as $item)
                                            <option {{ (old('ciudad_id', $persona->ciudad_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="barrio">Barrio</label>
                                    <input name="barrio" id="barrio" type="text" class="form-control" value="{{old('barrio', $persona->barrio)}}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="direccion">Direccion</label>
                                    <input name="direccion" id="direccion" type="text" class="form-control" value="{{old('direccion', $persona->direccion)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="celular">Celular</label>
                                    <input name="celular" id="celular" type="text" class="form-control" value="{{old('celular', $persona->celular)}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="email">Email</label>
                                    <input name="email" id="email" type="text" class="form-control" value="{{old('email', $persona->email)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="sexo_id">Sexo</label>
                                    <select name="sexo_id" id="sexo_id" class="form-control">
                                        @foreach ($sexo as $item)
                                            <option {{ (old('sexo_id', $persona->sexo_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="estado_civil_id">Estado Civil</label>
                                    <select name="estado_civil_id" id="estado_civil_id" class="form-control">
                                        @foreach ($estado_civil as $item)
                                            <option {{ (old('estado_civil_id', $persona->estado_civil_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success">
                                    Grabar
                                </button>
                            </div>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection


@section('js')
    <script src="{{asset('js/asociado.js')}}"></script>
@endsection
