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

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 filtered-list-search mx-auto">
                        <div class="alert alert-icon-left alert-light-success mb-4" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12" y2="17"></line></svg>
                            Crear Establecimiento
                        </div>
                    </div>
                </div>

                <form action="{{route('establecimiento.store')}}" method="POST">
                    @csrf
                    
                    @livewire('establecimiento-create')
                    
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="punto">Punto</label>
                                    <input name="punto" id="punto" type="text" class="form-control" value="{{old('punto')}}" placeholder="001"  required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="numero_casa">Numero Casa</label>
                                    <input name="numero_casa" id="numero_casa" type="text" class="form-control" value="{{old('numero_casa')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="telefono">Telefono</label>
                                    <input name="telefono" id="telefono" type="text" class="form-control" value="{{old('telefono')}}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="descripcion">Establecimiento</label>
                                    <input name="descripcion" id="descripcion" type="text" class="form-control" value="{{old('descripcion')}}">
                                </div>


                                <div class="form-group col-md-6">
                                    <label for="direccion">Direccion</label>
                                    <input name="direccion" id="direccion" type="text" class="form-control" value="{{old('direccion')}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="sucursal">Factura Sucursal</label>
                                    <input name="sucursal" id="sucursal" type="text" class="form-control" value="{{old('sucursal')}}" placeholder="001">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="general">Factura General</label>
                                    <input name="general" id="general" type="text" class="form-control" value="{{old('general')}}" placeholder="001">
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