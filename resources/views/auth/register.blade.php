<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
            
            <!-- Encabezado con logo de la Municipalidad -->
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <div class="flex items-center space-x-3 bg-emerald-800/10 px-4 py-2 rounded-full border border-emerald-800/20">
                        <img class="h-10 w-auto object-contain" src="{{ asset('logo_municipal_negro.png') }}" alt="Logo Municipalidad de La Molina" onerror="this.style.display='none'; document.getElementById('fallback-register-logo').style.display='block';">
                        
                        <div id="fallback-register-logo" style="display: none;" class="bg-emerald-800 text-white p-2 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        {{-- <span class="text-sm font-semibold text-emerald-900">La Molina</span> --}}
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                    Crear Cuenta de Usuario
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Regístrate para reservar canchas y espacios deportivos
                </p>
            </div>

            <!-- Formulario de Registro de Fortify -->
            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
                @csrf
                
                <!-- Usuario -->
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700">
                        Usuario
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input 
                            id="usuario" 
                            name="usuario" 
                            type="text" 
                            value="{{ old('usuario') }}"
                            required 
                            autofocus
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('usuario') border-red-300 @enderror" 
                            placeholder="tu_usuario"
                        >
                    </div>
                    @error('usuario') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Nombres y apellidos (perfiles) -->
                <div>
                    <label for="nombres" class="block text-sm font-medium text-gray-700">
                        Nombres
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input
                            id="nombres"
                            name="nombres"
                            type="text"
                            value="{{ old('nombres') }}"
                            required
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('nombres') border-red-300 @enderror"
                            placeholder="Juan Carlos"
                        >
                    </div>
                    @error('nombres')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">
                            Apellido paterno
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input
                                id="apellido_paterno"
                                name="apellido_paterno"
                                type="text"
                                value="{{ old('apellido_paterno') }}"
                                required
                                class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('apellido_paterno') border-red-300 @enderror"
                                placeholder="Pérez"
                            >
                        </div>
                        @error('apellido_paterno')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700">
                            Apellido materno
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input
                                id="apellido_materno"
                                name="apellido_materno"
                                type="text"
                                value="{{ old('apellido_materno') }}"
                                required
                                class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('apellido_materno') border-red-300 @enderror"
                                placeholder="García"
                            >
                        </div>
                        @error('apellido_materno')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Correo Electrónico -->
                <div>
                    <label for="correo_electronico" class="block text-sm font-medium text-gray-700">
                        Correo Electrónico
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input 
                            id="correo_electronico" 
                            name="correo_electronico" 
                            type="email" 
                            value="{{ old('correo_electronico') }}"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('correo_electronico') border-red-300 @enderror" 
                            placeholder="ejemplo@correo.com"
                        >
                    </div>
                    @error('correo_electronico') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Contraseña
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400 @error('password') border-red-300 @enderror" 
                            placeholder="Mínimo 8 caracteres"
                        >
                    </div>
                    @error('password') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmar Contraseña
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            required 
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm placeholder-gray-400" 
                            placeholder="Repita su contraseña"
                        >
                    </div>
                </div>

                <!-- Botón de Envío -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-emerald-700 hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out"
                    >
                        Crear Cuenta
                    </button>
                </div>
            </form>

            <!-- Volver al Login -->
            <div class="mt-6 text-center text-sm text-gray-600">
                ¿Ya tienes una cuenta? 
                <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-600 transition duration-150">
                    Inicia sesión aquí
                </a>
            </div>

        </div>
    </div>
</x-guest-layout>