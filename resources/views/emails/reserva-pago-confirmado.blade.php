<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de reserva</title>
</head>

<body style="margin:0;padding:0;background-color:#f3f6f4;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

    <table
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="width:100%;background-color:#f3f6f4;padding:24px 12px;"
    >
        <tr>
            <td align="center" style="padding:0;">

                <table
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="width:100%;max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;"
                >

                    {{-- ENCABEZADO --}}
                    <tr>
                        <td
                            style="padding:16px 24px;background-color:#1b5e3b;"
                        >

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>

                                    <td
                                        style="padding:0;vertical-align:middle;"
                                    >
                                        <a
                                            href="{{ url('/') }}"
                                            style="text-decoration:none;display:inline-block;"
                                        >

                                            <table
                                                cellpadding="0"
                                                cellspacing="0"
                                                border="0"
                                            >
                                                <tr>

                                                    {{-- Logo --}}
                                                    <td
                                                        style="padding:0;vertical-align:middle;"
                                                    >
                                                        <img
                                                            src="{{ $message->embed(public_path('logo_municipal_negro2.png')) }}"
                                                            alt="Municipalidad de La Molina"
                                                            width="auto"
                                                            height="60"
                                                            style="height:60px;width:auto;border-radius:8px;display:block;object-fit:contain;border:0;"
                                                        >
                                                    </td>

                                                    {{-- Separador --}}
                                                    <td
                                                        style="padding:0 16px;vertical-align:middle;"
                                                    >
                                                        <div
                                                            style="width:2px;height:48px;background-color:rgba(255,255,255,0.70);"
                                                        ></div>
                                                    </td>

                                                    {{-- Nombre --}}
                                                    <td
                                                        style="padding:0;vertical-align:middle;"
                                                    >
                                                        <div
                                                            style="font-size:18px;line-height:1.2;font-weight:700;color:#ffffff;white-space:nowrap;"
                                                        >
                                                            Canchas Deportivas
                                                        </div>
                                                    </td>

                                                </tr>
                                            </table>

                                        </a>
                                    </td>

                                </tr>
                            </table>

                        </td>
                    </tr>


                    {{-- CONTENIDO --}}
                    <tr>
                        <td style="padding:24px;">

                            <p
                                style="margin:0 0 12px;font-size:15px;line-height:1.5;color:#1e293b;"
                            >
                                Hola{{ !empty($detalle['titular']) ? ' '.$detalle['titular'] : '' }},
                            </p>

                            @if ($usuarioNuevo)

                                <p
                                    style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#1e293b;"
                                >
                                    Tu pago fue registrado correctamente. Se creó tu cuenta en el sistema con los siguientes datos de acceso.
                                    Adjuntamos el comprobante (voucher) en PDF.
                                </p>

                                <table
                                    width="100%"
                                    cellpadding="0"
                                    cellspacing="0"
                                    border="0"
                                    style="width:100%;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:16px;"
                                >
                                    <tr>
                                        <td
                                            style="padding:16px;font-size:14px;line-height:1.8;color:#1e293b;"
                                        >
                                            <strong>Correo electrónico:</strong>
                                            {{ $usuarioLogin }}
                                            <br>

                                            <strong>Contraseña temporal:</strong>
                                            {{ $clavePlana }}
                                            <br>

                                            <span
                                                style="color:#64748b;font-size:12px;line-height:1.5;"
                                            >
                                                Ingresa al portal con tu correo y esta contraseña. Te recomendamos cambiarla al ingresar.
                                            </span>
                                        </td>
                                    </tr>
                                </table>

                            @else

                                <p
                                    style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#1e293b;"
                                >
                                    Tu pago fue registrado correctamente. Adjuntamos el comprobante (voucher) en PDF y estos son los datos de tu reserva:
                                </p>

                            @endif


                            {{-- DATOS DE RESERVA --}}
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="width:100%;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:16px;"
                            >
                                <tr>
                                    <td
                                        style="padding:16px;font-size:14px;line-height:1.8;color:#1e293b;"
                                    >

                                        <strong>Voucher:</strong>
                                        {{ $detalle['voucher'] }}
                                        <br>

                                        @if (!empty($detalle['club']))
                                            <strong>Sede:</strong>
                                            {{ $detalle['club'] }}
                                            <br>
                                        @endif

                                        @if (!empty($detalle['cancha']))
                                            <strong>Cancha:</strong>
                                            {{ $detalle['cancha'] }}
                                            <br>
                                        @endif

                                        @if (!empty($detalle['deporte']))
                                            <strong>Deporte:</strong>
                                            {{ $detalle['deporte'] }}
                                            <br>
                                        @endif

                                        @if (!empty($detalle['fecha']))
                                            <strong>Fecha:</strong>
                                            {{ $detalle['fecha'] }}
                                            <br>
                                        @endif

                                        @if (!empty($detalle['hora_inicio']) && !empty($detalle['hora_fin']))
                                            <strong>Horario:</strong>
                                            {{ $detalle['hora_inicio'] }} a {{ $detalle['hora_fin'] }} h
                                            <br>
                                        @endif

                                        <strong>Total pagado:</strong>
                                        S/ {{ number_format((float) $detalle['monto'], 2) }}

                                    </td>
                                </tr>
                            </table>


                            {{-- AVISO --}}
                            <p
                                style="margin:0;font-size:12px;color:#64748b;line-height:1.6;"
                            >
                                Presenta tu documento de identidad al momento de usar la cancha.
                                Si no realizaste esta reserva, ignora este mensaje.
                            </p>

                        </td>
                    </tr>


                    {{-- PIE --}}
                    <tr>
                        <td
                            style="padding:16px 24px;background-color:#f8fafc;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;text-align:center;line-height:1.5;"
                        >
                            Municipalidad de La Molina — Canchas deportivas
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>