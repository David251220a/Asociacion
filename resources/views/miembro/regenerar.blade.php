@extends('layouts.admin')

@section('styles')
    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/alert.css') }}"
    >
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                <div class="d-flex align-items-center mb-4">
                    <h4 class="mb-0">
                        Regenerar planilla de miembros
                    </h4>

                    <a
                        href="{{ route('miembros.planillas.index') }}"
                        class="btn btn-secondary ml-auto"
                    >
                        <i class="fa fa-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="alert alert-warning">
                    <strong>Importante:</strong>

                    se generará una nueva planilla para el mismo periodo.
                    Los detalles de la planilla anulada no serán utilizados.

                    La nueva planilla tomará solamente los miembros que
                    actualmente tengan:

                    <strong>estado activo y pago habilitado.</strong>
                </div>

                <form
                    method="POST"
                    action="{{ route(
                        'miembros.planillas.regenerar.store',
                        $planilla->id
                    ) }}"
                >
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>
                                Planilla anulada
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{
                                    str_pad(
                                        $planilla->numero,
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    )
                                }}/{{ $planilla->anio }}"
                                readonly
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>
                                Mes
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{
                                    $meses[(int) $planilla->mes]
                                    ?? ''
                                }}"
                                readonly
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>
                                Año
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $planilla->anio }}"
                                readonly
                            >
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">
                                        N.º
                                    </th>

                                    <th>
                                        Miembro
                                    </th>

                                    <th>
                                        Tipo
                                    </th>

                                    <th class="text-right">
                                        Remuneración
                                    </th>

                                    <th class="text-right">
                                        Adelanto
                                    </th>

                                    <th class="text-right">
                                        Neto
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($miembros as $miembro)
                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            <strong>
                                                {{ $miembro->nombre }}
                                                {{ $miembro->apellido }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{
                                                $tipos[
                                                    (int) $miembro->tipo
                                                ] ?? '-'
                                            }}
                                        </td>

                                        <td class="text-right">
                                            {{
                                                number_format(
                                                    $miembro
                                                        ->monto_remuneracion,
                                                    0,
                                                    ',',
                                                    '.'
                                                )
                                            }}
                                        </td>

                                        <td class="text-right">
                                            {{
                                                number_format(
                                                    $miembro->adelanto,
                                                    0,
                                                    ',',
                                                    '.'
                                                )
                                            }}
                                        </td>

                                        <td class="text-right">
                                            <strong>
                                                {{
                                                    number_format(
                                                        $miembro->neto,
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                }}
                                            </strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th
                                        colspan="3"
                                        class="text-right"
                                    >
                                        Totales
                                    </th>

                                    <th class="text-right">
                                        {{
                                            number_format(
                                                $totalRemuneracion,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </th>

                                    <th class="text-right">
                                        {{
                                            number_format(
                                                $totalAdelanto,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </th>

                                    <th class="text-right">
                                        {{
                                            number_format(
                                                $totalNeto,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-right mt-4">
                        <a
                            href="{{
                                route('miembros.planillas.index')
                            }}"
                            class="btn btn-secondary"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-success"
                            onclick="
                                this.disabled = true;
                                this.form.submit();
                            "
                        >
                            <i class="fa fa-refresh"></i>
                            Confirmar regeneración
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection
