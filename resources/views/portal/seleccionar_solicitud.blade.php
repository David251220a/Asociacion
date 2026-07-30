@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/alert.css') }}">

    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/infobox.css') }}">

    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/tables/table-basic.css') }}">

    <style>
        .solicitud-opcion {
            display: block;
            height: 100%;
            padding: 30px 25px;
            border: 1px solid #e3e8ee;
            border-radius: 14px;
            color: #33475b;
            background-color: #ffffff;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .solicitud-opcion:hover {
            color: #33475b;
            text-decoration: none;
            transform: translateY(-4px);
            border-color: #1b6fc2;
            box-shadow: 0 12px 30px rgba(27, 111, 194, 0.15);
        }

        .solicitud-icono {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .solicitud-icono.ayuda {
            color: #168552;
            background-color: #e1f5ec;
        }

        .solicitud-icono.prestamo {
            color: #1b6fc2;
            background-color: #e3f0fc;
        }

        .solicitud-icono.datos {
            color: #8055b5;
            background-color: #eee6f8;
        }

        .solicitud-opcion h4 {
            margin-bottom: 10px;
            color: #1b3a5b;
            font-weight: 700;
        }

        .solicitud-opcion p {
            margin-bottom: 0;
            color: #758392;
            line-height: 1.6;
        }
    </style>
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">



            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h3 class="mb-1" style="color: #1b3a5b; font-weight: 700;">
                            <i class="fa fa-file-text-o mr-2"></i>
                            Nueva solicitud
                        </h3>

                        <p class="text-muted mb-0">
                            Seleccioná el tipo de solicitud que deseas realizar.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <a
                            href="{{ route('solicitudes') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fa fa-arrow-left mr-1"></i>
                            Volver
                        </a>
                    </div>
                </div>

                @include('varios.mensaje')

                <hr class="mb-4">

                <div class="row">

                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="{{route('actualizar_datos')}}" class="solicitud-opcion">
                            <div class="solicitud-icono datos">
                                <i class="fas fa-user-edit"></i>
                            </div>

                            <h4>Actualización de datos</h4>

                            <p>
                                Solicitar la corrección de información personal.
                            </p>
                        </a>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="{{ route('ayuda_social')}}" class="solicitud-opcion">
                            <div class="solicitud-icono ayuda">
                                <i class="fas fa-hands-helping"></i>
                            </div>

                            <h4>Ayuda social</h4>

                            <p>
                                Solicitar apoyo económico por una necesidad personal
                                o familiar.
                            </p>
                        </a>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="#" class="solicitud-opcion">
                            <div class="solicitud-icono prestamo">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>

                            <h4>Préstamo</h4>

                            <p>
                                Iniciar una nueva solicitud de préstamo.
                            </p>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection

@section('js')
@endsection
