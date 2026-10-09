<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Planilla de miembros
        {{ str_pad($planilla->numero, 5, '0', STR_PAD_LEFT) }}
        /
        {{ $planilla->anio }}
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 25px 28px 35px 28px;
        }

        * {
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            color: #111111;
            font-size: 10px;
        }

        .tabla-encabezado {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .tabla-encabezado td {
            vertical-align: middle;
        }

        .datos-entidad {
            width: 70%;
            text-align: left;
        }

        .numero-planilla {
            width: 30%;
            padding: 8px;
            border: 1px solid #222222;
            text-align: center;
        }

        .nombre-entidad {
            margin: 0 0 3px 0;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .ruc-entidad {
            margin: 0;
            font-size: 10px;
        }

        .numero-planilla-titulo {
            margin-bottom: 5px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .numero-planilla-valor {
            font-size: 14px;
            font-weight: bold;
        }

        .titulo-principal {
            margin: 12px 0 3px 0;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .periodo {
            margin: 0 0 10px 0;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .linea {
            margin-bottom: 10px;
            border-bottom: 2px solid #222222;
        }

        .tabla-datos {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .tabla-datos td {
            padding: 4px 6px;
            border: 1px solid #777777;
            vertical-align: middle;
        }

        .tabla-datos .etiqueta {
            width: 13%;
            background-color: #eeeeee;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .tabla-datos .valor {
            width: 20%;
            font-size: 10px;
        }

        .estado {
            font-weight: bold;
        }

        .estado-generada {
            color: #856404;
        }

        .estado-pagada {
            color: #155724;
        }

        .estado-anulada {
            color: #9c0006;
        }

        .tabla-detalle {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-detalle thead {
            display: table-header-group;
        }

        .tabla-detalle tfoot {
            display: table-row-group;
        }

        .tabla-detalle tr {
            page-break-inside: avoid;
        }

        .tabla-detalle th {
            padding: 6px 4px;
            border: 1px solid #222222;
            background-color: #d9d9d9;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .tabla-detalle td {
            padding: 6px 5px;
            border: 1px solid #555555;
            font-size: 9px;
            vertical-align: middle;
        }

        .fila-par {
            background-color: #f7f7f7;
        }

        .centro {
            text-align: center;
        }

        .derecha {
            text-align: right;
        }

        .izquierda {
            text-align: left;
        }

        .nombre-miembro {
            font-weight: bold;
            text-transform: uppercase;
        }

        .monto {
            white-space: nowrap;
            text-align: right;
        }

        .fila-total td {
            padding-top: 7px;
            padding-bottom: 7px;
            border: 1px solid #222222;
            background-color: #d9d9d9;
            font-size: 10px;
            font-weight: bold;
        }

        .total-texto {
            text-align: right;
            text-transform: uppercase;
        }

        .resumen {
            width: 42%;
            margin-top: 12px;
            margin-left: 58%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .resumen td {
            padding: 5px 7px;
            border: 1px solid #444444;
        }

        .resumen-etiqueta {
            width: 60%;
            background-color: #eeeeee;
            font-weight: bold;
            text-transform: uppercase;
        }

        .resumen-monto {
            width: 40%;
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .resumen-neto td {
            background-color: #d9d9d9;
            font-size: 11px;
        }

        .tabla-firmas {
            width: 100%;
            margin-top: 65px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .tabla-firmas td {
            width: 33.33%;
            padding: 0 30px;
            text-align: center;
            vertical-align: top;
        }

        .firma-linea {
            padding-top: 5px;
            border-top: 1px solid #222222;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .firma-subtitulo {
            margin-top: 3px;
            font-size: 8px;
            font-weight: normal;
            text-transform: none;
        }

        .pie-pagina {
            position: fixed;
            right: 0;
            bottom: -22px;
            left: 0;
            padding-top: 4px;
            border-top: 1px solid #999999;
            color: #555555;
            font-size: 7px;
        }

        .pie-izquierda {
            float: left;
            width: 60%;
            text-align: left;
        }

        .pie-derecha {
            float: right;
            width: 40%;
            text-align: right;
        }

        .marca-anulada {
            position: fixed;
            top: 260px;
            left: 275px;
            color: #e6b8b8;
            font-size: 75px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    @php
        $estadoPlanilla = (int) $planilla->estado_planilla;

        $nombreEntidad =
            $entidad->nombre
            ?? $entidad->razon_social
            ?? 'ENTIDAD';

        $rucEntidad =
            $entidad->ruc
            ?? $entidad->ruc_sin_digito
            ?? null;

        $estadoDescripcion =
            $estadosPlanilla[$estadoPlanilla]
            ?? 'SIN ESTADO';

        $claseEstado = match ($estadoPlanilla) {
            1 => 'estado-generada',
            2 => 'estado-pagada',
            3 => 'estado-anulada',
            default => '',
        };

        $totalRemuneracion = (int) $planilla->detalles->sum(
            'monto_remuneracion'
        );

        $totalAdelanto = (int) $planilla->detalles->sum(
            'adelanto'
        );

        $totalNeto = (int) $planilla->detalles->sum(
            'neto'
        );

        $numeroPlanilla = str_pad(
            $planilla->numero,
            5,
            '0',
            STR_PAD_LEFT
        );
    @endphp

    @if ($estadoPlanilla === 3)
        <div class="marca-anulada">
            ANULADA
        </div>
    @endif

    {{-- ENCABEZADO --}}
    <table class="tabla-encabezado">
        <tr>
            <td class="datos-entidad">
                <div class="nombre-entidad">
                    {{ $nombreEntidad }}
                </div>

                @if ($rucEntidad)
                    <div class="ruc-entidad">
                        RUC: {{ $rucEntidad }}
                    </div>
                @endif
            </td>

            <td class="numero-planilla">
                <div class="numero-planilla-titulo">
                    Planilla N.º
                </div>

                <div class="numero-planilla-valor">
                    {{ $numeroPlanilla }}/{{ $planilla->anio }}
                </div>
            </td>
        </tr>
    </table>

    <div class="titulo-principal">
        Planilla de remuneración de miembros
    </div>

    <div class="periodo">
        Correspondiente a
        {{ $meses[(int) $planilla->mes] ?? '' }}
        de
        {{ $planilla->anio }}
    </div>

    <div class="linea"></div>

    {{-- DATOS GENERALES --}}
    <table class="tabla-datos">
        <tr>
            <td class="etiqueta">
                Periodo
            </td>

            <td class="valor">
                {{ $meses[(int) $planilla->mes] ?? '' }}
                /
                {{ $planilla->anio }}
            </td>

            <td class="etiqueta">
                Fecha generación
            </td>

            <td class="valor">
                @if ($planilla->fecha_generacion)
                    {{ \Carbon\Carbon::parse(
                        $planilla->fecha_generacion
                    )->format('d/m/Y') }}
                @else
                    -
                @endif
            </td>

            <td class="etiqueta">
                Estado
            </td>

            <td class="valor">
                <span class="estado {{ $claseEstado }}">
                    {{ $estadoDescripcion }}
                </span>
            </td>
        </tr>

        <tr>
            <td class="etiqueta">
                Miembros
            </td>

            <td class="valor">
                {{ $planilla->detalles->count() }}
            </td>

            <td class="etiqueta">
                Fecha de pago
            </td>

            <td class="valor">
                @if ($planilla->fecha_pago)
                    {{ \Carbon\Carbon::parse(
                        $planilla->fecha_pago
                    )->format('d/m/Y') }}
                @else
                    PENDIENTE
                @endif
            </td>

            <td class="etiqueta">
                Orden de pago
            </td>

            <td class="valor">
                {{ $planilla->orden_pago_id ?? 'PENDIENTE' }}
            </td>
        </tr>
    </table>

    {{-- DETALLE DE MIEMBROS --}}
    <table class="tabla-detalle">
        <thead>
            <tr>
                <th style="width: 4%;">
                    N.º
                </th>

                <th style="width: 31%;">
                    Nombre y apellido
                </th>

                <th style="width: 9%;">
                    Tipo
                </th>

                <th style="width: 15%;">
                    Remuneración
                </th>

                <th style="width: 14%;">
                    Adelanto
                </th>

                <th style="width: 15%;">
                    Neto a pagar
                </th>

                <th style="width: 12%;">
                    Estado
                </th>
            </tr>
        </thead>

        <tbody>
            @foreach ($planilla->detalles as $detalle)
                <tr class="{{ $loop->even ? 'fila-par' : '' }}">
                    <td class="centro">
                        {{ $loop->iteration }}
                    </td>

                    <td class="nombre-miembro">
                        {{ $detalle->nombre_completo }}
                    </td>

                    <td class="centro">
                        {{ $tipos[(int) $detalle->tipo] ?? '-' }}
                    </td>

                    <td class="monto">
                        Gs.
                        {{ number_format(
                            (int) $detalle->monto_remuneracion,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="monto">
                        Gs.
                        {{ number_format(
                            (int) $detalle->adelanto,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="monto">
                        Gs.
                        {{ number_format(
                            (int) $detalle->neto,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="centro">
                        @if ((int) $detalle->estado_pago === 2)
                            PAGADO
                        @else
                            PENDIENTE
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr class="fila-total">
                <td
                    colspan="3"
                    class="total-texto"
                >
                    Totales
                </td>

                <td class="monto">
                    Gs.
                    {{ number_format(
                        $totalRemuneracion,
                        0,
                        ',',
                        '.'
                    ) }}
                </td>

                <td class="monto">
                    Gs.
                    {{ number_format(
                        $totalAdelanto,
                        0,
                        ',',
                        '.'
                    ) }}
                </td>

                <td class="monto">
                    Gs.
                    {{ number_format(
                        $totalNeto,
                        0,
                        ',',
                        '.'
                    ) }}
                </td>

                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- RESUMEN --}}
    <table class="resumen">
        <tr>
            <td class="resumen-etiqueta">
                Total remuneración
            </td>

            <td class="resumen-monto">
                Gs.
                {{ number_format(
                    $totalRemuneracion,
                    0,
                    ',',
                    '.'
                ) }}
            </td>
        </tr>

        <tr>
            <td class="resumen-etiqueta">
                Total adelantos
            </td>

            <td class="resumen-monto">
                Gs.
                {{ number_format(
                    $totalAdelanto,
                    0,
                    ',',
                    '.'
                ) }}
            </td>
        </tr>

        <tr class="resumen-neto">
            <td class="resumen-etiqueta">
                Total neto a pagar
            </td>

            <td class="resumen-monto">
                Gs.
                {{ number_format(
                    $totalNeto,
                    0,
                    ',',
                    '.'
                ) }}
            </td>
        </tr>
    </table>

    {{-- FIRMAS --}}
    <table class="tabla-firmas">
        <tr>
            <td>
                <div class="firma-linea">
                    Elaborado por
                </div>

                <div class="firma-subtitulo">
                    Firma y aclaración
                </div>
            </td>

            <td>
                <div class="firma-linea">
                    Verificado por
                </div>

                <div class="firma-subtitulo">
                    Firma y aclaración
                </div>
            </td>

            <td>
                <div class="firma-linea">
                    Autorizado por
                </div>

                <div class="firma-subtitulo">
                    Firma y aclaración
                </div>
            </td>
        </tr>
    </table>

    {{-- PIE DE PÁGINA --}}
    <div class="pie-pagina">
        <div class="pie-izquierda">
            Planilla N.º
            {{ $numeroPlanilla }}/{{ $planilla->anio }}
            -
            {{ $nombreEntidad }}
        </div>

        <div class="pie-derecha">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</body>
</html>
