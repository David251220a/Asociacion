@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/alert.css') }}">

    <style>
        .solicitud-card {
            overflow: hidden;
            border: 1px solid #e2e8ef;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 8px 25px rgba(27, 58, 91, 0.08);
        }

        .solicitud-header {
            padding: 25px 30px;
            color: #ffffff;
            background: linear-gradient(135deg, #1b6fc2, #173a5e);
        }

        .solicitud-header h3 {
            margin-bottom: 5px;
            color: #ffffff;
            font-weight: 700;
        }

        .solicitud-header p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.82);
        }

        .solicitud-body {
            padding: 30px;
        }

        .detalle-titulo {
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5ebf0;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .detalle-item {
            height: 100%;
            padding: 14px 16px;
            border: 1px solid #e1e8ee;
            border-radius: 10px;
            background-color: #f8fafc;
        }

        .detalle-item small {
            display: block;
            margin-bottom: 4px;
            color: #8795a3;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .detalle-item span {
            color: #34495e;
            font-size: 14px;
            font-weight: 600;
        }

        .motivo-box {
            padding: 20px;
            border-left: 4px solid #1b6fc2;
            border-radius: 8px;
            color: #485969;
            background-color: #f5f9fd;
            line-height: 1.7;
        }

        .documento-imagen {
            display: block;
            max-width: 100%;
            max-height: 500px;
            margin: 0 auto;
            border: 5px solid #ffffff;
            border-radius: 12px;
            object-fit: contain;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.15);
        }

        .estado-badge {
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .solicitud-header,
            .solicitud-body {
                padding: 22px 18px;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $estado = $data->estadoSolicitud;
        $colorEstado = $estado->color ?? 'secondary';

        $urlDocumento = $data->documento_respaldo
            ? Storage::disk('public')->url($data->documento_respaldo)
            : null;

        $extensionDocumento = $data->documento_respaldo
            ? strtolower(pathinfo(
                $data->documento_respaldo,
                PATHINFO_EXTENSION
            ))
            : null;
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="solicitud-card">

            <div class="solicitud-header">
                <div class="row align-items-center">

                    <div class="col-md-8">
                        <h3>
                            <i class="fa fa-heart mr-2"></i>
                            Solicitud de ayuda social
                        </h3>

                        <p>
                            Solicitud N.º
                            {{ str_pad($data->numero, 5, '0', STR_PAD_LEFT) }}/{{ $data->anio }}
                        </p>
                    </div>

                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <span class="badge badge-{{ $colorEstado }} estado-badge">
                            {{ $estado->descripcion ?? 'SIN ESTADO' }}
                        </span>
                    </div>

                </div>
            </div>

            <div class="solicitud-body">

                {{-- INFORMACIÓN GENERAL --}}
                <div class="detalle-titulo">
                    <i class="fa fa-file-text-o mr-2"></i>
                    Información general
                </div>

                <div class="row mb-4">

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Número</small>

                            <span>
                                {{ str_pad($data->numero, 5, '0', STR_PAD_LEFT) }}/{{ $data->anio }}
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Fecha de solicitud</small>

                            <span>
                                {{ \Carbon\Carbon::parse($data->fecha_solicitud)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Estado</small>

                            <span>
                                {{ $estado->descripcion ?? 'SIN ESTADO' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Monto Aprobado</small>

                            <span>
                                G. {{ number_format(
                                    $data->monto_aprobado,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>
                        </div>
                    </div>

                </div>

                {{-- SOLICITANTE --}}
                <div class="detalle-titulo">
                    <i class="fa fa-user mr-2"></i>
                    Datos del solicitante
                </div>

                <div class="row mb-4">

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Documento</small>
                            <span>{{ $persona->documento }}</span>
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Nombre y apellido</small>

                            <span>
                                {{ $persona->nombre }}
                                {{ $persona->apellido }}
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="detalle-item">
                            <small>Beneficiario</small>

                            <span>
                                {{ $data->beneficiario
                                    ?: $persona->nombre . ' ' . $persona->apellido }}
                            </span>
                        </div>
                    </div>

                </div>

                {{-- MOTIVO --}}
                <div class="detalle-titulo">
                    <i class="fa fa-commenting-o mr-2"></i>
                    Motivo de la solicitud
                </div>

                <div class="motivo-box mb-4">
                    {!! nl2br(e($data->motivo)) !!}
                </div>

                {{-- RESOLUCIÓN --}}
                @if ($data->fecha_resolucion || $data->monto_aprobado > 0)
                    <div class="detalle-titulo">
                        <i class="fa fa-check-circle mr-2"></i>
                        Resolución
                    </div>

                    <div class="row mb-4">

                        @if ($data->fecha_resolucion)
                            <div class="col-md-4 mb-3">
                                <div class="detalle-item">
                                    <small>Fecha de resolución</small>

                                    <span>
                                        {{ \Carbon\Carbon::parse($data->fecha_resolucion)->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        @if ($data->monto_aprobado > 0)
                            <div class="col-md-4 mb-3">
                                <div class="detalle-item">
                                    <small>Monto aprobado</small>

                                    <span>
                                        G. {{ number_format(
                                            $data->monto_aprobado,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif

                {{-- RECHAZO --}}
                @if (!empty($data->motivo_rechazo))
                    <div class="alert alert-danger">
                        <strong>
                            <i class="fa fa-times-circle mr-1"></i>
                            Motivo del rechazo:
                        </strong>

                        <div class="mt-2">
                            {!! nl2br(e($data->motivo_rechazo)) !!}
                        </div>
                    </div>
                @endif

                {{-- OBSERVACIÓN --}}
                @if (!empty($data->observacion))
                    <div class="alert alert-info">
                        <strong>Observación:</strong>

                        <div class="mt-2">
                            {!! nl2br(e($data->observacion)) !!}
                        </div>
                    </div>
                @endif

                {{-- DOCUMENTO --}}
                <div class="detalle-titulo mt-4">
                    <i class="fa fa-paperclip mr-2"></i>
                    Documento respaldatorio
                </div>

                @if ($urlDocumento)

                    @if (in_array($extensionDocumento, ['jpg', 'jpeg']))
                        <a
                            href="{{ $urlDocumento }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                src="{{ $urlDocumento }}"
                                class="documento-imagen"
                                alt="Documento respaldatorio"
                            >
                        </a>

                        <div class="text-center mt-3">
                            <a
                                href="{{ $urlDocumento }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-primary"
                            >
                                <i class="fa fa-search-plus mr-1"></i>
                                Ver imagen completa
                            </a>
                        </div>
                    @elseif ($extensionDocumento === 'pdf')
                        <div class="text-center py-4">
                            <i
                                class="fa fa-file-pdf-o text-danger"
                                style="font-size: 55px;"
                            ></i>

                            <p class="mt-3">
                                La solicitud contiene un documento PDF.
                            </p>

                            <a
                                href="{{ $urlDocumento }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-danger"
                            >
                                <i class="fa fa-eye mr-1"></i>
                                Ver documento PDF
                            </a>
                        </div>
                    @endif

                @else
                    <div class="alert alert-light border">
                        La solicitud no posee un documento respaldatorio.
                    </div>
                @endif

                <div class="mt-4">
                    <a
                        href="{{ route('solicitudes') }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fa fa-arrow-left mr-1"></i>
                        Volver a mis solicitudes
                    </a>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('js')
@endsection
