@if ($desdePortal)
    <x-app-layout>
        <x-slot name="title">Resultado del pago</x-slot>
        <div class="max-w-2xl mx-auto">
            @include('reservar-resultado.contenido')
        </div>
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultado del pago | Municipalidad de La Molina</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f3f6f4] text-slate-800 antialiased">
        <x-public-navbar :sticky="true" :show-social="false" />
        <main class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
            @include('reservar-resultado.contenido')
        </main>
    </body>
    </html>
@endif
