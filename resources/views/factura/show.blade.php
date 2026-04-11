@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/apps/invoice-preview.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @include('varios.mensaje')

    <div class="row invoice layout-top-spacing layout-spacing">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            
            <div class="doc-container">

                <div class="row">

                    <div class="col-xl-9">

                        <div class="invoice-container">
                            <div class="invoice-inbox">
                                
                                <div id="ct" class="">
                                    
                                    <div class="invoice-00001">
                                        <div class="content-section">

                                            <div class="inv--head-section inv--detail-section">
                                            
                                                <div class="row">
                                                
                                                    <div class="col-sm-6 col-12 mr-auto">
                                                        <div class="d-flex">
                                                            <img class="company-logo" src="assets/img/cork-logo.png" alt="company">
                                                            <h3 class="in-heading align-self-center">AJUPEM</h3>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 text-sm-right">
                                                        <p class="inv-list-number"><span class="inv-title">Factura : </span> 
                                                            <span class="inv-number">
                                                                {{$factura->factura_sucursal}}-{{$factura->factura_general}}-{{str_pad((string)$factura->factura_numero, 7, '0', STR_PAD_LEFT)}}
                                                            </span>
                                                        </p>
                                                    </div>

                                                    <div class="col-sm-6 align-self-center mt-3">
                                                        <p class="inv-street-addr">{{$entidad->razon_social}}</p>
                                                        <p class="inv-email-address">{{$entidad->email}}</p>
                                                        <p class="inv-email-address">{{$entidad->telefono}}</p>
                                                    </div>
                                                    <div class="col-sm-6 align-self-center mt-3 text-sm-right">
                                                        <p class="inv-created-date"><span class="inv-title">Factura Fecha : </span> <span class="inv-date">{{$factura->fecha_factura}}</span></p>
                                                    </div>
                                                
                                                </div>
                                                
                                            </div>

                                            <div class="inv--detail-section inv--customer-detail-section">

                                                <div class="row">

                                                    <div class="col-xl-8 col-lg-7 col-md-6 col-sm-4 align-self-center">
                                                        <p class="inv-to">Factura a</p>
                                                    </div>

                                                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-8 align-self-center order-sm-0 order-1 inv--payment-info">
                                                        <h6 class=" inv-title">Información de Pago:</h6>
                                                    </div>
                                                    
                                                    <div class="col-xl-8 col-lg-7 col-md-6 col-sm-4">
                                                        <p class="inv-customer-name">{{$factura->persona->documento}}</p>
                                                        <p class="inv-customer-name">{{$factura->persona->nombre}} {{$factura->persona->apellido}}</p>
                                                        <p class="inv-street-addr"></p>
                                                        <p class="inv-email-address">{{$factura->persona->email}}</p>
                                                        <p class="inv-email-address">{{$factura->persona->celular}}</p>
                                                    </div>
                                                    
                                                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-8 col-12 order-sm-0 order-1">
                                                        <div class="inv--payment-info">
                                                            @foreach ($factura->forma_pagos as $item)
                                                                <p><span class=" inv-subtitle">Forma de Pago:</span> <span>{{$item->forma_cobro->descripcion}}</span></p>
                                                                <p><span class=" inv-subtitle">Banco: </span> <span>{{$item->banco->descripcion}}</span></p>
                                                                <p><span class=" inv-subtitle">Monto Abando  :</span> <span>{{number_format($item->monto, 0, ',', '.')}}</span></p>
                                                                <hr>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                </div>
                                                
                                            </div>

                                            <div class="inv--product-table-section">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead class="">
                                                            <tr>
                                                                <th scope="col">S.No</th>
                                                                <th scope="col">Items</th>
                                                                <th class="text-right" scope="col">Precio Unitario</th>
                                                                <th class="text-right" scope="col">Cantidad</th>
                                                                <th class="text-right" scope="col">Exento</th>
                                                                <th class="text-right" scope="col">10 %</th>
                                                                <th class="text-right" scope="col">5 %</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($data as $item)
                                                                <tr>
                                                                    <td>{{$loop->iteration}}</td>
                                                                    <td>{{$item->descripcion}}</td>
                                                                    <td class="text-right">{{number_format($item->precio, 0, ',', '.')}}</td>
                                                                    <td class="text-right">{{$item->cantidad}}</td>
                                                                    <td class="text-right">
                                                                        {{number_format($item->exento, 0, ',', '.')}}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{number_format($item->grabado_10, 0, ',', '.')}}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{number_format($item->grabado_5, 0, ',', '.')}}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <div class="inv--total-amounts">
                                            
                                                <div class="row mt-4">
                                                    <div class="col-sm-5 col-12 order-sm-0 order-1">
                                                    </div>
                                                    <div class="col-sm-7 col-12 order-sm-1 order-0">
                                                        <div class="text-sm-right">
                                                            <div class="row">
                                                                <div class="col-sm-8 col-7">
                                                                    <p class="">Total: </p>
                                                                </div>
                                                                <div class="col-sm-4 col-5">
                                                                    <p class="">{{ number_format($data->sum('total'), 0, ',', '.')}}</p>
                                                                </div>
                                                                <div class="col-sm-8 col-7">
                                                                    <p class="">I.V.A.: </p>
                                                                </div>
                                                                <div class="col-sm-4 col-5">
                                                                    <p class="">{{ number_format($data->sum('iva_5') + $data->sum('iva_10'), 0, ',', '.') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="inv--note">

                                                <div class="row mt-4">
                                                    <div class="col-sm-12 col-12 order-sm-0 order-1">
                                                        <p>Nota:  Gracias por hacer negocios con nosotros.</p>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div> 
                                    
                                </div>


                            </div>

                        </div>

                    </div>

                    <div class="col-xl-3">

                        <div class="invoice-actions-btn">

                            <div class="invoice-action-btn">

                                <div class="row">
                                    @if ($factura->sifen?->sifen_estado == 'RECHAZADO' || $factura->generado_sifen == 0)
                                        <div class="col-xl-12 col-md-3 col-sm-6">
                                            <form action="{{route('sifen.enviar', $factura)}}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100 mb-4">Enviar</button>
                                            </form>
                                            
                                        </div>
                                    @endif
                                    
                                    <div class="col-xl-12 col-md-3 col-sm-6">
                                        <a href="#" class="btn btn-secondary btn-print  action-print">Imprimir</a>
                                    </div>
                                    <div class="col-xl-12 col-md-3 col-sm-6">
                                        <a href="{{route('factura.index')}}" class="btn btn-info btn-download">Regresar a Factura</a>
                                    </div>
                                    {{-- <div class="col-xl-12 col-md-3 col-sm-6">
                                        <a href="#" class="btn btn-dark btn-edit">Editar</a>
                                    </div> --}}
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>

                </div>
                
                
            </div>

        </div>
    </div>


@endsection


@section('js')
@endsection
