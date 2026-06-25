@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <h3 class="mb-2 mb-md-0">
                            Orden de Pago: {{ str_pad($data->numero, 7, '0', STR_PAD_LEFT) }}/{{ $data->anio }}
                        </h3>

                        <a href="{{route('pdf.orden', $data)}}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-print"></i> Imprimir
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-row mb-2">
                            <div class="form-group col-md-3">
                                <label for="documento">Documento</label>
                                <input type="text" wire:blur="buscarPersona" class="form-control" value="{{$data->persona->documento}}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="ruc">RUC</label>
                                <input type="text" class="form-control" value="{{ $data->persona->ruc }}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="beneficiario">Beneficiario</label>
                                <input type="text" class="form-control" value="{{ $data->persona->nombre . ' ' . $data->persona->apellido }}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="tipo_egreso_id">Tipo Egreso</label>
                                <input type="text" class="form-control" value="{{ $data->tipo_egreso->descripcion }}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="concepto">Concepto</label>
                                <input type="text" class="form-control" value="{{$data->concepto}}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="observacion">Observacion</label>
                                <input type="text" class="form-control" value="{{$data->descripcion}}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="total">Total</label>
                                <input type="text" class="form-control text-right" value="{{number_format($data->total, 0, ',', '.')}}" readonly style="color: black; font-weight:bold">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="estado">Estado</label>
                                @php
                                    $descrip = '';
                                    if ($data->estado_pago == 0) {
                                        $descrip = 'PENDIENTE';
                                    }
                                    if ($data->estado_pago == 1) {
                                        $descrip = 'PAGADO';
                                    }
                                    if ($data->estado_pago == 2) {
                                        $descrip = 'ANULADO';
                                    }
                                @endphp
                                <input type="text" class="form-control text-right" value="{{$descrip}}" readonly style="color: black; font-weight:bold">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h3>Forma de Pago</h3>
                        @foreach ($data->pagos as $item)
                            <div class="form-row mb-2">
                                <div class="form-group col-md-2">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" value="{{ $item->fecha_pago }}" readonly style="color: black; font-weight:bold">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="fecha">Forma de Pago</label>
                                    <input type="text" class="form-control" value="{{ $item->forma->descripcion }}" readonly style="color: black; font-weight:bold">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="fecha">Banco</label>
                                    <input type="text" class="form-control" value="{{ $item->banco->descripcion }}" readonly style="color: black; font-weight:bold">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="fecha">Nro. Comprobante</label>
                                    <input type="text" class="form-control" value="{{ $item->numero_comprobante }}" readonly style="color: black; font-weight:bold">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="fecha">Monto</label>
                                    <input type="text" class="form-control" value="{{number_format($item->monto, 0, ',', '.')}}" readonly style="color: black; font-weight:bold">
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('js')
@endsection
