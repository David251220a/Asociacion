@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                <form action="{{route('entidad.actividades_editar_post', $data)}}" method="POST"
                    onsubmit="
                    if (this.dataset.enviando === '1') return false;
                    this.dataset.enviando = '1';
                    document.getElementById('btnEnviar').disabled = true;
                    document.getElementById('btnEnviar').innerText = 'Enviando...';"
                >
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h4>Editar Actividad Economica</h4>
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="codigo">Codigo</label>
                                    <input name="codigo" id="codigo" type="text" class="form-control"  value="{{old('codigo', $data->codigo)}}" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label for="descripcion">Descripcion</label>
                                    <input name="descripcion" id="descripcion" type="text" class="form-control" value="{{old('descripcion', $data->descripcion)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="estado_id">Codigo</label>
                                    <select name="estado_id" id="estado_id" class="form-control">
                                        <option {{ (old('estado_id', $data->estado_id) == 1 ? 'selected' : '') }} value="1">ACTIVO</option>
                                        <option {{ (old('estado_id', $data->estado_id) == 2 ? 'selected' : '') }} value="2">INACTIVO</option>
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

@endsection
