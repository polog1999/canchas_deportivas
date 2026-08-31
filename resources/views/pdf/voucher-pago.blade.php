<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Voucher {{ $voucher['codigo_voucher'] ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
        }
        .center { text-align: center; }
        .logo { height: 48px; margin-bottom: 8px; }
        .title { font-size: 11px; font-weight: bold; letter-spacing: 0.5px; margin: 0; }
        .subtitle { font-size: 9px; color: #64748b; margin: 2px 0; }
        .section-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin: 14px 0 6px;
        }
        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 12px 0;
        }
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail td {
            padding: 3px 0;
            vertical-align: top;
        }
        table.detail td.label {
            width: 42%;
            color: #64748b;
        }
        table.detail td.value {
            font-weight: bold;
        }
        .total-box {
            width: 100%;
            border: 2px solid #1e293b;
            border-collapse: collapse;
            margin-top: 16px;
        }
        .total-box td {
            padding: 8px 10px;
            font-weight: bold;
        }
        .total-box td.amount {
            text-align: right;
            font-size: 13px;
        }
        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 10px;
        }
        .footer-muted { color: #94a3b8; font-size: 9px; }
        .brand { color: #1b5e3b; font-weight: bold; text-transform: uppercase; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center">
        @if ($logoBase64)
            <img src="{{ $logoBase64 }}" alt="La Molina" class="logo">
        @endif
        <p class="title">MUNICIPALIDAD DE LA MOLINA</p>
        <p class="subtitle">RUC 20131372175</p>
        <p class="subtitle">Av. Elías Aparicio 740 - La Molina</p>
        <p class="brand" style="margin-top: 10px;">Reserva de canchas deportivas</p>
        <p class="subtitle">molicanchas.munimolina.gob.pe</p>
    </div>

    <div class="divider"></div>

    <table class="detail">
        <tr>
            <td class="label">N° PEDIDO:</td>
            <td class="value">{{ $voucher['nro_pedido'] }}</td>
        </tr>
        <tr>
            <td class="label">CÓDIGO VOUCHER:</td>
            <td class="value">{{ $voucher['codigo_voucher'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">N° OPERACIÓN:</td>
            <td class="value">{{ $voucher['nro_operacion'] }}</td>
        </tr>
        <tr>
            <td class="label">FECHA Y HORA DEL PAGO:</td>
            <td class="value">{{ $voucher['fecha_pago'] }} (hora Perú)</td>
        </tr>
    </table>

    <div class="divider"></div>
    <p class="section-title">Pagado por</p>
    <table class="detail">
        <tr>
            <td class="label">NOMBRE:</td>
            <td class="value">{{ $voucher['titular'] }}</td>
        </tr>
        <tr>
            <td class="label">DNI:</td>
            <td class="value">{{ $voucher['dni'] }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <p class="section-title">Detalle</p>
    <table class="detail">
        <tr>
            <td class="label">CONCEPTO:</td>
            <td class="value">{{ $voucher['concepto'] }}</td>
        </tr>
        <tr>
            <td class="label">SEDE:</td>
            <td class="value">{{ $voucher['sede'] }}</td>
        </tr>
        <tr>
            <td class="label">CANCHA:</td>
            <td class="value">{{ $voucher['cancha'] }}</td>
        </tr>
        <tr>
            <td class="label">DEPORTE:</td>
            <td class="value">{{ $voucher['deporte'] }}</td>
        </tr>
        <tr>
            <td class="label">TURNO RESERVADO:</td>
            <td class="value">{{ $voucher['fecha_turno'] }} · {{ $voucher['horario'] }}</td>
        </tr>
        <tr>
            <td class="label">MEDIO:</td>
            <td class="value">{{ $voucher['medio_pago'] }}</td>
        </tr>
    </table>

    <table class="total-box">
        <tr>
            <td>TOTAL PAGADO</td>
            <td class="amount">S/ {{ number_format((float) $voucher['monto'], 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p style="font-weight: bold; margin: 0;">¡Gracias por tu reserva!</p>
        <p class="footer-muted">Conserve este comprobante como constancia.</p>
        <p class="footer-muted">Canchas Deportivas — La Molina</p>
    </div>
</body>
</html>
