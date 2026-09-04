<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva reprogramada</title>
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

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
                                Tu reserva fue reprogramada. El monto ya pagado se mantiene y no hay ningún
                                cobro adicional. Adjuntamos la constancia de reprogramación en PDF.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;margin-bottom:16px;">
                                <tr>
                                    <td style="padding:16px;font-size:14px;line-height:1.8;">
                                        <span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;letter-spacing:.5px;">NUEVO TURNO</span>
                                        <br><br>
                                        @if (!empty($detalle['sede']))
                                            <strong>Sede:</strong> {{ $detalle['sede'] }}<br>
                                        @endif
                                        <strong>Cancha:</strong> {{ $detalle['cancha'] }}<br>
                                        <strong>Fecha:</strong> {{ $detalle['fecha'] }}<br>
                                        <strong>Horario:</strong> {{ $detalle['hora_inicio'] }} a {{ $detalle['hora_fin'] }} hs
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:16px;">
                                <tr>
                                    <td style="padding:16px;font-size:14px;line-height:1.8;">
                                        <strong>Voucher:</strong> {{ $detalle['voucher'] }}<br>
                                        <strong>Turno anterior:</strong>
                                        <span style="color:#94a3b8;text-decoration:line-through;">
                                            {{ $detalle['cancha_anterior'] }} · {{ $detalle['turno_anterior'] }}
                                        </span><br>
                                        @if (!empty($detalle['motivo']))
                                            <strong>Motivo:</strong> {{ $detalle['motivo'] }}<br>
                                        @endif
                                        <strong>Monto ya pagado:</strong> S/ {{ number_format((float) $detalle['monto'], 2) }}
                                        <span style="color:#64748b;font-size:12px;">(sin costo adicional)</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:12px;color:#64748b;line-height:1.6;">
                                Presenta esta constancia y tu documento de identidad el día de tu nuevo turno.
                                Reemplaza al horario que figura en tu comprobante de pago original.
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
