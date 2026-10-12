<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Contrato de préstamo
        {{ str_pad($numeroPrestamo, 5, '0', STR_PAD_LEFT) }}
        /
        {{ $prestamo->anio }}
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25px 45px 35px 45px;
        }

        * {
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            color: #111111;
            font-size: 10px;
            line-height: 1.45;
        }

        .encabezado {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .encabezado td {
            vertical-align: middle;
        }

        .logo-contenedor {
            width: 30%;
            text-align: center;
            vertical-align: middle;
        }

        .datos-institucionales {
            width: 70%;
            text-align: center;
        }

        .logo {
            width: 200px;
            height: auto;
            max-height: 115px;
        }

        .lema {
            margin-bottom: 5px;
            font-family: DejaVu Serif, serif;
            font-size: 17px;
            font-style: italic;
            font-weight: bold;
        }

        .mision {
            padding: 0 15px;
            font-size: 8px;
            font-style: italic;
            line-height: 1.3;
        }

        .linea-encabezado {
            width: 70%;
            margin: 8px auto 15px auto;
            border-bottom: 3px solid #333333;
        }

        .fecha {
            margin: 15px 0 20px 0;
            text-align: right;
        }

        .numero-contrato {
            margin-bottom: 15px;
            text-align: right;
            font-size: 9px;
        }

        .titulo {
            margin: 12px 0 15px 0;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .parrafo {
            margin: 0 0 9px 0;
            text-align: justify;
            text-indent: 30px;
        }

        .sin-sangria {
            text-indent: 0;
        }

        .clausula {
            font-weight: bold;
            text-transform: uppercase;
        }

        .dato {
            font-weight: bold;
            text-transform: uppercase;
        }

        .tabla-cuotas {
            width: 100%;
            margin: 12px 0;
            border-collapse: collapse;
        }

        .tabla-cuotas th,
        .tabla-cuotas td {
            padding: 5px 4px;
            border: 1px solid #333333;
        }

        .tabla-cuotas th {
            background-color: #e8e8e8;
            font-size: 8px;
            text-align: center;
            text-transform: uppercase;
        }

        .tabla-cuotas td {
            font-size: 8px;
        }

        .centro {
            text-align: center;
        }

        .derecha {
            text-align: right;
        }

        .firmas {
            width: 100%;
            margin-top: 55px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .firmas td {
            width: 50%;
            padding: 0 30px;
            text-align: center;
            vertical-align: top;
        }

        .linea-firma {
            padding-top: 5px;
            border-top: 1px solid #222222;
            font-size: 9px;
            font-weight: bold;
        }

        .datos-firma {
            margin-top: 4px;
            font-size: 8px;
            font-weight: normal;
        }

        .pie {
            position: fixed;
            right: 0;
            bottom: -22px;
            left: 0;
            padding-top: 5px;
            border-top: 1px solid #999999;
            font-size: 7px;
            font-style: italic;
            text-align: center;
        }

        .marca-anulado {
            position: fixed;
            top: 350px;
            left: 160px;
            color: #e5b9b9;
            font-size: 60px;
            font-weight: bold;
            transform: rotate(-30deg);
        }
    </style>
</head>

<body>

    @php
        $logo = public_path('storage/iconos/logo.jpg');

        $nombreEntidad =
            $entidad->nombre
            ?? $entidad->razon_social
            ?? 'ASOCIACIÓN DE JUBILADOS Y PENSIONADOS MUNICIPALES DEL PARAGUAY';

        $rucEntidad =
            $entidad->ruc
            ?? $entidad->ruc_sin_digito
            ?? null;

        $nombreCompleto = trim(
            ($persona->nombre ?? '')
            . ' '
            . ($persona->apellido ?? '')
        );

        $telefono =
            $persona->celular
            ?? $persona->telefono
            ?? '';

        $tipoPrestamo =
            optional($prestamo->tipoPrestamo)->descripcion
            ?? optional(
                $prestamo->solicitud
            )->tipoPrestamo->descripcion
            ?? 'EMERGENCIA';
    @endphp

    @if ((int) $prestamo->estado_prestamo_id === 4)
        <div class="marca-anulado">
            ANULADO
        </div>
    @endif

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <table class="encabezado">
        <tr>
            <td class="logo-contenedor">
                <img src="{{ $logo }}" alt="Logo" class="logo">
            </td>

            <td class="datos-institucionales">
                <div class="lema">
                    Tesoro de sabiduría, uniendo generaciones
                </div>

                <div class="mision">
                    <strong>Misión:</strong>

                    Mejorar la calidad de vida de los asociados,
                    promoviendo su bienestar social, cultural y recreativo,
                    así como defendiendo sus derechos.
                </div>

                <div class="linea-encabezado"></div>
            </td>
        </tr>
    </table>

    <div class="fecha">
        Asunción,
        {{ $fechaContrato->format('d') }}
        de
        {{
            mb_strtolower(
                $fechaContrato
                    ->locale('es')
                    ->translatedFormat('F'),
                'UTF-8'
            )
        }}
        de
        {{ $fechaContrato->format('Y') }}
    </div>

    <div class="numero-contrato">
        Contrato N.º

        <strong>
            {{
                str_pad(
                    $numeroPrestamo,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
            }}/{{ $prestamo->anio }}
        </strong>
    </div>

    <div class="titulo">
        Contrato de préstamo de
        {{ $tipoPrestamo }}
        con carácter irrevocable
    </div>

    <p class="parrafo sin-sangria">
        Entre

        <span class="dato">
            {{ $nombreEntidad }}
        </span>,

        @if ($rucEntidad)
            con RUC N.º
            <span class="dato">{{ $rucEntidad }}</span>,
        @endif

        en adelante denominada

        <strong>“LA ASOCIACIÓN”</strong>,

        y el/la señor/a

        <span class="dato">{{ $nombreCompleto }}</span>,

        con Cédula de Identidad N.º

        <span class="dato">
            {{ $persona->documento }}
        </span>,

        socio/a de la institución, en adelante denominado/a

        <strong>“EL/LA PRESTATARIO/A”</strong>,

        acuerdan celebrar el presente contrato de préstamo conforme
        a las siguientes cláusulas:
    </p>

    <p class="parrafo">
        <span class="clausula">
            Primera – Objeto:
        </span>

        LA ASOCIACIÓN concede a EL/LA PRESTATARIO/A un préstamo de

        <strong>
            Gs.
            {{
                number_format(
                    $montoCapital,
                    0,
                    ',',
                    '.'
                )
            }}
        </strong>

        (<strong>{{ $montoLetras }}</strong>), cuyo recibo y aceptación
        se reconocen por medio del presente documento.
    </p>

    <p class="parrafo">
        <span class="clausula">
            Segunda – Forma de pago:
        </span>

        EL/LA PRESTATARIO/A se obliga a cancelar el préstamo en

        <strong>{{ $cantidadCuotas }}</strong>

        cuota(s), con una tasa aplicada de

        <strong>
            {{
                number_format(
                    (float) $tasaAplicada,
                    2,
                    ',',
                    '.'
                )
            }}%
        </strong>,

        más el IVA correspondiente sobre los intereses.
        El importe total del préstamo asciende a

        <strong>
            Gs.
            {{
                number_format(
                    $montoTotal,
                    0,
                    ',',
                    '.'
                )
            }}.
        </strong>
    </p>

    {{-- PLAN DE CUOTAS --}}
    <table class="tabla-cuotas">
        <thead>
            <tr>
                <th>Cuota</th>
                <th>Vencimiento</th>
                <th>Capital</th>
                <th>Interés</th>
                <th>IVA</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($prestamo->detalles as $cuota)
                @php
                    $ivaCuota =
                        $cuota->monto_iva
                        ?? $cuota->iva
                        ?? 0;
                @endphp

                <tr>
                    <td class="centro">
                        {{ $cuota->numero_cuota }}
                    </td>

                    <td class="centro">
                        {{
                            \Carbon\Carbon::parse(
                                $cuota->fecha_vencimiento
                            )->format('d/m/Y')
                        }}
                    </td>

                    <td class="derecha">
                        {{
                            number_format(
                                (int) $cuota->monto_capital,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="derecha">
                        {{
                            number_format(
                                (int) $cuota->monto_interes,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="derecha">
                        {{
                            number_format(
                                (int) $ivaCuota,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="derecha">
                        {{
                            number_format(
                                (int) $cuota->monto_total,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="parrafo">
        <span class="clausula">
            Tercera – Autorización irrevocable:
        </span>

        EL/LA PRESTATARIO/A autoriza suficientemente a LA ASOCIACIÓN
        a efectuar el descuento mensual de sus haberes o por medio de
        las planillas correspondientes, con carácter irrevocable,
        hasta la cancelación total del préstamo, sus intereses, IVA
        y demás conceptos que legalmente correspondan.
    </p>

    <p class="parrafo">
        <span class="clausula">
            Cuarta – Pago parcial:
        </span>

        Cuando el importe abonado sea inferior al total exigible,
        el sistema aplicará el pago a los conceptos pendientes conforme
        al orden establecido por LA ASOCIACIÓN, quedando el saldo
        pendiente para las siguientes planillas.
    </p>

    <p class="parrafo">
        <span class="clausula">
            Quinta – Interés punitorio:
        </span>

        Cuando una planilla haya sido procesada y una cuota conserve
        saldo pendiente, dicha cuota será considerada atrasada. En la
        siguiente generación podrá aplicarse un interés punitorio del
        uno por ciento (1%) sobre el saldo de capital, más el IVA
        correspondiente.
    </p>

    <p class="parrafo">
        <span class="clausula">
            Sexta – Conformidad:
        </span>

        Las partes manifiestan su conformidad con las condiciones
        establecidas en el presente documento y con lo dispuesto en
        la Resolución N.º {{ $resolucion }} de LA ASOCIACIÓN.
        En prueba de conformidad, firman dos ejemplares de un mismo
        tenor y a un solo efecto.
    </p>

    {{-- FIRMAS --}}
    <table class="firmas">
        <tr>
            <td>
                <div class="linea-firma">
                    {{ mb_strtoupper($nombreCompleto, 'UTF-8') }}

                    <div class="datos-firma">
                        C.I. N.º {{ $persona->documento }}

                        @if ($telefono)
                            <br>
                            Teléfono: {{ $telefono }}
                        @endif

                        <br>
                        EL/LA PRESTATARIO/A
                    </div>
                </div>
            </td>

            <td>
                <div class="linea-firma">
                    POR {{ mb_strtoupper($nombreEntidad, 'UTF-8') }}

                    <div class="datos-firma">
                        Firma autorizada y sello

                        <br>
                        LA ASOCIACIÓN
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="pie">
        <strong>Visión:</strong>

        Ser reconocidos como una asociación sólida y sostenible,
        referente de unidad y apoyo mutuo.
    </div>

</body>
</html>
