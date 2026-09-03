 <!-- MODAL POPUP: SELECTOR TUSNE DINÁMICO -->
    <div x-show="popup.visible" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cerrarPopup()"></div>
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-emerald-900/10 p-6 sm:p-7 z-10 max-h-[90vh] overflow-y-auto"
            @click.stop>
            
            <button type="button" @click="cerrarPopup()"
                class="absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Cabecera -->
            <div class="flex items-center justify-between gap-3 pr-10 mb-4 pb-3 border-b border-slate-100">
                <div class="inline-flex items-center gap-2.5 min-w-0">
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-[#1b5e3b] flex items-center justify-center shrink-0 text-lg">
                        <i class="fa-solid fa-futbol"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-base sm:text-lg font-bold text-slate-900 truncate" x-text="seleccion?.cancha"></p>
                        <p class="text-xs text-slate-500 truncate" x-text="'Deporte: ' + deporte"></p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-1.5 shrink-0 text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100">
                    <i class="fa-regular fa-clock text-[#1b5e3b]"></i>
                    <span x-text="rangoHora"></span>
                </div>
            </div>

            <!-- SELECTOR DINÁMICO DE MODALIDAD TUSNE -->
            <div class="mb-4" x-show="modalidadesCancha.length > 0">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                    1. ¿Para qué utilizarás la cancha?
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <template x-for="mod in modalidadesCancha" :key="mod.codigo">
                        <button type="button"
                            @click="if (modalidadDisponibleEnTurno(mod.codigo)) cambiarTipoUso(mod.codigo)"
                            :disabled="!modalidadDisponibleEnTurno(mod.codigo)"
                            class="p-2.5 rounded-xl border text-left transition flex items-start gap-2.5"
                            :class="!modalidadDisponibleEnTurno(mod.codigo)
                                ? 'bg-slate-50 border-slate-200 opacity-40 cursor-not-allowed text-slate-400'
                                : (seleccion?.tipoUso === mod.codigo
                                    ? 'bg-emerald-50/80 border-emerald-600 ring-2 ring-emerald-600/20 text-emerald-950'
                                    : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50')">
                            
                            <i class="fa-solid mt-0.5 text-sm" :class="mod.icono || 'fa-futbol'"
                               :class="seleccion?.tipoUso === mod.codigo ? 'text-emerald-700' : 'text-slate-400'"></i>
                            
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-xs" x-text="mod.nombre"></p>
                                    <span x-show="seleccion?.tipoUso === mod.codigo" class="text-[10px] text-emerald-700 font-bold">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-500 leading-tight mt-0.5" 
                                   x-text="!modalidadDisponibleEnTurno(mod.codigo) ? (esNoche ? 'No disponible de noche' : 'Solo en horario nocturno') : mod.descripcion"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- DETALLE DEL TUSNE Y TURNO DETECTADO -->
            <div class="mb-4 bg-slate-50 border border-slate-200/80 rounded-2xl p-3">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-semibold text-slate-500">Turno horario:</span>
                    <span class="font-bold px-2 py-0.5 rounded-md text-[11px]"
                        :class="esNoche ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'">
                        <i class="fa-solid mr-1" :class="esNoche ? 'fa-moon' : 'fa-sun'"></i>
                        <span x-text="esNoche ? 'Nocturno (A partir de 18:00)' : 'Diurno (08:00 a 17:00)'"></span>
                    </span>
                </div>
                <div class="text-[11px] text-slate-700 flex items-start gap-1.5 border-t border-slate-200/60 pt-2">
                    <i class="fa-solid fa-barcode text-emerald-600 mt-0.5 text-xs"></i>
                    <div class="min-w-0">
                        <span class="font-bold text-emerald-800" x-text="tusneActivo ? ('TUSNE Cód: ' + tusneActivo.codigo) : 'Tarifa General'"></span>
                        <p class="text-slate-500 truncate text-[10px]" x-text="tusneActivo?.descripcion || 'Sin concepto'"></p>
                    </div>
                </div>
            </div>

            <!-- DURACIÓN Y PRECIO REAL DE ORACLE -->
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">2. Duración de la reserva</p>
            <div class="space-y-2.5 mb-6">
                <button type="button"
                    @click="elegirDuracion(60)"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl border-2 text-sm font-semibold transition"
                    :class="seleccion?.duracion === 60
                        ? 'bg-emerald-50 border-emerald-600 text-emerald-950'
                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                    <span>60 minutos (1 hora)</span>
                    <span class="font-bold text-emerald-900 text-base" x-text="'PEN ' + precioDuracion(60).toFixed(2)"></span>
                </button>
                <button type="button"
                    @click="elegirDuracion(120)"
                    :disabled="!puede120"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl border-2 text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="seleccion?.duracion === 120
                        ? 'bg-emerald-50 border-emerald-600 text-emerald-950'
                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                    <span>120 minutos (2 horas)</span>
                    <span class="font-bold text-emerald-900 text-base" x-text="puede120 ? ('PEN ' + precioDuracion(120).toFixed(2)) : 'No disponible'"></span>
                </button>
            </div>

            <!-- BOTÓN CONTINUAR -->
            <button type="button" @click="continuar()"
                class="w-full py-3.5 rounded-full bg-[#1b5e3b] hover:bg-[#164d31] text-white text-base font-bold shadow-sm transition">
                Continuar - PEN <span x-text="precioDuracion(seleccion?.duracion || 60).toFixed(2)"></span>
            </button>
        </div>
    </div>