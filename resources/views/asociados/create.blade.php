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
                        <h3 class="mb-0">Crear Asociado</h3>
                    </div>
                </div>
                @include('varios.mensaje')
                <form id="form_general" action="{{route('asociado.store')}}" method="post" enctype="multipart/form-data" 
                        onsubmit="
                        if (this.dataset.enviando === '1') return false;
                        this.dataset.enviando = '1';
                        document.getElementById('btnEnviar').disabled = true;
                        document.getElementById('btnEnviar').innerText = 'Enviando...';
                    ">
                
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="numero_socio">Nro Socio</label>
                                    <input name="numero_socio" id="numero_socio" type="text" class="form-control" onkeyup="punto_decimal(this)" value="{{old('numero_socio')}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="fecha_admision">Fecha Admision</label>
                                    <input name="fecha_admision" id="fecha_admision" type="date" class="form-control" value="{{old('fecha_admision')}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="tipo_asociado_id">Tipo Asociado</label>
                                    <select name="tipo_asociado_id" id="tipo_asociado_id" class="form-control">
                                        @foreach ($tipo_asociado as $item)
                                            <option {{ (old('tipo_asociado_id') == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="documento">Documento</label>
                                    <input name="documento" id="documento" type="text" class="form-control" value="{{old('documento')}}" onkeyup="punto_decimal(this)" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="ruc">RUC</label>
                                    <input name="ruc" id="ruc" type="text" class="form-control" value="{{old('ruc')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Nombre</label>
                                    <input name="nombre" id="nombre" type="text" class="form-control" value="{{old('nombre')}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Apellido</label>
                                    <input name="apellido" id="apellido" type="text" class="form-control" value="{{old('apellido')}}" required>
                                </div>
                                
                                <div class="form-group col-md-3">
                                    <label for="fecha_nacimiento">Fecha Nacimiento</label>
                                    <input name="fecha_nacimiento" id="fecha_nacimiento" type="date" value="{{old('fecha_nacimiento')}}" class="form-control">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="sexo_id">Sexo</label>
                                    <select name="sexo_id" id="sexo_id" class="form-control">
                                        @foreach ($sexo as $item)
                                            <option {{ (old('sexo_id') == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="estado_civil_id">Estado Civil</label>
                                    <select name="estado_civil_id" id="estado_civil_id" class="form-control">
                                        @foreach ($estado_civil as $item)
                                            <option {{ (old('estado_civil_id') == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="departamento_id">Departamento</label>
                                    <select name="departamento_id" id="departamento_id" class="form-control basic">
                                        @foreach ($departamento as $item)
                                            <option {{ (old('departamento_id') == $item->id ? 'selected' : '' ) }}  value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="distrito_id">Distrito</label>
                                    <select name="distrito_id" id="distrito_id" class="form-control basic">
                                        @foreach ($distrito as $item)
                                            <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="ciudad_id">Ciudad</label>
                                    <select name="ciudad_id" id="ciudad_id" class="form-control basic">
                                        @foreach ($ciudad as $item)
                                            <option value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="barrio">Barrio</label>
                                    <input name="barrio" id="barrio" type="text" class="form-control" value="{{old('barrio')}}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="direccion">Direccion</label>
                                    <input name="direccion" id="direccion" type="text" class="form-control" value="{{old('direccion')}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="celular">Celular</label>
                                    <input name="celular" id="celular" type="text" class="form-control" value="{{old('celular')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="email">Email</label>
                                    <input name="email" id="email" type="text" class="form-control" value="{{old('email')}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="tipo_vivienda_id">Tipo Vivienda</label>
                                    <select name="tipo_vivienda_id" id="tipo_vivienda_id" class="form-control">
                                        @foreach ($tipo_vivienda as $item)
                                            <option {{ (old('tipo_vivienda_id') == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="vivienda">Descripcion Vivienda</label>
                                    <input name="vivienda" id="vivienda" type="text" class="form-control" value="{{old('vivienda')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="documento_frente">Documento Frente</label>
                                    <input name="documento_frente" id="documento_frente" type="file" class="form-control" accept=".jpg,.jpeg" value="{{old('documento_frente')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="documento_reverso">Documento Reverso</label>
                                    <input name="documento_reverso" id="documento_reverso" type="file" class="form-control" accept=".jpg,.jpeg" value="{{old('documento_reverso')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="selfi">Selfi</label>
                                    <input name="selfi" id="selfi" type="file" class="form-control" accept=".jpg,.jpeg" value="{{old('selfi')}}">
                                </div>

                            </div>

                            <h4>Familiares</h4>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="documento_conyuge">Documento Conyuge</label>
                                    <input name="documento_conyuge" id="documento_conyuge" type="text" class="form-control" value="{{old('documento_conyuge')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="nombre_conyuge">Nombre Conyuge</label>
                                    <input name="nombre_conyuge" id="nombre_conyuge" type="text" class="form-control" value="{{old('nombre_conyuge')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="apellido_conyuge">Apellido Conyuge</label>
                                    <input name="apellido_conyuge" id="apellido_conyuge" type="text" class="form-control" value="{{old('apellido_conyuge')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="celular_conyuge">Celular Conyuge</label>
                                    <input name="celular_conyuge" id="celular_conyuge" type="text" class="form-control" value="{{old('celular_conyuge')}}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="documento_hijo1">Documento Hijo/a</label>
                                    <input name="documento_hijo1" id="documento_hijo1" type="text" class="form-control" value="{{old('documento_hijo1')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="nombre_hijo1">Nombre Hijo/a</label>
                                    <input name="nombre_hijo1" id="nombre_hijo1" type="text" class="form-control" value="{{old('nombre_hijo1')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="apellido_hijo1">Apellido Hijo/a</label>
                                    <input name="apellido_hijo1" id="apellido_hijo1" type="text" class="form-control" value="{{old('apellido_hijo1')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="celular_hijo1">Celular Hijo/a</label>
                                    <input name="celular_hijo1" id="celular_hijo1" type="text" class="form-control" value="{{old('celular_hijo1')}}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="documento_hijo2">Documento Hijo/a</label>
                                    <input name="documento_hijo2" id="documento_hijo2" type="text" class="form-control" value="{{old('documento_hijo2')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="nombre_hijo2">Nombre Hijo/a</label>
                                    <input name="nombre_hijo2" id="nombre_hijo2" type="text" class="form-control" value="{{old('nombre_hijo2')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="apellido_hijo2">Apellido Hijo/a</label>
                                    <input name="apellido_hijo2" id="apellido_hijo2" type="text" class="form-control" value="{{old('apellido_hijo2')}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="celular_hijo2">Celular Hijo/a</label>
                                    <input name="celular_hijo2" id="celular_hijo2" type="text" class="form-control" value="{{old('celular_hijo2')}}">
                                </div>
                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success"
                                    onclick="
                                        if (this.dataset.clicked === '1') return false;
                                        this.dataset.clicked = '1';
                                        this.disabled = true;
                                        this.innerText = 'Enviando...';
                                        this.form.requestSubmit();
                                        return false;
                                    ">
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
    <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('plugins/select2/custom-select2.js')}}"></script>
    <script src="{{asset('js/asociado.js')}}"></script>
@endsection
