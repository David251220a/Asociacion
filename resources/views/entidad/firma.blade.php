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

                <form action="{{route('entidad.firma_post')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h4>Firma Digital</h4>
                            <div class="form-row mb-2">
                                <div class="form-group col-md-6">
                                    <label for="file">Archivo .p12</label>
                                    <input name="file" id="file" type="file" class="form-control" accept=".p12">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="pass_firma">Contraseña Firma</label>
                                    <input name="pass_firma" id="pass_firma" type="text" class="form-control" value="{{$data->pass_firma}}" required>
                                </div>

                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success">
                                    Actualizar
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
    <script>
        let enviado = false;
        document.querySelector('form').addEventListener('submit', function (e) {

            if (enviado) {
                e.preventDefault();
                return false;
            }

            enviado = true;

            let btn = document.getElementById('btnEnviar');
            btn.disabled = true;
            btn.innerText = 'Enviando...';

        });
    </script>
@endsection