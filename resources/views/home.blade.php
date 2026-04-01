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
                        <div class="mb-4" style="border-left: 4px solid #1b6fc2; padding-left: 15px;">
                            <h3 style="font-weight: 700; color: #1b3a5b; margin-bottom: 5px;">
                                Bienvenido al Sistema AJUPEM
                            </h3>

                            <p style="margin: 0; color: #6c757d; font-size: 15px;">
                                Un espacio diseñado para facilitar la gestión institucional y brindar un mejor servicio a nuestros asociados.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
