@extends('layouts.admin')

@section('styles')
    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/alert.css') }}"
    >

    <style>
        .planilla-resumen {
            padding: 20px;
            border: 1px solid #e1e7ed;
            border-radius: 10px;
            background: #f8fafc;
        }

        .planilla-resumen-etiqueta {
            display: block;
            margin-bottom: 5px;
            color: #77818c;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .planilla-resumen-valor {
            color: #263746;
            font-size: 20px;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                @if ($errors->has('planilla'))
                    <div class="alert alert-danger">
                        {{ $errors->first('planilla') }}
                    </div>
                @endif

                <div
                    class="d-flex flex-wrap align-items-center
                           justify-content-between mb-4"
                >
                    <div>
                        <h4 class="mb-1">
                            Generar planilla de miembros
                        </h4>

                        <small class="text-muted">
                            Los importes se obtendrán directamente
                            de la configuración de cada miembro.
                        </small>
                    </div>

                    <a
                        href="{{ route('miembros.planillas.index') }}"
                        class="btn btn-secondary mt-2"
                    >
                        <i class="fa fa-arrow-left mr-1"></i>
                        Volver
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle mr-1"></i>

                    Se incluirán únicamente los miembros activos
                    que estén habilitados para percibir remuneración.

                    Para modificar una dieta o un adelanto,
                    debe hacerlo desde la administración de miembros
                    antes de generar esta planilla.
                </div>

                <div class="planilla-resumen mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <span class="planilla-resumen-etiqueta">
                                Miembros habilitados
                            </span>

                            <span class="planilla-resumen-valor">
                                {{ number_format(
                                    $cantidadMiembros,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <span class="planilla-resumen-etiqueta">
                                Remuneración
                            </span>

                            <span class="planilla-resumen-valor">
                                G.
                                {{ number_format(
                                    $totalRemuneracion,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <span class="planilla-resumen-etiqueta">
                                Adelantos
                            </span>

                            <span class="planilla-resumen-valor">
                                G.
                                {{ number_format(
                                    $totalAdelanto,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>
                        </div>

                        <div class="col-md-3">
                            <span class="planilla-resumen-etiqueta">
                                Neto
                            </span>

                            <span class="planilla-resumen-valor text-success">
                                G.
                                {{ number_format(
                                    $totalNeto,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>
                        </div>
                    </div>
                </div>

                <form
                    action="{{ route('miembros.planillas.store') }}"
                    method="POST"
                    onsubmit="
                        if (this.dataset.enviando === '1') return false;
                        this.dataset.enviando = '1';
                        document.getElementById('btnEnviar').disabled = true;
                        document.getElementById('btnEnviar').innerText = 'Enviando...';"
                >
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mes">
                                Mes
                            </label>

                            <select
                                name="mes"
                                id="mes"
                                class="form-control
                                    @error('mes') is-invalid @enderror"
                                required
                            >
                                <option value="">
                                    Seleccione...
                                </option>

                                @foreach ($meses as $numero => $nombre)
                                    <option
                                        value="{{ $numero }}"
                                        {{ (int) old(
                                            'mes',
                                            now()->month
                                        ) === $numero
                                            ? 'selected'
                                            : '' }}
                                    >
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('mes')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="anio">
                                Año
                            </label>

                            <select
                                name="anio"
                                id="anio"
                                class="form-control
                                    @error('anio') is-invalid @enderror"
                                required
                            >
                                @for (
                                    $anio = now()->year;
                                    $anio >= now()->year - 10;
                                    $anio--
                                )
                                    <option
                                        value="{{ $anio }}"
                                        {{ (int) old(
                                            'anio',
                                            now()->year
                                        ) === $anio
                                            ? 'selected'
                                            : '' }}
                                    >
                                        {{ $anio }}
                                    </option>
                                @endfor
                            </select>

                            @error('anio')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" id="btnEnviar" class="btn btn-primary" {{ $cantidadMiembros <= 0 ? 'disabled' : '' }}>
                            <i class="fa fa-file-text-o mr-1"></i>
                            Generar planilla
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
