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

                <form action="{{route('entidad.obligaciones_post')}}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h4>Obligaciones</h4>
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="codigo">Codigo</label>
                                    <input name="codigo" id="codigo" type="text" class="form-control"  value="{{old('codigo')}}" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label for="descripcion">Descripcion</label>
                                    <input name="descripcion" id="descripcion" type="text" class="form-control" value="{{old('descripcion')}}" required>
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