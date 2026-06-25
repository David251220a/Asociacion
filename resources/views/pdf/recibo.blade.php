<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Dinero</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .contenedor {
            width: 100%;
            border: 1px solid #000;
            padding: 12px;
        }

        .header {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .logo {
            width: 180px;
        }

        .titulo {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .subtitulo {
            text-align: center;
            font-size: 11px;
        }

        .numero {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        .datos {
            margin-top: 10px;
            line-height: 1.1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #eee;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
        }

        .firmas {
            margin-top: 60px;
            width: 100%;
        }

        .firma {
            width: 45%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

       .marca-agua {
            position: fixed;
            top: 40%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 120px;
            font-weight: bold;
            color: #ff0000;
            opacity: 0.3;
            transform: rotate(-35deg);
        }
    </style>
</head>
<body>
    @if($recibo->estado_id == 2)
        <div class="marca-agua">
            ANULADO
        </div>
    @endif

    <div class="contenedor">

        <table class="header">
            <tr>
                <td width="20%" style="border: none;">
                    {{-- Si tenés logo --}}
                    <img src="{{ public_path('storage/iconos/logo.jpg') }}" class="logo">
                </td>

                <td width="55%" style="border: none;">
                    <div class="titulo">{{ $entidad->razon_social ?? 'ENTIDAD' }}</div>
                    <div class="subtitulo">
                        RUC: {{ $entidad->ruc ?? '' }} <br>
                        {{ $entidad->direccion ?? '' }} <br>
                        Tel.: {{ $entidad->telefono ?? '' }}
                    </div>
                </td>

                <td width="45%" style="border: none;">
                    <div class="numero">
                        RECIBO<br>
                        N° {{$recibo->sucursal}}-{{$recibo->general}}-{{ str_pad($recibo->numero ?? $recibo->id, 7, '0', STR_PAD_LEFT) }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="datos">
            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($recibo->fecha_factura)->format('d/m/Y') }} <br>

            <strong>Recibimos de:</strong>
            {{ $recibo->persona->nombre ?? '' }}
            {{ $recibo->persona->apellido ?? '' }} <br>

            <strong>Documento/RUC:</strong>
            {{ $recibo->persona->documento ?? $recibo->persona->ruc ?? '' }} <br>

            <strong>La suma de Gs.:</strong>
            {{ number_format($recibo->monto_total, 0, ',', '.') }}
        </div>

        <table>
            <thead>
                <tr>
                    <th width="8%">Cant.</th>
                    <th>Concepto</th>
                    <th width="20%">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td class="text-center">{{ $item->cantidad }}</td>
                        <td>{{ $item->descripcion }}</td>
                        <td class="text-right">
                            {{ number_format($item->total, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right total">TOTAL</td>
                    <td class="text-right total">
                        {{ number_format($recibo->monto_total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
        @foreach ($recibo->forma_pagos as $item)
            <div class="datos">
                <strong>Forma de pago:</strong>
                {{ $item->forma_cobro->descripcion}} <br>
                <strong>Fecha:</strong>
                {{ date('d/m/Y', strtotime($item->fecha)) }} <br>
                <strong>Banco:</strong>
                {{ $item->banco->descripcion}} <br>
                <strong>Nro. Comprobante:</strong>
                {{ $item->numero_comprobante}} <br>
                <strong>Monto:</strong>
                {{ number_format($item->monto, 0, ',', '.')}} <br>
            </div>
        @endforeach
        <br>
        <strong>Observación:</strong>
        {{ $recibo->observacion ?? 'Sin observación' }}

        <table class="firmas">
            <tr>
                <td style="border: none;"></td>
                <td class="firma">
                    Firma y sello
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
