@extends('layouts.admin')

@section('styles')
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <style>
        .solicitud-wrapper {
            overflow: hidden;
            border: 1px solid #e5eaf0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 28px rgba(31, 53, 79, 0.08);
        }

        .solicitud-encabezado {
            padding: 28px 32px;
            border-bottom: 1px solid #edf0f3;
            background: linear-gradient(135deg, #f8fbfe 0%, #ffffff 100%);
        }

        .solicitud-encabezado h3 {
            margin-bottom: 6px;
            color: #193b5c;
            font-weight: 700;
        }

        .solicitud-encabezado p {
            margin-bottom: 0;
            color: #738293;
        }

        .asociado-resumen {
            display: flex;
            align-items: center;
            margin-top: 22px;
            padding: 16px 20px;
            border: 1px solid #e4ebf2;
            border-radius: 12px;
            background-color: #ffffff;
        }

        .asociado-icono {
            display: flex;
            width: 48px;
            height: 48px;
            min-width: 48px;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            border-radius: 50%;
            color: #1b6fc2;
            background-color: #e8f2fb;
        }

        .asociado-resumen strong {
            display: block;
            color: #253d55;
            font-size: 16px;
        }

        .asociado-resumen span {
            color: #7d8995;
            font-size: 13px;
        }

        .solicitud-contenido {
            padding: 30px 32px 18px;
        }

        .solicitud-subtitulo {
            margin-bottom: 20px;
            color: #314b63;
            font-size: 16px;
            font-weight: 600;
        }

        .prestamo-card {
            position: relative;
            display: flex;
            height: 100%;
            min-height: 185px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 26px 22px;
            overflow: hidden;
            border: 1px solid #e2e8ef;
            border-radius: 14px;
            color: #34495e;
            background-color: #ffffff;
            text-align: center;
            text-decoration: none !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease,
                border-color 0.2s ease;
        }

        .prestamo-card::before {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background-color: var(--color-prestamo, #1b6fc2);
            content: '';
        }

        .prestamo-card:hover {
            border-color: var(--color-prestamo, #1b6fc2);
            color: #34495e;
            box-shadow: 0 12px 28px rgba(31, 69, 105, 0.14);
            transform: translateY(-4px);
        }

        .prestamo-icono {
            display: flex;
            width: 66px;
            height: 66px;
            align-items: center;
            justify-content: center;
            margin-bottom: 17px;
            border-radius: 18px;
            color: var(--color-prestamo, #1b6fc2);
            background-color: var(--fondo-prestamo, #e8f2fb);
        }

        .prestamo-card h4 {
            margin-bottom: 8px;
            color: #193b5c;
            font-size: 17px;
            font-weight: 700;
        }

        .prestamo-card p {
            margin-bottom: 14px;
            color: #7b8997;
            font-size: 13px;
            line-height: 1.55;
        }

        .prestamo-seleccionar {
            color: var(--color-prestamo, #1b6fc2);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .prestamo-color-1 {
            --color-prestamo: #1b6fc2;
            --fondo-prestamo: #e8f2fb;
        }

        .prestamo-color-2 {
            --color-prestamo: #168552;
            --fondo-prestamo: #e4f5ed;
        }

        .prestamo-color-3 {
            --color-prestamo: #8055b5;
            --fondo-prestamo: #f0eafa;
        }

        .prestamo-color-4 {
            --color-prestamo: #c17817;
            --fondo-prestamo: #fff3df;
        }

        .sin-prestamos {
            padding: 38px 20px;
            border: 1px dashed #cfd8e2;
            border-radius: 12px;
            color: #7b8997;
            background-color: #fafbfd;
            text-align: center;
        }

        .solicitud-pie {
            padding: 0 32px 30px;
        }

        @media (max-width: 767.98px) {
            .solicitud-encabezado,
            .solicitud-contenido,
            .solicitud-pie {
                padding-right: 20px;
                padding-left: 20px;
            }

            .asociado-resumen {
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $persona = $asociado->persona;
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="solicitud-wrapper">

            <div class="solicitud-encabezado">
                <h3>Nueva solicitud de préstamo</h3>

                <p>
                    Seleccione el tipo de préstamo que desea solicitar para el asociado.
                </p>
                @include('varios.mensaje')
                <div class="asociado-resumen">
                    <div class="asociado-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>

                    <div>
                        <strong>
                            {{ $persona?->nombre }} {{ $persona?->apellido }}
                        </strong>

                        <span>
                            C.I. {{ $persona?->documento ?? 'Sin documento' }}
                            · Socio N.º {{ $asociado->numero_socio ?? 'Sin número' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="solicitud-contenido">
                <div class="solicitud-subtitulo">
                    Tipos de préstamo disponibles
                </div>

                <div class="row">
                    @forelse ($tipoPrestamo as $tipo)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <a
                                href="{{ route('asociado.crear_solicitud', ['asociado' => $asociado->id, 'tipoPrestamo' => $tipo->id]) }}"
                                class="prestamo-card prestamo-color-{{ ($loop->index % 4) + 1 }}"
                                data-tipo-prestamo-id="{{ $tipo->id }}"
                            >
                                <div class="prestamo-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="31" height="31"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M11 15h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.5.6L3 16"></path>
                                        <path d="m7 20 1.6-1.4c.4-.4 1-.6 1.6-.6H15c1.1 0 2.1-.4 2.8-1.2L22 13"></path>
                                        <path d="M2 15l6 6"></path>
                                        <circle cx="16" cy="7" r="4"></circle>
                                    </svg>
                                </div>

                                <h4>
                                    {{ $tipo->descripcion }}
                                </h4>

                                <p>
                                    Crear una nueva solicitud de este tipo para el asociado seleccionado.
                                </p>

                                <span class="prestamo-seleccionar">
                                    Seleccionar &nbsp;→
                                </span>
                            </a>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="sin-prestamos">
                                No existen tipos de préstamo habilitados actualmente.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="solicitud-pie">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>

        </div>
    </div>
@endsection

@section('js')
@endsection
