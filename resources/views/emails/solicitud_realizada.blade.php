<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Solicitud de Asociación</title>
</head>

<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">

                <table width="650" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:8px;overflow:hidden;">

                    <!-- Encabezado -->
                    <tr>
                        <td align="center"
                            style="background:#0b4ea2;padding:25px;color:#ffffff;">

                            <img src="{{ public_path('storage/iconos/logo.jpg') }}" class="logo">

                            <h2 style="margin:15px 0 5px 0;">
                                Asociación de Jubilados y Pensionados
                            </h2>

                            <h3 style="margin:0;">
                                Municipales del Paraguay
                            </h3>

                        </td>
                    </tr>

                    <!-- Contenido -->
                    <tr>
                        <td style="padding:35px;">

                            <h2 style="color:#0b4ea2;margin-top:0;">
                                Solicitud recibida correctamente
                            </h2>

                            <p>
                                Estimado/a
                                <strong>{{ $solicitud->nombre }} {{ $solicitud->apellido }}</strong>,
                            </p>

                            <p>
                                Hemos recibido correctamente su solicitud de asociación.
                            </p>

                            <table width="100%" cellpadding="8" cellspacing="0"
                                style="margin:20px 0;border:1px solid #ddd;border-collapse:collapse;">

                                <tr style="background:#f8f8f8;">
                                    <td><strong>Número de Solicitud</strong></td>
                                    <td>
                                        {{ str_pad($solicitud->numero_solicitud, 7, '0', STR_PAD_LEFT) }}/{{$solicitud->anio}}
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Fecha</strong></td>
                                    <td>{{ date('d/m/Y') }}</td>
                                </tr>

                                <tr style="background:#f8f8f8;">
                                    <td><strong>Documento</strong></td>
                                    <td>{{ $solicitud->documento }}</td>
                                </tr>

                            </table>

                            <p>
                                Su solicitud será revisada por nuestros funcionarios.
                            </p>

                            <p>
                                Una vez concluido el proceso recibirá otro correo
                                notificando si fue aprobada o si es necesaria alguna
                                información adicional.
                            </p>

                            <div style="margin-top:30px;padding:15px;background:#eef5ff;border-left:5px solid #0b4ea2;">

                                📄 Se adjunta una copia de la solicitud en formato PDF.

                            </div>

                            <p style="margin-top:35px;">
                                Muchas gracias por confiar en nuestra institución.
                            </p>

                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td align="center"
                            style="background:#f5f5f5;padding:20px;font-size:13px;color:#666;">

                            Asociación de Jubilados y Pensionados Municipales del Paraguay

                            <br><br>

                            Este es un correo automático. Favor no responder este mensaje.

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
