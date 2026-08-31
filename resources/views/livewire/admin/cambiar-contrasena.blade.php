<div class="max-w-2xl mx-auto">
    <x-slot name="title">Cambiar contraseña</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Cambiar contraseña</h2>
        <p class="text-sm text-gray-600 mt-1">
            Por seguridad, verificaremos tu correo antes de permitir el cambio.
        </p>
    </div>

    @if ($mensajeExito)
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $mensajeExito }}
        </div>
    @endif

    @if ($mensajeError)
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $mensajeError }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        @if (! $correoEnmascarado)
            <div class="text-center py-8">
                <i class="fa-solid fa-envelope-circle-check text-4xl text-amber-500 mb-3"></i>
                <p class="text-gray-700 font-medium">No tienes un correo registrado</p>
                <p class="text-sm text-gray-500 mt-2">
                    Contacta al administrador para actualizar tu correo electrónico antes de cambiar la contraseña.
                </p>
            </div>
        @elseif ($paso === 'solicitar')
            <div class="space-y-5">
                <div class="rounded-lg bg-gray-50 border border-gray-100 p-4 text-sm text-gray-700">
                    <p class="font-medium text-gray-900 mb-1">Correo registrado</p>
                    <p>{{ $correoEnmascarado }}</p>
                    <p class="text-gray-500 mt-2">
                        Te enviaremos un código de 6 dígitos a este correo para confirmar que eres tú.
                    </p>
                </div>

                <button type="button" wire:click="enviarCodigo" wire:loading.attr="disabled"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="enviarCodigo">
                        <i class="fa-solid fa-paper-plane"></i>
                    </span>
                    <span wire:loading wire:target="enviarCodigo">Enviando...</span>
                    <span wire:loading.remove wire:target="enviarCodigo">Enviar código al correo</span>
                </button>
            </div>

        @elseif ($paso === 'verificar')
            <div class="space-y-5">
                <p class="text-sm text-gray-600">
                    Ingresa el código de 6 dígitos enviado a <strong>{{ $correoEnmascarado }}</strong>.
                </p>

                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Código de verificación</label>
                    <input id="codigo" type="text" wire:model="codigo" maxlength="6" inputmode="numeric"
                        placeholder="000000"
                        class="w-full tracking-[0.4em] text-center text-xl font-semibold px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" wire:click="verificarCodigo" wire:loading.attr="disabled"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition disabled:opacity-60">
                        <span wire:loading wire:target="verificarCodigo">Verificando...</span>
                        <span wire:loading.remove wire:target="verificarCodigo">Verificar código</span>
                    </button>
                    <button type="button" wire:click="enviarCodigo" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium transition">
                        Reenviar código
                    </button>
                </div>

                <button type="button" wire:click="reiniciar" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Cancelar y volver
                </button>
            </div>

        @elseif ($paso === 'cambiar')
            <div class="space-y-5">
                <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-4 text-sm text-emerald-800">
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    Correo verificado. Define tu nueva contraseña.
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                    <input id="password" type="password" wire:model="password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                </div>

                <button type="button" wire:click="actualizarContrasena" wire:loading.attr="disabled"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition disabled:opacity-60">
                    <span wire:loading wire:target="actualizarContrasena">Guardando...</span>
                    <span wire:loading.remove wire:target="actualizarContrasena">Guardar nueva contraseña</span>
                </button>
            </div>

        @elseif ($paso === 'listo')
            <div class="text-center py-8">
                <i class="fa-solid fa-shield-check text-4xl text-emerald-600 mb-3"></i>
                <p class="text-gray-800 font-semibold text-lg">Contraseña actualizada</p>
                <p class="text-sm text-gray-500 mt-2">Ya puedes usar tu nueva contraseña la próxima vez que ingreses.</p>
                <button type="button" wire:click="reiniciar"
                    class="mt-6 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cambiar de nuevo
                </button>
            </div>
        @endif
    </div>
</div>
