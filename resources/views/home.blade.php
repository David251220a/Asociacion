@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-xs-12">
                        <div class="alert alert-arrow-left alert-icon-left alert-light-primary mb-4" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9">
                            </path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            <strong>Información!</strong> Facturación del Mes.
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-xl-6 col-md-6 col-sm-12 col-12">
                        <div class="infobox-3">
                            <div class="info-icon" style="background: #8f1414">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 64 64" fill="none">
                                    <rect x="8" y="8" width="40" height="48" rx="4" ry="4" fill="#FFFFFF" stroke="#2E2E2E" stroke-width="2"/>
                                    <line x1="16" y1="20" x2="40" y2="20" stroke="#2E2E2E" stroke-width="2"/>
                                    <line x1="16" y1="28" x2="40" y2="28" stroke="#2E2E2E" stroke-width="2"/>
                                    <line x1="16" y1="36" x2="32" y2="36" stroke="#2E2E2E" stroke-width="2"/>
                                    <circle cx="48" cy="48" r="12" fill="#dc3545"/>
                                    <line x1="42" y1="42" x2="54" y2="54" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="54" y1="42" x2="42" y2="54" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h5 class="info-heading">Facturas Rechazadas : 0</h5>
                            <a class="info-link" href="#">Ver todos los rechazados
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                <line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                            </a>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 col-sm-12 col-12">
                        <div class="infobox-3">
                            <div class="info-icon" style="background: #0d3a0d">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 64 64" fill="none">
                                    <rect x="8" y="8" width="40" height="48" rx="4" ry="4" fill="#FFFFFF" stroke="#2E2E2E" stroke-width="2"/>
                                    <line x1="16" y1="20" x2="40" y2="20" stroke="#2E2E2E" stroke-width="2"/>
                                    <line x1="16" y1="28" x2="40" y2="28" stroke="#2E2E2E" stroke-width="2"/>
                                    <line x1="16" y1="36" x2="32" y2="36" stroke="#2E2E2E" stroke-width="2"/>
                                    <circle cx="48" cy="48" r="12" fill="#28a745"/>
                                    <path d="M42 48l4 4 8-8" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h5 class="info-heading">Facturas Aprobadas : 0</h5>
                            <a class="info-link" href="#">Ver todos los aprobados 
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                <line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="row mt-5">
                    <div  class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">
                                <thead>
                                    <tr>
                                        <th class="">Fecha</th>
                                        <th class="">Factura</th>
                                        <th class="">Monto</th>
                                        <th class="">Estado Sifen</th>
                                        <th class="">Evento</th>
                                        <th class="">Estado Evento</th>
                                        <th class="text-center">Icons</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @foreach ($data as $item)
                                        <tr>
                                            <td class="">
                                                {{$item->fecha_factura}}
                                            </td>
                                            <td>
                                                {{$item->factura_sucursal}}-{{$item->factura_general}}-{{str_pad($item->factura_numero, 7, '0', STR_PAD_LEFT)}}
                                            </td>
                                            <td class="text-right">{{number_format($item->monto_total, 0, ".", ".")}}</td>
                                            <td>
                                                 @if ($item->sifen->sifen_estado == 'APROBADO')
                                                    <span class="badge badge-success">APROBADO</span>
                                                @elseif($item->sifen->sifen_estado == 'RECHAZADO')
                                                    <span class="badge badge-danger">RECHAZADO</span>
                                                @else
                                                    <span class="badge badge-info">{{$item->sifen->sifen_estado}}</span>
                                                @endif
                                            </td>

                                            <td>{{$item->sifen->evento}}</td>
                                            <td>
                                                {{$item->sifen->sifen_evento_msjrespuesta}}
                                            </td>

                                            <td class="text-center">
                                                <ul class="table-controls">
                                                    <li>
                                                        <a class="ml-2" href="{{$item->sifen->link_qr}}" target="__blank" data-toggle="tooltip" data-placement="top" title="Consulta Sifen">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                            class="feather feather-chrome"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="21.17" y1="8" x2="12" y2="8"></line>
                                                            <line x1="3.95" y1="6.06" x2="8.54" y2="14"></line><line x1="10.88" y1="21.94" x2="15.46" y2="14"></line></svg>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="ml-2" href="{{route('factura.show', $item)}}" data-toggle="tooltip" data-placement="top" title="Ver Factura">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                            class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        @if ($item->anulado == 0)
                                                            <a class="ml-2" href="{{route('factura.evento', $item)}}" data-toggle="tooltip" data-placement="top" title="Eventos">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                                class="feather feather-zap"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                                            </a>
                                                        @endif
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach --}}
                                   
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7">total</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
