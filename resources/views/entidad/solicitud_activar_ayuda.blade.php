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
                        <h3 class="mb-0">Editar Ayuda Social</h3>
                    </div>
                </div>
                @include('varios.mensaje')
                <form id="form_general" action="{{route('entidad_soli.solicitud_ayuda_social_post')}}" method="post"
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
                                    <label for="activar">Activo</label>
                                    <select name="activar" id="activar" class="form-control">
                                        <option {{ (old('activar', $data->activo_ayuda_social) == 1 ? 'selected' : '' ) }} value="1">ACTIVO</option>
                                        <option {{ (old('activar', $data->activo_ayuda_social) == 0 ? 'selected' : '' ) }} value="0">INACTIVO</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="limite">Limite</label>
                                    <input name="limite" id="limite" type="text" class="form-control" value="{{old('limite', $data->limite_ayuda_social)}}" onkeyup="punto_decimal(this)" required>
                                </div>

                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success">
                                    Editar
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
