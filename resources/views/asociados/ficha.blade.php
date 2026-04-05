@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Ficha Medica Asociado</h3>
                    </div>
                </div>
                @include('varios.mensaje')
                <form action="{{route('ficha_medica.store', $data)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">
                                <div class="form-group col-md-3">
                                    <label for="numero_socio">Nro Socio</label>
                                    <input type="text" class="form-control" value="{{number_format($data->numero_socio, 0, ".", ".")}}" readonly>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="documento">Documento</label>
                                    <input type="text" class="form-control" value="{{$data->persona->documento}}" readonly>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" value="{{$data->persona->nombre}}" readonly>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Apellido</label>
                                    <input type="text" class="form-control" value="{{$data->persona->apellido}}" readonly>
                                </div>

                            </div>

                            <h4>Observacion</h4>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="">Enfermedad que padece</label>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="cancer">Cancer</label>
                                    <input type="checkbox" name="cancer" id="cancer" {{( $data->ficha_medica?->cancer == 1 ? 'checked' : '' )}} >
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="diabetes">Diabetes</label>
                                    <input type="checkbox" name="diabetes" id="diabetes" {{( $data->ficha_medica?->diabetes == 1 ? 'checked' : '' )}} >
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="presion_alta">Presion Alta</label>
                                    <input type="checkbox" name="presion_alta" id="presion_alta" {{( $data->ficha_medica?->presion_alta == 1 ? 'checked' : '' )}} >
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="otro_enfermedad">Otra Enfermedad</label>
                                    <input type="text" name="otro_enfermedad" id="otro_enfermedad" class="form-control" value="{{$data->ficha_medica?->otro}}">
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="otro_enfermedad">Medicamento que consume</label>
                                    <input type="text" name="medicamentos" id="medicamentos" class="form-control" value="{{$data->ficha_medica?->medicamentos}}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="documento_conyuge">Seguro Medico</label>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="seguro_particular">Particular</label>
                                    <input type="checkbox" name="seguro_particular" id="seguro_particular" {{( $data->ficha_medica?->seguro_particular == 1 ? 'checked' : '' )}} >
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="seguro_ips">Diabetes</label>
                                    <input type="checkbox" name="seguro_ips" id="seguro_ips" {{( $data->ficha_medica?->seguro_ips == 1 ? 'checked' : '' )}} >
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="seguro_ninguno">Ninguno</label>
                                    <input type="checkbox" name="seguro_ninguno" id="seguro_ninguno" {{( $data->ficha_medica?->seguro_ninguno == 1 ? 'checked' : '' )}} >
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="observacion">Observación</label>
                                    <input type="text" name="observacion" id="observacion" class="form-control" value="{{$data->ficha_medica?->observacion}}">
                                </div>
                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success">Grabar</button>
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
