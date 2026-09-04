<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Constancia</title>

    <style>

        @page {
            margin: 20px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | ENCABEZADO
        |--------------------------------------------------------------------------
        */

        .header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 2px solid #1b5e3b;
        }

        .logo {
            width: 75px;
            height: auto;
            margin-bottom: 5px;
        }

        .municipalidad {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .ruc {
            color: #666;
            font-size: 9px;
            margin-top: 3px;
        }

        .direccion {
            color: #666;
            font-size: 9px;
            margin-top: 2px;
        }

        .titulo {
            color: #1b5e3b;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .web {
            color: #888;
            font-size: 8px;
            margin-top: 3px;
        }

        /*
        |--------------------------------------------------------------------------
        | SECCIONES
        |--------------------------------------------------------------------------
        */

        .section {
            margin-top: 12px;
        }

        .section-title {
            background: #f3f4f6;
            border-left: 3px solid #1b5e3b;
            padding: 5px 7px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 5px;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLAS
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 3px 2px;
            vertical-align: top;
        }

        td.label {
            width: 38%;
            color: #666;
            font-size: 8px;
            font-weight: bold;
        }

        td.value {
            width: 62%;
            color: #222;
            font-size: 9px;
        }

        /*
        |--------------------------------------------------------------------------
        | SEPARADOR
        |--------------------------------------------------------------------------
        */

        .separator {
            border-top: 1px dashed #aaa;
            margin: 10px 0;
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO
        |--------------------------------------------------------------------------
        */

        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
        }

        .badge-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-free {
            background: #e0f2fe;
            color: #075985;
        }

        .badge-repro {
            background: #fef3c7;
            color: #92400e;
        }

        .aviso-repro {
            margin-top: 10px;
            border: 1.5px solid #b45309;
            background: #fffbeb;
            color: #92400e;
            padding: 6px 8px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .anulado {
            color: #92400e;
            text-decoration: line-through;
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        .total {
            margin-top: 14px;
            border: 1.5px solid #222;
            width: 100%;
        }

        .total td {
            padding: 7px;
            font-weight: bold;
            font-size: 10px;
        }

        .total-label {
            width: 60%;
        }

        .total-value {
            width: 40%;
            text-align: right;
            font-size: 12px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 8px;
        }

        .thanks {
            color: #222;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 4px;
        }

        .small {
            font-size: 8px;
        }

    </style>

</head>

<body>

@php
    $esRepro = ($pagoSeleccionado['tipo'] ?? 'pago') === 'reprogramacion';
@endphp

<div class="container">

    {{-- ==========================================================
         ENCABEZADO
         ========================================================== --}}

    <div class="header">

        @php
            $logoPath = public_path('logo_municipal_negro.png');
        @endphp

        @if (file_exists($logoPath))

            <img
                src="{{ $logoPath }}"
                class="logo"
                alt="La Molina"
            >

        @endif

        <div class="municipalidad">
            MUNICIPALIDAD DE LA MOLINA
        </div>

        <div class="ruc">
            RUC 20131372175
        </div>

        <div class="direccion">
            Av. Elías Aparicio 740 - La Molina
        </div>

        <div class="titulo">
            @if ($esRepro)
                Constancia de reprogramación
            @else
                Reserva de canchas deportivas
            @endif
        </div>

        <div class="web">
             {{ request()->getHost() }}
        </div>

    </div>


    @if ($esRepro)

        <div class="aviso-repro">
            Reserva reprogramada
        </div>

    @endif


    {{-- ==========================================================
         INFORMACIÓN DEL PAGO
         ========================================================== --}}

    <div class="section">

        <div class="section-title">
            @if ($esRepro)
                Información de la reprogramación
            @else
                Información del pago
            @endif
        </div>

        <table>

            <tr>

                <td class="label">
                    N° PEDIDO
                </td>

                <td class="value">
                    <strong>
                        {{ $pagoSeleccionado['nro_pedido'] }}
                    </strong>
                </td>

            </tr>

            @if ($esRepro)

                <tr>

                    <td class="label">
                        N° REPROGRAMACIÓN
                    </td>

                    <td class="value">
                        <strong>
                            {{ $pagoSeleccionado['nro_reprogramacion'] }}
                        </strong>
                    </td>

                </tr>

            @endif

            <tr>

                <td class="label">
                    CÓDIGO VOUCHER
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['codigo_voucher'] ?? '—' }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    N° OPERACIÓN
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['nro_operacion'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    @if ($esRepro)
                        FECHA DEL CAMBIO
                    @else
                        FECHA Y HORA
                    @endif
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['fecha_pago'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    ESTADO
                </td>

                <td class="value">

                    @if ($esRepro)

                        <span class="badge badge-repro">
                            REPROGRAMADO
                        </span>

                    @elseif ($pagoSeleccionado['estado'] === 'Pagado')

                        <span class="badge badge-paid">
                            PAGADO
                        </span>

                    @elseif ($pagoSeleccionado['estado'] === 'Gratuito')

                        <span class="badge badge-free">
                            GRATUITO
                        </span>

                    @else

                        {{ $pagoSeleccionado['estado'] }}

                    @endif

                </td>

            </tr>

        </table>

    </div>


    <div class="separator"></div>


    {{-- ==========================================================
         PAGADO POR
         ========================================================== --}}

    <div class="section">

        <div class="section-title">
            Pagado por
        </div>

        <table>

            <tr>

                <td class="label">
                    NOMBRE
                </td>

                <td class="value">

                    <strong>
                        {{ $pagoSeleccionado['titular'] }}
                    </strong>

                </td>

            </tr>

            <tr>

                <td class="label">
                    DNI
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['dni'] }}
                </td>

            </tr>

        </table>

    </div>


    <div class="separator"></div>


    {{-- ==========================================================
         DETALLE DE RESERVA
         ========================================================== --}}

    <div class="section">

        <div class="section-title">
            @if ($esRepro)
                Nuevo turno
            @else
                Detalle de la reserva
            @endif
        </div>

        <table>

            <tr>

                <td class="label">
                    CONCEPTO
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['concepto'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    SEDE
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['sede'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    CANCHA
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['cancha'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    DEPORTE
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['deporte'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    FECHA TURNO
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['fecha_turno'] }}
                </td>

            </tr>

            <tr>

                <td class="label">
                    HORARIO
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['horario'] }}
                </td>

            </tr>

            @if ($esRepro)

                <tr>

                    <td class="label">
                        TURNO ANTERIOR
                    </td>

                    <td class="value anulado">
                        {{ $pagoSeleccionado['cancha_anterior'] }} ·
                        {{ $pagoSeleccionado['turno_anterior'] }}
                    </td>

                </tr>

                @if (! empty($pagoSeleccionado['motivo']))

                    <tr>

                        <td class="label">
                            MOTIVO
                        </td>

                        <td class="value">
                            {{ $pagoSeleccionado['motivo'] }}
                        </td>

                    </tr>

                @endif

            @endif

            <tr>

                <td class="label">
                    MEDIO DE PAGO
                </td>

                <td class="value">
                    {{ $pagoSeleccionado['medio_pago'] }}
                </td>

            </tr>

        </table>

    </div>


    {{-- ==========================================================
         TOTAL
         ========================================================== --}}

    <table class="total">

        <tr>

            <td class="total-label">
                @if ($esRepro)
                    YA PAGADO (SIN COSTO ADICIONAL)
                @else
                    TOTAL PAGADO
                @endif
            </td>

            <td class="total-value">
                S/
                {{ number_format($pagoSeleccionado['monto'], 2) }}
            </td>

        </tr>

    </table>


    {{-- ==========================================================
         FOOTER
         ========================================================== --}}

    <div class="footer">

        @if ($esRepro)

            <div class="thanks">
                Tu reserva fue reprogramada
            </div>

            <div>
                Presenta este documento el día de tu nuevo turno.
                Reemplaza al horario de la constancia de pago original.
            </div>

        @else

            <div class="thanks">
                ¡Gracias por tu reserva!
            </div>

            <div>
                Conserve este documento como constancia de pago.
            </div>

        @endif

        <div style="margin-top: 4px;">
            Canchas Deportivas — La Molina
        </div>

    </div>

</div>

</body>

</html>