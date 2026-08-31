<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
            
            <!-- Encabezado con logo de la Municipalidad -->
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <!-- Contenedor del Logo -->
                    <div class="flex items-center space-x-3 bg-emerald-800/10 px-4 py-2 rounded-full border border-emerald-800/20">
                        <!-- Reemplace 'logo-la-molina.png' con la ruta real de su imagen en public/ -->
                        <img class="h-15 w-auto object-contain" src="{{ asset('logo_municipal_negro.png') }}" alt="Logo Municipalidad de La Molina" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='block';">
                        
                        <!-- Logo de respaldo en caso de que no cargue la imagen -->
                        <div id="fallback-logo" style="display: none;" class="bg-emerald-800 text-white p-2 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        {{-- <span class="text-sm font-semibold text-emerald-900">La Molina</span> --}}
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                    Alquiler de Canchas
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ingresa para gestionar tus reservas de canchas deportivas
                </p>
            </div>

            <!-- Formulario de Fortify -->
            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                @csrf
                
                <!-- Correo electrónico -->
                <div>
                    <label for="correo_electronico" class="block text-sm font-medium text-gray-700">
                        Correo electrónico
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input 
                            id="correo_electronico" 
                            name="correo_electronico" 
                            type="email" 
                            value="{{ old('correo_electronico') }}"
                            required 
                            autofocus
                            autocomplete="email"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('correo_electronico') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror" 
                            placeholder="correo@ejemplo.com"
                        >
                    </div>
                    @error('correo_electronico') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Contraseña con Alpine.js -->
                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Contraseña
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        
                        <input 
                            id="password" 
                            name="password" 
                            :type="showPassword ? 'text' : 'password'" 
                            required 
                            class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('password') border-red-300 @enderror" 
                            placeholder="••••••••"
                        >

                        <button 
                            type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                        >
                            <!-- Ver Contraseña -->
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Ocultar Contraseña -->
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Recordar sesión y recuperar contraseña -->
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Recordar sesión
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-emerald-700 hover:text-emerald-600 transition duration-150">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <!-- Botón de Envío -->
                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-emerald-700 hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out"
                    >
                        Iniciar Sesión
                    </button>
                </div>
            </form>

            <!-- Registro -->
            <div class="mt-6 text-center text-sm text-gray-600">
                ¿Aún no tienes cuenta? 
                <a href="{{ route('register') }}" class="font-medium text-emerald-700 hover:text-emerald-600 transition duration-150">
                    Regístrate aquí
                </a>
            </div>

        </div>
    </div>
</x-guest-layout>