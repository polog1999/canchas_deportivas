<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('code', 'Error') - Canchas Deportivas</title>

    @php
        /*
        |--------------------------------------------------------------------------
        | IMAGEN SEGÚN CÓDIGO DEL ERROR
        |--------------------------------------------------------------------------
        |
        | Puedes usar:
        | .svg
        | .png
        | .jpg
        | .jpeg
        |
        | Si el error no tiene una imagen definida, no se muestra nada.
        |
        */

        $errorCode = (int) trim($__env->yieldContent('code', '500'));

        $errorImage = match ($errorCode) {
            403 => 'errors/403.png',

            404 => 'errors/404.svg',

            419 => 'errors/419.jpg',

            429 => 'errors/429.png',

            500 => 'errors/500.svg',

            503 => 'errors/503.svg',

            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | COMPROBAR QUE LA IMAGEN REALMENTE EXISTE
        |--------------------------------------------------------------------------
        */

        if ($errorImage && !file_exists(public_path($errorImage))) {
            $errorImage = null;
        }
    @endphp

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
                "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f4f7f5;
            color: #1f2937;
        }

        /* =========================================
           CONTENEDOR PRINCIPAL
        ========================================= */

        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        /* =========================================
           DECORACIÓN DE FONDO
        ========================================= */

        .circle {
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
        }

        .circle-one {
            width: 420px;
            height: 420px;
            background: rgba(27, 94, 59, 0.06);
            top: -180px;
            right: -140px;
        }

        .circle-two {
            width: 300px;
            height: 300px;
            background: rgba(27, 94, 59, 0.05);
            bottom: -130px;
            left: -100px;
        }

        /* =========================================
           CONTENIDO
        ========================================= */

        .content {
            width: 100%;
            max-width: 620px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        /* =========================================
           LOGO
        ========================================= */

        .system-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin: 0 auto 32px;
            text-decoration: none;
            width: fit-content;
            min-width: 0;
        }

        .logo-image-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-image {
            height: 60px;
            width: auto;
            border-radius: 8px;
            object-fit: contain;
            display: block;
        }

        .logo-separator {
            height: 48px;
            width: 2px;
            background: #1b5e3b;
            opacity: 0.7;
            flex-shrink: 0;
        }

        .system-name {
            margin: 0;
            color: #1b5e3b;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.3px;
            white-space: nowrap;
        }

        /* =========================================
           IMAGEN DEL ERROR
        ========================================= */

        .error-image-container {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        .error-image {
            display: block;
            width: auto;
            max-width: 280px;
            max-height: 190px;
            object-fit: contain;
        }

        /* SVG */

        .error-image-svg {
            width: 260px;
            max-width: 100%;
            height: auto;
        }

        /* =========================================
           CÓDIGO DEL ERROR
        ========================================= */

        .error-code {
            display: block;
            margin: 0;
            padding: 0;

            font-size: 72px;
            line-height: 1;
            font-weight: 900;
            color: #1b5e3b;
            letter-spacing: -3px;

            visibility: visible;
            opacity: 1;
        }

        /* =========================================
           SEPARADOR
        ========================================= */

        .divider {
            width: 70px;
            height: 5px;
            background: #1b5e3b;
            border-radius: 999px;
            margin: 22px auto 28px;
        }

        /* =========================================
           MENSAJE
        ========================================= */

        .message {
            margin: 0 0 12px;
            font-size: 25px;
            line-height: 1.3;
            font-weight: 700;
            color: #1f2937;
        }

        .description {
            max-width: 500px;
            margin: 0 auto;
            font-size: 16px;
            line-height: 1.7;
            color: #6b7280;
        }

        /* =========================================
           BOTONES
        ========================================= */

        .actions {
            margin-top: 32px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 12px 22px;

            border-radius: 10px;
            text-decoration: none;

            font-size: 14px;
            font-weight: 700;

            transition: all 0.2s ease;
        }

        .button-primary {
            background: #1b5e3b;
            color: #ffffff;
            box-shadow: 0 5px 15px rgba(27, 94, 59, 0.20);
        }

        .button-primary:hover {
            background: #164d30;
            transform: translateY(-1px);
        }

        .button-secondary {
            background: #ffffff;
            color: #1b5e3b;
            border: 1px solid #d1d5db;
        }

        .button-secondary:hover {
            background: #f9fafb;
        }

        /* =========================================
           FOOTER
        ========================================= */

        .footer {
            margin-top: 42px;
            font-size: 12px;
            color: #9ca3af;
        }

        /* =========================================
           MÓVIL
        ========================================= */

        @media (max-width: 640px) {

            .error-page {
                padding: 20px;
            }

            .system-logo {
                gap: 12px;
                margin-bottom: 24px;
            }

            .logo-image {
                height: 48px;
            }

            .logo-separator {
                height: 40px;
            }

            .system-name {
                font-size: 15px;
            }

            .error-image {
                max-width: 220px;
                max-height: 150px;
            }

            .error-image-svg {
                width: 210px;
            }

            .error-code {
                font-size: 58px;
                letter-spacing: -2px;
            }

            .message {
                font-size: 21px;
            }

            .description {
                font-size: 15px;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
</head>

<body>

    <div class="error-page">

        <div class="circle circle-one"></div>
        <div class="circle circle-two"></div>

        <main class="content">

            {{-- =====================================
                 LOGO + NOMBRE DEL SISTEMA
            ====================================== --}}

            <a href="{{ url('/') }}" class="system-logo">

                <div class="logo-image-container">

                    <img src="{{ asset('logo_municipal_negro.png') }}" alt="Municipalidad de La Molina"
                        class="logo-image" onerror="this.style.display='none'">

                </div>

                <div class="logo-separator"></div>

                <div>
                    <p class="system-name">
                        Canchas Deportivas
                    </p>
                </div>

            </a>


            {{-- =====================================
                 IMAGEN SEGÚN ERROR
            ====================================== --}}

            @if ($errorImage)
                <div class="error-image-container">

                    <img src="{{ asset($errorImage) }}" alt="Error {{ $errorCode }}" class="error-image">

                </div>
            @endif


            {{-- =====================================
                 CÓDIGO DEL ERROR
            ====================================== --}}

            <h1 class="error-code">
                @yield('code', '500')
            </h1>


            {{-- =====================================
                 SEPARADOR
            ====================================== --}}

            <div class="divider"></div>


            {{-- =====================================
                 MENSAJE
            ====================================== --}}

            <h2 class="message">
                @yield('message', 'Error del servidor')
            </h2>


            {{-- =====================================
                 DESCRIPCIÓN
            ====================================== --}}

            <p class="description">
                @yield('description', 'Lo sentimos, ocurrió un problema inesperado. Intenta nuevamente en unos momentos.')
            </p>


            {{-- =====================================
                 BOTONES
            ====================================== --}}

            <div class="actions">

                <a href="{{ url('/') }}" class="button button-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 1024 1024">
                        <path d="M0 0h1024v1024H0z" fill="none" />
                        <path fill="currentColor"
                            d="M946.5 505L560.1 118.8l-25.9-25.9a31.5 31.5 0 0 0-44.4 0L77.5 505a63.9 63.9 0 0 0-18.8 46c.4 35.2 29.7 63.3 64.9 63.3h42.5V940h691.8V614.3h43.4c17.1 0 33.2-6.7 45.3-18.8a63.6 63.6 0 0 0 18.7-45.3c0-17-6.7-33.1-18.8-45.2M568 868H456V664h112zm217.9-325.7V868H632V640c0-22.1-17.9-40-40-40H432c-22.1 0-40 17.9-40 40v228H238.1V542.3h-96l370-369.7l23.1 23.1L882 542.3z" />
                    </svg>

                    Volver al inicio
                </a>

                <a href="javascript:history.back()" class="button button-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="m11 5l-7 7l7 7m-7-7h16" />
                    </svg>

                    Regresar
                </a>

            </div>


            {{-- =====================================
                 FOOTER
            ====================================== --}}

            <div class="footer">
                Canchas Deportivas · Sistema de Reservas
            </div>

        </main>

    </div>

</body>

</html>
