{{-- resources/views/solicitud/pdf.blade.php --}}

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="{{ asset('css/pdf.css') }}">
    </head>
    <body>

        {{-- HOJA 1 --}}
        <div class="header">

        <table class="header-table">
            <tr>
                {{-- LOGO IZQUIERDA --}}
                <td style="width: 20%;">
                    <img src="{{ public_path('storage/iconos/logo.jpg') }}" class="logo">
                </td>

                {{-- TITULO CENTRADO --}}
                <td style="width: 80%; text-align: center;">
                    <div class="datos-entidad">
                        Tel: {{ $entidad->telefono ?? '' }} <br>
                        Dirección: {{ $entidad->direccion ?? '' }} <br>
                        Correo: {{ $entidad->email ?? '' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="mision">
            Misión: "{{ $entidad->mision }}"
        </div>

        </div>

        <div class="row">
            <span class="label">Fecha:</span>
            <span class="linea-corta">{{ date('d/m/Y', strtotime($solicitud->fecha_solicitud)) }}</span>
        </div>

        <div class="titulo-banda">Solicitud de admisión del socio</div>

        <table class="datos-tabla">
            <tr>
                <td colspan="3">
                    <span class="dato-label">Nombre y Apellido:</span>
                    <span class="dato-linea">{{ $solicitud->nombre }} {{ $solicitud->apellido }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">C.I. N°:</span>
                    <span class="dato-linea">{{ $solicitud->documento }}</span>
                </td>
                <td>
                    <span class="dato-label">Fecha Nac.:</span>
                    <span class="dato-linea">{{ date('d/m/Y', strtotime($solicitud->fecha_nacimiento)) }}</span>
                </td>
                <td>
                    <span class="dato-label">Sexo:</span>
                    <span class="dato-linea">{{ $solicitud->sexo->descripcion ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">Estado civil:</span>
                    <span class="dato-linea">{{ $solicitud->estado_civil->descripcion ?? '' }}</span>
                </td>
                <td>
                    <span class="dato-label">Departamento:</span>
                    <span class="dato-linea">{{ $solicitud->departamento->descripcion ?? '' }}</span>
                </td>
                <td>
                    <span class="dato-label">Distrito:</span>
                    <span class="dato-linea">{{ $solicitud->distrito->descripcion ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">Ciudad:</span>
                    <span class="dato-linea">{{ $solicitud->ciudad->descripcion ?? '' }}</span>
                </td>
                <td colspan="2">
                    <span class="dato-label">Institución municipal:</span>
                    <span class="dato-linea">{{ $solicitud->institucion->descripcion ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="dato-label">Domicilio:</span>
                    <span class="dato-linea">{{ $solicitud->direccion }}</span>
                </td>
                <td>
                    <span class="dato-label">Barrio:</span>
                    <span class="dato-linea">{{ $solicitud->barrio }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">Teléfono:</span>
                    <span class="dato-linea">{{ $solicitud->celular }}</span>
                </td>
                <td colspan="2">
                    <span class="dato-label">Email:</span>
                    <span class="dato-linea">{{ $solicitud->email }}</span>
                </td>
            </tr>
        </table>

        <div class="titulo-banda">Datos adicionales</div>

        @if($solicitud->familiares->count() > 0)

            <table class="datos-tabla">
                <thead>
                    <tr>
                        <th>Tipo familiar</th>
                        <th>C.I.</th>
                        <th>Nombre y Apellido</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitud->familiares as $familiar)
                        <tr>
                            <td>{{ $familiar->tipo_familia->descripcion ?? '' }}</td>
                            <td>{{ $familiar->documento }}</td>
                            <td>{{ $familiar->nombre }} {{ $familiar->apellido }}</td>
                            <td>{{ $familiar->celular }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @else

            <div style="margin-top:10px; font-style: italic;">
                No se registraron familiares.
            </div>

        @endif

        @php
            $tipoIngreso = ($solicitud->tipo_asociado_id == 3)
                ? 'salario o remuneración'
                : 'haber jubilatorio';
        @endphp

        <div class="titulo-banda">AUTORIZACIÓN DE DESCUENTO</div>

        <div class="bloque-autorizacion">
            <strong>
                Autorizo suficientemente el descuento de mi {{ $tipoIngreso }},
                lo correspondiente en concepto de cuota social y otros descuentos,
                y que sea incluido en la planilla mensual de descuentos remitidos
                a la Caja de Jubilaciones y Pensiones del Personal Municipal.
            </strong>
        </div>


        <div style="margin-top: 30px; width: 100%;">

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; text-align: center;">
                        _______________________________<br>
                        Firma del socio
                    </td>
                    <td style="width: 50%;"></td>
                </tr>
            </table>

            <table style="width: 100%; margin-top: 25px; border-collapse: collapse;">
                <tr>
                    <td style="width: 60%;">
                        <strong>Admitido en sesión de fecha:</strong>
                        <span style="display:inline-block; border-bottom:1px solid #444; width: 180px;">
                            {{ $solicitud->fecha_aprobacion_o_rechazo ? date('d/m/Y', strtotime($solicitud->fecha_aprobacion_o_rechazo)) : '' }}
                        </span>
                    </td>

                    <td style="width: 40%;">
                        <strong>Acta N°:</strong>
                        <span style="display:inline-block; border-bottom:1px solid #444; width: 120px;">
                            {{ $solicitud->acta ?? '' }}
                        </span>
                    </td>
                </tr>
            </table>

        </div>

        <div class="footer-vision">
            Visión: "{{ $entidad->vision }}"
        </div>

        <div class="page-break"></div>

        <table class="header-ficha">
            <tr>
                <td style="width: 35%;">
                    <img src="{{ public_path('storage/iconos/logo.jpg') }}" class="logo-ficha">
                </td>

                <td style="width: 40%;">
                    <div class="mision-ficha">
                        Misión: "{{ $entidad->mision }}"
                    </div>
                </td>

                <td style="width: 25%;">
                    <div class="info-ficha">
                        <strong>Fecha:</strong> {{ date('d/m/Y', strtotime($solicitud->fecha_solicitud)) }} <br>
                        <strong>Mes:</strong> {{ date('m', strtotime($solicitud->fecha_solicitud)) }}
                        &nbsp;&nbsp;
                        <strong>Año:</strong> {{ $solicitud->anio }} <br>
                        <strong>N° de Socio:</strong>
                        {{ $solicitud->numero_socio > 0 ? $solicitud->numero_socio : '' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="titulo-banda">FICHA BÁSICA DEL SOCIO (DATOS MÉDICOS Y SOCIALES)</div>

        <table class="datos-tabla">
            <tr>
                <td colspan="3">
                    <span class="dato-label">Nombre y Apellido:</span>
                    <span class="dato-linea">{{ $solicitud->nombre }} {{ $solicitud->apellido }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">C.I. N°:</span>
                    <span class="dato-linea">{{ $solicitud->documento }}</span>
                </td>
                <td>
                    <span class="dato-label">Fecha Nac.:</span>
                    <span class="dato-linea">{{ date('d/m/Y', strtotime($solicitud->fecha_nacimiento)) }}</span>
                </td>
                <td>
                    <span class="dato-label">Sexo:</span>
                    <span class="dato-linea">{{ $solicitud->sexo->descripcion ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <strong>Estado Civil:</strong>
                    @foreach ($civil as $item)
                        {{ $item->descripcion }} <span class="check">{{ ($solicitud->estado_civil_id) == $item->id  ? 'X' : '' }}</span>
                    @endforeach
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <strong>Asociado:</strong>
                    @foreach ($tiposAsociados as $item)
                        {{ $item->descripcion }} <span class="check">{{ ($solicitud->tipo_asociado_id) == $item->id  ? 'X' : '' }}</span>
                    @endforeach
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <span class="dato-label">Dirección:</span>
                    <span class="dato-linea">{{ $solicitud->direccion }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">Barrio:</span>
                    <span class="dato-linea">{{ $solicitud->barrio }}</span>
                </td>
                <td colspan="2">
                    <span class="dato-label">Ciudad:</span>
                    <span class="dato-linea">{{ $solicitud->ciudad->descripcion ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dato-label">Teléfono:</span>
                    <span class="dato-linea">{{ $solicitud->celular }}</span>
                </td>
                <td colspan="2">
                    <span class="dato-label">Email:</span>
                    <span class="dato-linea">{{ $solicitud->email }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="dato-label">Vivienda (Descripción):</span>
                    <span class="campo-alto">{{ $solicitud->vivienda }}</span>
                </td>

                <td>
                    <strong>Tipo de vivienda</strong><br>
                    Propia <span class="check">{{ $solicitud->tipo_vivienda_id == 1 ? 'X' : '' }}</span><br>
                    Alquilada <span class="check">{{ $solicitud->tipo_vivienda_id == 2 ? 'X' : '' }}</span>
                </td>
            </tr>
        </table>

        @php
            $ficha = $solicitud->fichaMedica;
        @endphp

        <table class="datos-tabla mt-10">
            <tr>
                <td colspan="3">
                    <strong>Enfermedad que padece:</strong>

                    Cáncer <span class="check">{{ $ficha && $ficha->cancer ? 'X' : '' }}</span>
                    Diabetes <span class="check">{{ $ficha && $ficha->diabetes ? 'X' : '' }}</span>
                    Presión alta <span class="check">{{ $ficha && $ficha->presion_alta ? 'X' : '' }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <span class="dato-label">Otros:</span>
                    <span class="dato-linea">{{ $ficha->otro ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <span class="dato-label">Medicamento que consume:</span>
                    <span class="dato-linea">{{ $ficha->medicamentos ?? '' }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <strong>Seguro Médico:</strong>

                    Particular <span class="check">{{ $ficha && $ficha->seguro_particular ? 'X' : '' }}</span>
                    IPS <span class="check">{{ $ficha && $ficha->seguro_ips ? 'X' : '' }}</span>
                    Ninguno <span class="check">{{ $ficha && $ficha->seguro_ninguno ? 'X' : '' }}</span>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <span class="dato-label">Observación:</span>
                    <span class="campo-alto">{{ $ficha->observacion ?? '' }}</span>
                </td>
            </tr>
        </table>

        <div style="margin-top: 35px; text-align:center;">
            _______________________________<br>
            Firma del socio
        </div>

        <div class="footer-vision">
            Visión: "{{ $entidad->vision }}"
        </div>
        <div class="page-break"></div>

        {{-- HOJA 3 --}}
        <div class="header">
            <div class="entidad">{{ $entidad->razon_social }}</div>
        </div>

        <div class="doc-title">Documento frente</div>
        <div class="img-container">
            <img class="foto-doc" src="{{ storage_path('app/public/'.$solicitud->documento_frente) }}">
        </div>

        <div class="doc-title">Documento reverso</div>
        <div class="img-container">
            <img class="foto-doc" src="{{ storage_path('app/public/'.$solicitud->documento_reverso) }}">
        </div>

        <div class="doc-title">Selfie</div>
        <div class="img-container">
            <img class="foto-selfie" src="{{ storage_path('app/public/'.$solicitud->selfi) }}">
        </div>

    </body>
</html>
