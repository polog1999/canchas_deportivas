<div x-show="popup.visible" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cerrarPopup()"></div>
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border p-6 sm:p-7 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <button type="button" @click="cerrarPopup()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="flex items-center justify-between gap-3 pr-10 mb-4 pb-3 border-b">
            <div>
                <p class="text-lg font-bold text-slate-900" x-text="seleccion?.cancha"></p>
                <p class="text-xs text-slate-500" x-text="seleccion?.detalle"></p>
            </div>
            <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100" x-text="rangoHora"></span>
        </div>
        <p class="text-xs font-bold uppercase text-slate-500 mb-2">¿Para qué utilizarás la cancha?</p>
        <div class="grid grid-cols-2 gap-2 mb-4">
            @foreach ([
                ['alquiler_regular', 'fa-futbol', 'Público General', 'Práctica / Pichanga'],
                ['campeonato_corporativo', 'fa-trophy', 'Campeonato', 'Corporativo'],
                ['liga_oficial', 'fa-shield-halved', 'Liga Oficial', 'Partidos'],
                ['liga_entrenamiento', 'fa-person-running', 'Entrenamiento', 'Clubes'],
            ] as [$tipo, $icono, $titulo, $sub])
                <button type="button" @click="cambiarTipoUso('{{ $tipo }}')"
                    class="p-2.5 rounded-xl border text-left text-xs"
                    :class="seleccion?.tipoUso === '{{ $tipo }}' ? 'bg-emerald-50 border-emerald-600 ring-2 ring-emerald-600/20' : 'border-slate-200 hover:bg-slate-50'">
                    <i class="fa-solid {{ $icono }} text-emerald-700 mr-1"></i>
                    <span class="font-bold">{{ $titulo }}</span>
                    <span class="block text-[10px] text-slate-500">{{ $sub }}</span>
                </button>
            @endforeach
        </div>
        <div class="mb-4 bg-slate-50 border rounded-2xl p-3 text-xs">
            <span class="font-bold text-emerald-800" x-text="tusneActivo ? ('TUSNE ' + tusneActivo.codigo) : 'Tarifa general'"></span>
            <p class="text-slate-500 text-[10px]" x-text="tusneActivo?.descripcion || ''"></p>
        </div>
        <p class="text-xs font-bold uppercase text-slate-500 mb-2">Duración</p>
        <div class="space-y-2 mb-6">
            <button type="button" @click="elegirDuracion(60)" class="w-full flex justify-between px-4 py-3 rounded-2xl border-2 text-sm font-semibold"
                :class="seleccion?.duracion === 60 ? 'bg-emerald-50 border-emerald-600' : 'border-slate-200'">
                <span>60 minutos</span>
                <span x-text="'PEN ' + precioDuracion(60).toFixed(2)"></span>
            </button>
            <button type="button" @click="elegirDuracion(120)" :disabled="!puede120" class="w-full flex justify-between px-4 py-3 rounded-2xl border-2 text-sm font-semibold disabled:opacity-40"
                :class="seleccion?.duracion === 120 ? 'bg-emerald-50 border-emerald-600' : 'border-slate-200'">
                <span>120 minutos</span>
                <span x-text="puede120 ? ('PEN ' + precioDuracion(120).toFixed(2)) : 'No disponible'"></span>
            </button>
        </div>
        <button type="button" @click="continuar()" class="w-full py-3.5 rounded-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold">
            Continuar - PEN <span x-text="precioDuracion(seleccion?.duracion || 60).toFixed(2)"></span>
        </button>
    </div>
</div>
