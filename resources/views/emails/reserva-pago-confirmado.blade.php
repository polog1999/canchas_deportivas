<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de reserva</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f4;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6f4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:#1b5e3b;padding:20px 24px;color:#ffffff;">
                            <h1 style="margin:0;font-size:18px;font-weight:700;">Municipalidad de La Molina</h1>
                            <p style="margin:6px 0 0;font-size:13px;opacity:.9;">Reserva de canchas deportivas</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;font-size:15px;">
                                Hola{{ !empty($detalle['titular']) ? ' '.$detalle['titular'] : '' }},
                            </p>

                            @if ($usuarioNuevo)
                                <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
                                    Tu pago fue registrado correctamente. Se creó tu cuenta en el sistema y adjuntamos el comprobante en PDF.
                                </p>
                                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:16px;">
                                    <tr>
                                        <td style="padding:16px;font-size:14px;line-height:1.8;">
                                            <strong>Usuario:</strong> {{ $usuarioLogin }}<br>
                                            <strong>Contraseña temporal:</strong> {{ $clavePlana }}<br>
                                            <span style="color:#64748b;font-size:12px;">Te recomendamos cambiar tu contraseña al ingresar.</span>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
                                    Tu pago fue registrado correctamente. Adjuntamos el comprobante en PDF y estos son los datos de tu reserva:
                                </p>
                            @endif

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:16px;">
                                <tr>
                                    <td style="padding:16px;font-size:14px;line-height:1.8;">
                                        <strong>Voucher:</strong> {{ $detalle['voucher'] }}<br>
                                        @if (!empty($detalle['club']))
                                            <strong>Sede:</strong> {{ $detalle['club'] }}<br>
                                        @endif
                                        @if (!empty($detalle['cancha']))
                                            <strong>Cancha:</strong> {{ $detalle['cancha'] }}<br>
                                        @endif
                                        @if (!empty($detalle['deporte']))
                                            <strong>Deporte:</strong> {{ $detalle['deporte'] }}<br>
                                        @endif
                                        @if (!empty($detalle['fecha']))
                                            <strong>Fecha:</strong> {{ $detalle['fecha'] }}<br>
                                        @endif
                                        @if (!empty($detalle['hora_inicio']) && !empty($detalle['hora_fin']))
                                            <strong>Horario:</strong> {{ $detalle['hora_inicio'] }} a {{ $detalle['hora_fin'] }} hs<br>
                                        @endif
                                        <strong>Total pagado:</strong> S/ {{ number_format((float) $detalle['monto'], 2) }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:12px;color:#64748b;line-height:1.6;">
                                Presenta tu documento de identidad al momento de usar la cancha.
                                Si no realizaste esta reserva, ignora este mensaje.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;text-align:center;">
                            Municipalidad de La Molina — Canchas deportivas
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
