<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Reservas</title>
 <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Tailwind CSS (Vía Vite o CDN si está en pruebas) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased font-sans">
    
    {{ $slot }}

    @livewireScripts
</body>
</html>