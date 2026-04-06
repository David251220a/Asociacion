@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/select2/select2.min.css')}}">
    <link href="{{asset('assets/css/components/custom-modal.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .img-doc {
            max-height: 280px;
            width: 100%;
            object-fit: cover;
        }
    </style>
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Editar Asociado</h3>
                    </div>
                </div>
                @include('varios.mensaje')
                <form action="{{route('asociado.update', $data)}}" method="post" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="numero_socio">Nro Socio</label>
                                    <input name="numero_socio" id="numero_socio" type="text" class="form-control" onkeyup="punto_decimal(this)" value="{{old('numero_socio', number_format($data->numero_socio, 0, ".", "."))}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="fecha_admision">Fecha Admision</label>
                                    <input name="fecha_admision" id="fecha_admision" type="date" class="form-control" value="{{old('fecha_admision', $data->fecha_admision)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="tipo_asociado_id">Tipo Asociado</label>
                                    <select name="tipo_asociado_id" id="tipo_asociado_id" class="form-control">
                                        @foreach ($tipo_asociado as $item)
                                            <option {{ (old('tipo_asociado_id', $data->tipo_asociado_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

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
                                    <label for="fecha_nacimiento">Fecha Nacimiento</label>
                                    <input name="fecha_nacimiento" id="fecha_nacimiento" type="date" value="{{old('fecha_nacimiento', $persona->fecha_nacimiento)}}" class="form-control">
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
                                    <label for="tipo_vivienda_id">Tipo Vivienda</label>
                                    <select name="tipo_vivienda_id" id="tipo_vivienda_id" class="form-control">
                                        @foreach ($tipo_vivienda as $item)
                                            <option {{ (old('tipo_vivienda_id', $persona->tipo_vivienda_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="vivienda">Descripcion Vivienda</label>
                                    <input name="vivienda" id="vivienda" type="text" class="form-control" value="{{old('vivienda', $persona->vivienda)}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="documento_frente">Documento Frente</label>
                                    <input name="documento_frente" id="documento_frente" type="file" class="form-control" accept=".jpg,.jpeg" value="{{old('documento_frente')}}">
                                    <div class="card">
                                        <img src="{{ ($persona->documento_frente && Storage::exists($persona->documento_frente)) 
                                        ? Storage::url($persona->documento_frente) 
                                        : Storage::url('iconos/user.jpg') }}" 
                                        class="card-img-top img-doc" alt="widget-card-2">
                                    </div>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="documento_reverso">Documento Reverso</label>
                                    <input name="documento_reverso" id="documento_reverso" type="file" class="form-control" accept=".jpg,.jpeg" value="{{old('documento_reverso')}}">
                                    <div class="card">
                                        <img src="{{ ($persona->documento_reverso && Storage::exists($persona->documento_reverso)) 
                                        ? Storage::url($persona->documento_reverso) 
                                        : Storage::url('iconos/user.jpg') }}" 
                                        class="card-img-top img-doc" alt="widget-card-2">
                                    </div>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="selfi">Selfi</label>
                                    <input name="selfi" id="selfi" type="file" class="form-control" accept=".jpg,.jpeg" value="{{old('selfi')}}">
                                    <div class="card">
                                        <img src="{{ ($persona->selfi && Storage::exists($persona->selfi)) 
                                        ? Storage::url($persona->selfi) 
                                        : Storage::url('iconos/user.jpg') }}" 
                                        class="card-img-top img-doc" alt="widget-card-2">
                                    </div>
                                </div>

                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="estado_id">Estado</label>
                                    <select name="estado_id" id="estado_id" class="form-control">
                                        @foreach ($estado as $item)
                                            <option {{ (old('estado_id', $persona->estado_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="motivo">Motivo Baja</label>
                                    <select name="motivo" id="motivo" class="form-control">
                                        <option {{ (old('motivo', $data->motivo) == 1 ? 'selected' : '' ) }} value="1">SIN ESPECIFICAR</option>
                                        <option {{ (old('motivo', $data->motivo) == 2 ? 'selected' : '' ) }} value="2">FALLECIMIENTO</option>
                                        <option {{ (old('motivo', $data->motivo) == 3 ? 'selected' : '' ) }} value="3">RETIRO VOLUNTARIO</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="motivo_baja">Descripcion Baja</label>
                                    <input name="motivo_baja" id="motivo_baja" type="text" class="form-control" value="{{old('motivo_baja', $data->motivo_baja_otro)}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="fecha_baja">Fecha Baja</label>
                                    <input name="fecha_baja" id="fecha_baja" type="date" class="form-control" value="{{old('fecha_baja', $data->fecha_baja)}}">
                                </div>
                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success">Actualizar</button>
                            </div>

                        </div>
                    </div>
                </form>
                <br>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="mb-0">Familiares</h4>
                    <a href="{{route('familiar.create', $persona)}}" class="btn btn-info">Agregar</a>
                </div>

                <div class="row mt-2">
                    <div  class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">
                                <thead>
                                    <tr>
                                        <th class="">Documento</th>
                                        <th>Tipo Familiar</th>
                                        <th class="">Familiar</th>
                                        <th class="">Celular</th>
                                        <th class="text-center">Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($persona->familiares as $item)
                                        <tr>
                                            <td class="">
                                                {{number_format($item->documento, 0, ".", ".")}}
                                            </td>
                                            <td>{{$item->tipo->descripcion}}</td>
                                            <td>
                                                {{$item->nombre}} {{$item->apellido}}
                                            </td>
                                            <td>{{$item->celular}}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger mr-3" data-toggle="modal" data-target="#exampleModalCenter_{{ $item->id }}">
                                                    <svg 
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                                        stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line>
                                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                                    </svg>
                                                </button>
                                                <div class="modal fade" 
                                                    id="exampleModalCenter_{{ $item->id }}" 
                                                    tabindex="-1" 
                                                    role="dialog"
                                                    data-backdrop="static"
                                                    data-keyboard="false"
                                                    aria-labelledby="modalTitle_{{ $item->id }}" 
                                                    aria-hidden="true">

                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalTitle_{{ $item->id }}">
                                                                    Eliminar Familiar
                                                                </h5>
                                                            </div>

                                                            <div class="modal-body">
                                                                ¿Está seguro que desea eliminar este familiar?
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-primary" data-dismiss="modal">
                                                                    Cancelar
                                                                </button>
                                                                <form action="{{route('familiar.delete', $item)}}" method="POST">
                                                                    @csrf
                                                                    <button type="submit"  class="btn btn-danger">
                                                                        Eliminar
                                                                    </button>
                                                                </form>
                                                                
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <a href="{{route('familiar.edit', $item)}}" class="mr-3">
                                                    <svg 
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                                        stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <th>
                                        <td colspan="5"></td>
                                    </th>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('js')
    <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('plugins/select2/custom-select2.js')}}"></script>
    <script src="{{asset('js/asociado.js')}}"></script>
@endsection