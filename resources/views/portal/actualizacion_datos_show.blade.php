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
            background: linear-gradient(135deg, #8055b5, #5e3c8c);
        }

        .solicitud-header h3 {
            margin-bottom: 5px;
            color: #ffffff;
            font-weight: 700;
        }

        .solicitud-header p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.85);
        }

        .solicitud-body {
            padding: 30px;
        }

        .seccion-titulo {
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e4eaf0;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .cambio-card {
            height: 100%;
            padding: 18px;
            border: 1px solid #e2e8ef;
            border-radius: 12px;
            background-color: #fafcfd;
        }

        .cambio-titulo {
            margin-bottom: 14px;
            color: #8055b5;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .cambio-valores {
            display: grid;
            grid-template-columns: 1fr 35px 1fr;
            align-items: center;
            gap: 10px;
        }

        .cambio-valor small {
            display: block;
            margin-bottom: 4px;
            color: #8a98a6;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .cambio-valor span {
            display: block;
            color: #34495e;
            font-size: 14px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .cambio-nuevo span {
            color: #168552;
        }

        .cambio-flecha {
            color: #8055b5;
            text-align: center;
            font-size: 18px;
        }

        .motivo-box {
            padding: 20px;
            border-left: 4px solid #8055b5;
            border-radius: 8px;
            color: #485969;
            background-color: #faf7fd;
            line-height: 1.7;
        }

        .documento-card {
            padding: 18px;
            border: 1px solid #e2e8ef;
            border-radius: 12px;
            background-color: #f8fafc;
            text-align: center;
        }

        .documento-card img {
            width: 100%;
            max-width: 340px;
            height: 230px;
            border-radius: 10px;
            background-color: #eef1f4;
            object-fit: contain;
        }

        .documento-etiqueta {
            display: block;
            margin-bottom: 12px;
            color: #1b3a5b;
            font-weight: 700;
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

            .cambio-valores {
                grid-template-columns: 1fr;
            }

            .cambio-flecha {
                transform: rotate(90deg);
            }
        }
    </style>
@endsection

@section('content')

    @php
        $estado = $data->estadoSolicitud;

        $cambios = collect([
            [
                'titulo' => 'Documento',
                'actual' => $data->documento_actual,
                'nuevo'  => $data->documento_nuevo,
            ],
            [
                'titulo' => 'Nombre',
                'actual' => $data->nombre_actual,
                'nuevo'  => $data->nombre_nuevo,
            ],
            [
                'titulo' => 'Apellido',
                'actual' => $data->apellido_actual,
                'nuevo'  => $data->apellido_nuevo,
            ],
            [
                'titulo' => 'Fecha de nacimiento',

                'actual' => $data->fecha_nacimiento_actual
                    ? \Carbon\Carbon::parse(
                        $data->fecha_nacimiento_actual
                    )->format('d/m/Y')
                    : 'No registrada',

                'nuevo' => $data->fecha_nacimiento_nueva
                    ? \Carbon\Carbon::parse(
                        $data->fecha_nacimiento_nueva
                    )->format('d/m/Y')
                    : null,
            ],
            [
                'titulo' => 'Institución municipal',

                'actual' => $data->institucionActual?->descripcion
                    ?? 'No registrada',

                'nuevo' => $data->institucionNueva?->descripcion,
            ],
            [
                'titulo' => 'Correo electrónico',
                'actual' => $data->email_actual
                    ?: 'No registrado',
                'nuevo'  => $data->email_nuevo,
            ],
            [
                'titulo' => 'Celular',
                'actual' => $data->celular_actual
                    ?: 'No registrado',
                'nuevo'  => $data->celular_nuevo,
            ],
        ])->filter(function ($cambio) {
            return $cambio['nuevo'] !== null
                && $cambio['nuevo'] !== '';
        });

        $frenteActual = $data->documento_frente_actual
            ? Storage::disk('public')->url(
                $data->documento_frente_actual
            )
            : null;

        $frenteNuevo = $data->documento_frente_nuevo
            ? Storage::disk('public')->url(
                $data->documento_frente_nuevo
            )
            : null;

        $reversoActual = $data->documento_reverso_actual
            ? Storage::disk('public')->url(
                $data->documento_reverso_actual
            )
            : null;

        $reversoNuevo = $data->documento_reverso_nuevo
            ? Storage::disk('public')->url(
                $data->documento_reverso_nuevo
            )
            : null;
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="solicitud-card">

            <div class="solicitud-header">
                <div class="row align-items-center">

                    <div class="col-md-8">
                        <h3>
                            <i class="fa fa-pencil-square-o mr-2"></i>
                            Actualización de datos
                        </h3>

                        <p>
                            Solicitud N.º
                            {{ str_pad(
                                $data->numero,
                                5,
                                '0',
                                STR_PAD_LEFT
                            ) }}/{{ $data->anio }}
                        </p>
                    </div>

                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <span class="badge badge-{{ $estado->color ?? 'secondary' }} estado-badge">
                            {{ $estado->descripcion ?? 'SIN ESTADO' }}
                        </span>
                    </div>

                </div>
            </div>

            <div class="solicitud-body">

                <div class="row mb-4">

                    <div class="col-md-4 mb-3">
                        <div class="cambio-card">
                            <span class="cambio-titulo">
                                Número de solicitud
                            </span>

                            <div class="cambio-valor">
                                <span>
                                    {{ str_pad(
                                        $data->numero,
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}/{{ $data->anio }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="cambio-card">
                            <span class="cambio-titulo">
                                Fecha de solicitud
                            </span>

                            <div class="cambio-valor">
                                <span>
                                    {{ \Carbon\Carbon::parse(
                                        $data->fecha_solicitud
                                    )->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="cambio-card">
                            <span class="cambio-titulo">
                                Estado
                            </span>

                            <div class="cambio-valor">
                                <span>
                                    {{ $estado->descripcion
                                        ?? 'SIN ESTADO' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="seccion-titulo">
                    <i class="fa fa-exchange mr-2"></i>
                    Cambios solicitados
                </div>

                <div class="row">

                    @forelse ($cambios as $cambio)
                        <div class="col-lg-6 mb-4">
                            <div class="cambio-card">

                                <div class="cambio-titulo">
                                    {{ $cambio['titulo'] }}
                                </div>

                                <div class="cambio-valores">

                                    <div class="cambio-valor">
                                        <small>Dato actual</small>
                                        <span>
                                            {{ $cambio['actual'] }}
                                        </span>
                                    </div>

                                    <div class="cambio-flecha">
                                        <i class="fa fa-arrow-right"></i>
                                    </div>

                                    <div class="cambio-valor cambio-nuevo">
                                        <small>Dato solicitado</small>
                                        <span>
                                            {{ $cambio['nuevo'] }}
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light border">
                                No se encontraron cambios de datos personales.
                            </div>
                        </div>
                    @endforelse

                </div>

                @if ($frenteNuevo || $reversoNuevo)

                    <div class="seccion-titulo mt-3">
                        <i class="fa fa-id-card-o mr-2"></i>
                        Actualización de documentos
                    </div>

                    <div class="row">

                        @if ($frenteNuevo)
                            <div class="col-lg-6 mb-4">
                                <div class="documento-card">

                                    <span class="documento-etiqueta">
                                        Frente de la cédula
                                    </span>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <small class="d-block mb-2">
                                                Imagen anterior
                                            </small>

                                            @if ($frenteActual)
                                                <a
                                                    href="{{ $frenteActual }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <img
                                                        src="{{ $frenteActual }}"
                                                        alt="Frente anterior"
                                                    >
                                                </a>
                                            @else
                                                <p class="text-muted">
                                                    No registrada
                                                </p>
                                            @endif
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <small class="d-block mb-2">
                                                Imagen solicitada
                                            </small>

                                            <a
                                                href="{{ $frenteNuevo }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <img
                                                    src="{{ $frenteNuevo }}"
                                                    alt="Frente nuevo"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif

                        @if ($reversoNuevo)
                            <div class="col-lg-6 mb-4">
                                <div class="documento-card">

                                    <span class="documento-etiqueta">
                                        Reverso de la cédula
                                    </span>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <small class="d-block mb-2">
                                                Imagen anterior
                                            </small>

                                            @if ($reversoActual)
                                                <a
                                                    href="{{ $reversoActual }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <img
                                                        src="{{ $reversoActual }}"
                                                        alt="Reverso anterior"
                                                    >
                                                </a>
                                            @else
                                                <p class="text-muted">
                                                    No registrada
                                                </p>
                                            @endif
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <small class="d-block mb-2">
                                                Imagen solicitada
                                            </small>

                                            <a
                                                href="{{ $reversoNuevo }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <img
                                                    src="{{ $reversoNuevo }}"
                                                    alt="Reverso nuevo"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif

                    </div>
                @endif

                <div class="seccion-titulo mt-3">
                    <i class="fa fa-commenting-o mr-2"></i>
                    Motivo de la solicitud
                </div>

                <div class="motivo-box mb-4">
                    {!! nl2br(e($data->motivo)) !!}
                </div>

                @if ($data->fecha_resolucion)
                    <div class="alert alert-info">
                        <strong>Fecha de resolución:</strong>

                        {{ \Carbon\Carbon::parse(
                            $data->fecha_resolucion
                        )->format('d/m/Y H:i') }}
                    </div>
                @endif

                @if ($data->motivo_rechazo)
                    <div class="alert alert-danger">
                        <strong>Motivo del rechazo:</strong>

                        <div class="mt-2">
                            {!! nl2br(e($data->motivo_rechazo)) !!}
                        </div>
                    </div>
                @endif

                @if ($data->observacion)
                    <div class="alert alert-info">
                        <strong>Observación:</strong>

                        <div class="mt-2">
                            {!! nl2br(e($data->observacion)) !!}
                        </div>
                    </div>
                @endif

                <a
                    href="{{ route('solicitudes') }}"
                    class="btn btn-outline-secondary mt-3"
                >
                    <i class="fa fa-arrow-left mr-1"></i>
                    Volver a mis solicitudes
                </a>

            </div>
        </div>
    </div>

@endsection

@section('js')
@endsection
