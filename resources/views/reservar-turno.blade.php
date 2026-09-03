<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige tu turno | Municipalidad de La Molina</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#eef2ef] text-slate-800 antialiased font-sans" x-data="turnoMaqueta()">

    <x-public-navbar
        :back-href="route('reservar.deporte', ['sede' => $sede['id'], 'fecha' => $fecha])"
        back-label="Volver a deportes"
    />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="mb-5">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Paso 2 de 3</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#123d2a]">Elige tu turno</h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-semibold text-slate-700" x-text="club.nombre"></span>
                · <span x-text="club.direccion"></span>
            </p>
        </div>

        <div x-show="avisoTurno" x-cloak
            class="mb-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-600"></i>
            <p class="flex-1" x-text="avisoTurno"></p>
            <button type="button" @click="avisoTurno = ''"
                class="text-amber-600 hover:text-amber-800 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-800">
                    <i class="fa-solid fa-futbol text-[#1b5e3b]"></i>
                    <span x-text="deporte"></span>
                </div>

                <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden">
                    <button type="button" @click="cambiarDia(-1)" class="px-3 py-2 hover:bg-slate-100 text-slate-500 transition-colors">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <div class="px-3 py-2 text-sm font-bold text-slate-800 min-w-[8.5rem] text-center select-none" x-text="etiquetaFecha"></div>
                    <button type="button" @click="cambiarDia(1)" class="px-3 py-2 hover:bg-slate-100 text-slate-500 transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>

                <p class="sm:ml-auto text-xs text-slate-400" x-show="cargandoOcupacion" x-cloak>
                    <i class="fa-solid fa-spinner animate-spin mr-1"></i> Actualizando ocupación...
                </p>
            </div>

            <!-- Grilla Horaria con Validación de Horas Pasadas y Turno TUSNE -->
            <div class="overflow-x-auto relative" x-show="canchas.length">
                <div class="min-w-[900px] relative" id="grillaTurnos">
                    <div class="grid border-b border-slate-100"
                        :style="'grid-template-columns: 220px repeat(' + horas.length + ', minmax(48px, 1fr))'">
                        <div class="px-4 py-2 text-[10px] font-bold uppercase tracking-wide text-slate-400 bg-slate-50 sticky left-0 z-10">
                            CANCHA | HORAS
                        </div>
                        <template x-for="h in horas" :key="'h'+h">
                            <div class="py-2 text-center text-[11px] font-bold text-slate-500 bg-slate-50 border-l border-slate-100"
                                x-text="String(h).padStart(2,'0')"></div>
                        </template>
                    </div>

                    <template x-for="cancha in canchas" :key="cancha.id">
                        <div class="grid border-b border-slate-100"
                            :style="'grid-template-columns: 220px repeat(' + horas.length + ', minmax(48px, 1fr))'">
                            <div class="px-4 py-3 sticky left-0 z-10 bg-white border-r border-slate-100">
                                <p class="text-sm font-bold text-slate-800 leading-tight" x-text="cancha.nombre"></p>
                                <p class="text-[10px] text-slate-400 mt-0.5 leading-snug" x-text="cancha.detalle"></p>
                            </div>
                            <template x-for="h in horas" :key="cancha.id + '-' + h">
                                <button type="button"
                                    class="h-14 border-l border-slate-100 relative transition"
                                    :data-celda="cancha.id + '-' + h"
                                    :disabled="estaBloqueado(cancha, h) || validandoSlot"
                                    :class="claseCelda(cancha, h)"
                                    @click.stop="seleccionar($event, cancha, h)"
                                    :title="tituloCelda(cancha, h)">

                                    <!-- 0. Validando disponibilidad -->
                                    <span x-show="slotValidandose === cancha.id + '-' + h"
                                        class="absolute inset-1.5 rounded-md bg-white/80 flex items-center justify-center pointer-events-none text-[#1b5e3b]">
                                        <i class="fa-solid fa-circle-notch fa-spin text-[11px]"></i>
                                    </span>
                                    
                                    <!-- 1. Hora pasada -->
                                    <span x-show="esHoraPasada(h)"
                                        class="absolute inset-1.5 rounded-md bg-slate-200/60 flex items-center justify-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                    </span>

                                    <!-- 2. Ocupado por otra reserva -->
                                    <span x-show="!esHoraPasada(h) && estaOcupado(cancha, h)"
                                        class="absolute inset-1.5 rounded-md bg-slate-400/80 pointer-events-none"></span>
                                    
                                    <!-- 3. Sin tarifa TUSNE en este horario -->
                                    <span x-show="!esHoraPasada(h) && !estaOcupado(cancha, h) && !tieneTarifaEnHora(cancha, h)"
                                        class="absolute inset-1.5 rounded-md bg-slate-100 flex items-center justify-center pointer-events-none text-slate-300">
                                        <i class="fa-solid fa-ban text-[10px]"></i>
                                    </span>

                                    <!-- 4. Turno Seleccionado -->
                                    <span x-show="estaSeleccionado(cancha, h)"
                                        class="absolute inset-1.5 rounded-md bg-[#1b5e3b] pointer-events-none"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-6 py-10 text-center text-slate-500" x-show="!canchas.length" x-cloak>
                <p class="font-semibold text-slate-700">Esta sede aún no tiene canchas activas para este deporte.</p>
                <p class="text-sm mt-1">Regístralas en el portal de administración para habilitar turnos.</p>
            </div>

            <div class="px-4 sm:px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                <p class="text-xs text-slate-500 flex items-start gap-2">
                    <i class="fa-solid fa-circle-info text-sky-600 mt-0.5"></i>
                    Las horas pasadas y los horarios sin arancel TUSNE habilitado aparecen bloqueados automáticamente.
                </p>
                <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-200"></span> Pasado / Sin turno
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-400"></span> Reservado
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-[#1b5e3b]"></span> Tu reserva
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded border border-slate-200 bg-white"></span> Disponible
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row gap-3 sm:justify-end">
            <a href="{{ route('reservar.deporte', ['sede' => $sede['id'], 'fecha' => $fecha]) }}"
                class="inline-flex justify-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                Cambiar deporte
            </a>
        </div>

        <!-- Mapa -->
        @if (!empty($sede['mapa_embed']) || !empty($sede['enlace_mapas']) || !empty($sede['direccion']))
            <section class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-[#123d2a]">Ubicación</h2>
                        @if (!empty($sede['direccion']))
                            <p class="text-sm text-slate-500 mt-1 flex items-start gap-1.5">
                                <i class="fa-solid fa-location-dot text-slate-400 mt-0.5"></i>
                                <span>{{ $sede['direccion'] }}</span>
                            </p>
                        @endif
                    </div>
                    @if (!empty($sede['enlace_mapas']))
                        <a href="{{ $sede['enlace_mapas'] }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-semibold border border-emerald-100 hover:bg-emerald-100 transition">
                            <i class="fa-solid fa-map-location-dot"></i>
                            Abrir en Google Maps
                        </a>
                    @endif
                </div>
                @if (!empty($sede['mapa_embed']))
                    <div class="aspect-[16/9] sm:aspect-[21/9] bg-slate-100">
                        <iframe
                            title="Mapa de {{ $sede['nombre'] }}"
                            src="{{ $sede['mapa_embed'] }}"
                            class="w-full h-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen>
                        </iframe>
                    </div>
                @endif
            </section>
        @endif
    </main>

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
            <button type="button" @click="continuar()" :disabled="validandoSlot"
                class="w-full py-3.5 rounded-full bg-[#1b5e3b] hover:bg-[#164d31] text-white text-base font-bold shadow-sm transition disabled:opacity-60 disabled:cursor-wait">
                <span x-show="!validandoSlot">
                    Continuar - PEN <span x-text="precioDuracion(seleccion?.duracion || 60).toFixed(2)"></span>
                </span>
                <span x-show="validandoSlot" x-cloak>
                    <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Validando disponibilidad...
                </span>
            </button>
        </div>
    </div>

    <!-- Script Alpine.js -->
    <script>
        function turnoMaqueta() {
            const club = @json($sede);
            const deporteParam = @json($deporte);
            const deporteIdParam = @json($deporte_id);
            const fechaParam = @json($fecha);
            const ocupacionUrl = @json(route('reservar.ocupacion'));
            const disponibilidadUrl = @json(route('reservar.disponibilidad'));

            const inicio = parseInt(String(club.hora_inicio || '08:00').split(':')[0], 10);
            const fin = parseInt(String(club.hora_fin || '22:00').split(':')[0], 10);
            const horas = [];

            for (let h = inicio; h <= fin; h++) {
                horas.push(h);
            }

            return {
                club,
                deporte: deporteParam,
                deporteId: deporteIdParam,
                fecha: fechaParam,
                horas: horas.length ? horas : Array.from({ length: 14 }, (_, i) => i + 8),
                canchas: club.canchas || [],
                seleccion: null,
                popup: { visible: false },
                cargandoOcupacion: false,
                puede120: false,
                validandoSlot: false,
                slotValidandose: null,
                avisoTurno: '',

                get canchaSeleccionada() {
                    if (!this.seleccion) return null;
                    return this.canchas.find(item => item.id === this.seleccion.canchaId) || null;
                },

                // VALIDACIÓN DE HORAS PASADAS (Ej: Si son 15:25, bloquea hasta las 14:00; las 15:00 quedan habilitadas)
                esHoraPasada(h) {
                    const hoy = new Date();
                    const year = hoy.getFullYear();
                    const month = String(hoy.getMonth() + 1).padStart(2, '0');
                    const day = String(hoy.getDate()).padStart(2, '0');
                    const hoyStr = `${year}-${month}-${day}`;

                    if (this.fecha < hoyStr) return true;

                    if (this.fecha === hoyStr) {
                        const horaActual = hoy.getHours();
                        return h < horaActual;
                    }

                    return false;
                },

                // SOLO DÍA Y NOCHE
                turnoDeHora(h) {
                    return h >= 18 ? 'noche' : 'dia';
                },

                get esNoche() {
                    if (!this.seleccion) return false;
                    return this.seleccion.hora >= 18;
                },

                // VALIDADOR TUSNE: Verifica si la cancha tiene al menos un TUSNE para esa hora
                tieneTarifaEnHora(cancha, h) {
                    if (!cancha.tusnes || !cancha.tusnes.length) return false;
                    const turno = this.turnoDeHora(h);
                    return cancha.tusnes.some(t => t.horario_turno === turno || t.horario_turno === 'todos');
                },

                // Bloqueado si: pasó la hora, está reservado o no tiene TUSNE
                estaBloqueado(cancha, h) {
                    return this.esHoraPasada(h) || this.estaOcupado(cancha, h) || !this.tieneTarifaEnHora(cancha, h);
                },

                // Modalidades configuradas en la cancha
                get modalidadesCancha() {
                    return this.canchaSeleccionada?.modalidades_disponibles || [];
                },

                // Verifica si una modalidad tiene TUSNE en el turno de la hora seleccionada
                modalidadDisponibleEnTurno(codigoMod) {
                    if (!this.canchaSeleccionada) return false;
                    const turno = this.turnoDeHora(this.seleccion.hora);
                    return (this.canchaSeleccionada.tusnes || []).some(t => 
                        (t.tipo_uso === codigoMod || t.tipo_uso === 'todos') &&
                        (t.horario_turno === turno || t.horario_turno === 'todos')
                    );
                },

                // EMPAREJADOR TUSNE EXACTO
                get tusneActivo() {
                    if (!this.canchaSeleccionada || !this.canchaSeleccionada.tusnes?.length) return null;

                    const turno = this.turnoDeHora(this.seleccion.hora);
                    const uso = this.seleccion.tipoUso;

                    // 1. Coincidencia exacta por modalidad y turno
                    let match = this.canchaSeleccionada.tusnes.find(t => 
                        (t.tipo_uso === uso || t.tipo_uso === 'todos') && 
                        (t.horario_turno === turno || t.horario_turno === 'todos')
                    );

                    // 2. Coincidencia secundaria por turno
                    if (!match) {
                        match = this.canchaSeleccionada.tusnes.find(t => 
                            t.horario_turno === turno || t.horario_turno === 'todos'
                        );
                    }

                    return match || null;
                },

                get precioHoraActual() {
                    if (this.tusneActivo && this.tusneActivo.precio_hora > 0) {
                        return Number(this.tusneActivo.precio_hora);
                    }
                    return Number(this.seleccion?.precioBase || 0);
                },

                get etiquetaFecha() {
                    const d = new Date(this.fecha + 'T12:00:00');
                    const hoy = new Date();
                    hoy.setHours(12, 0, 0, 0);
                    const manana = new Date(hoy);
                    manana.setDate(manana.getDate() + 1);
                    const label = d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
                    if (d.toDateString() === hoy.toDateString()) return 'Hoy ' + label;
                    if (d.toDateString() === manana.toDateString()) return 'Mañana ' + label;
                    return d.toLocaleDateString('es-PE', { weekday: 'short', day: '2-digit', month: '2-digit' });
                },

                horaLabel(h) {
                    if (h === null || h === undefined) return '';
                    return String(h).padStart(2, '0') + ':00';
                },

                get rangoHora() {
                    if (!this.seleccion) return '';
                    const inicio = this.seleccion.hora;
                    const horasBloque = (this.seleccion.duracion || 60) / 60;
                    const fin = inicio + horasBloque;
                    return this.horaLabel(inicio) + ' a ' + this.horaLabel(fin);
                },

                precioDuracion(minutos) {
                    if (!this.seleccion) return 0;
                    const horas = (minutos || 60) / 60;
                    return (this.precioHoraActual * horas);
                },

                cambiarTipoUso(tipo) {
                    if (!this.seleccion) return;
                    this.seleccion.tipoUso = tipo;
                },

                async cambiarDia(delta) {
                    const d = new Date(this.fecha + 'T12:00:00');
                    d.setDate(d.getDate() + delta);
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    const max = new Date(hoy);
                    max.setDate(max.getDate() + 6);
                    if (d < hoy || d > max) return;
                    this.fecha = d.toISOString().slice(0, 10);
                    this.cerrarPopup();
                    await this.cargarOcupacion();
                },

                async cargarOcupacion() {
                    this.cargandoOcupacion = true;
                    try {
                        const params = new URLSearchParams({
                            sede: String(this.club.id),
                            fecha: this.fecha,
                        });
                        if (this.deporteId) params.set('deporte_id', String(this.deporteId));

                        const res = await fetch(ocupacionUrl + '?' + params.toString(), {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        if (!data.ok) return;

                        const mapa = data.ocupados || {};
                        this.canchas = this.canchas.map((c) => ({
                            ...c,
                            ocupados: mapa[c.id] || mapa[String(c.id)] || [],
                        }));
                    } catch (e) {
                    } finally {
                        this.cargandoOcupacion = false;
                    }
                },

                estaOcupado(cancha, h) {
                    return (cancha.ocupados || []).includes(h);
                },

                estaSeleccionado(cancha, h) {
                    if (!this.seleccion || this.seleccion.canchaId !== cancha.id) return false;
                    const inicio = this.seleccion.hora;
                    const bloques = (this.seleccion.duracion || 60) / 60;
                    return h >= inicio && h < inicio + bloques;
                },

                claseCelda(cancha, h) {
                    if (this.esHoraPasada(h)) {
                        return 'bg-slate-100 cursor-not-allowed opacity-40';
                    }
                    if (this.estaOcupado(cancha, h)) {
                        return 'bg-slate-100 cursor-not-allowed';
                    }
                    if (!this.tieneTarifaEnHora(cancha, h)) {
                        return 'bg-slate-50 cursor-not-allowed opacity-50';
                    }
                    if (this.estaSeleccionado(cancha, h)) {
                        return 'bg-[#1b5e3b]/10';
                    }
                    return 'bg-white hover:bg-[#1b5e3b]/10 cursor-pointer';
                },

                tituloCelda(cancha, h) {
                    if (this.esHoraPasada(h)) return 'Horario pasado (No disponible)';
                    if (this.estaOcupado(cancha, h)) return 'No disponible (Reservado)';
                    if (!this.tieneTarifaEnHora(cancha, h)) {
                        const turno = this.turnoDeHora(h);
                        return turno === 'dia' ? 'Tarifa disponible solo en horario nocturno' : 'Tarifa disponible solo en horario diurno';
                    }
                    return 'Reservar ' + this.horaLabel(h);
                },

                // Pregunta al servidor si el turno sigue libre (otra persona pudo tomarlo)
                async turnoSigueLibre(canchaId, hora, duracion) {
                    const params = new URLSearchParams({
                        cancha_id: String(canchaId),
                        fecha: this.fecha,
                        hora: this.horaLabel(hora),
                        duracion: String(duracion),
                    });

                    const res = await fetch(disponibilidadUrl + '?' + params.toString(), {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!res.ok) throw new Error('No se pudo validar la disponibilidad.');

                    const data = await res.json();

                    // Refresca la grilla con lo que el servidor acaba de ver
                    const mapa = data.ocupados || {};
                    if (mapa[canchaId] || mapa[String(canchaId)]) {
                        this.canchas = this.canchas.map((c) => c.id === canchaId
                            ? { ...c, ocupados: mapa[c.id] || mapa[String(c.id)] || [] }
                            : c);
                    }

                    return data;
                },

                async seleccionar(event, cancha, h) {
                    if (this.estaBloqueado(cancha, h) || this.validandoSlot) return;

                    this.avisoTurno = '';
                    this.validandoSlot = true;
                    this.slotValidandose = cancha.id + '-' + h;

                    try {
                        const data = await this.turnoSigueLibre(cancha.id, h, 60);

                        if (!data.disponible) {
                            this.avisoTurno = data.mensaje
                                || 'Ese horario ya no está disponible. Elige otro turno.';
                            return;
                        }
                    } catch (e) {
                        this.avisoTurno = 'No pudimos validar la disponibilidad. Intenta nuevamente.';
                        return;
                    } finally {
                        this.validandoSlot = false;
                        this.slotValidandose = null;
                    }

                    const puede120 = !this.estaBloqueado(cancha, h + 1) && this.horas.includes(h + 1);

                    // Buscar qué modalidades tienen TUSNE válido para ese turno (Día o Noche)
                    const turno = this.turnoDeHora(h);
                    const tusnesValidosTurno = (cancha.tusnes || []).filter(t => t.horario_turno === turno || t.horario_turno === 'todos');
                    
                    // Si el turno es día y solo hay campeonato, seleccionará campeonato directamente:
                    const codigosValidos = [...new Set(tusnesValidosTurno.map(t => t.tipo_uso))];
                    const modalidadSeleccionada = codigosValidos[0] || 'alquiler_regular';

                    this.puede120 = puede120;
                    this.seleccion = {
                        canchaId: cancha.id,
                        cancha: cancha.nombre,
                        detalle: cancha.detalle,
                        precioBase: Number(cancha.precio) || 0,
                        hora: h,
                        duracion: 60,
                        tipoUso: modalidadSeleccionada, // Selecciona el TUSNE disponible (Ej: campeonato en el día)
                        deporteIds: cancha.deporte_ids || [],
                    };
                    this.popup.visible = true;
                },

                async elegirDuracion(minutos) {
                    if (!this.seleccion || this.validandoSlot) return;
                    if (minutos === 120 && !this.puede120) return;

                    if (minutos === 120) {
                        this.validandoSlot = true;
                        try {
                            const data = await this.turnoSigueLibre(this.seleccion.canchaId, this.seleccion.hora, 120);

                            if (!data.disponible) {
                                this.puede120 = false;
                                this.avisoTurno = 'La segunda hora acaba de ser tomada. Solo puedes reservar 60 minutos.';
                                return;
                            }
                        } catch (e) {
                            this.avisoTurno = 'No pudimos validar la disponibilidad. Intenta nuevamente.';
                            return;
                        } finally {
                            this.validandoSlot = false;
                        }
                    }

                    this.seleccion.duracion = minutos;
                },

                cerrarPopup() {
                    this.popup.visible = false;
                    this.seleccion = null;
                    this.puede120 = false;
                },

                async continuar() {
                    if (!this.seleccion || this.validandoSlot) return;

                    this.validandoSlot = true;
                    try {
                        const data = await this.turnoSigueLibre(
                            this.seleccion.canchaId,
                            this.seleccion.hora,
                            this.seleccion.duracion || 60,
                        );

                        if (!data.disponible) {
                            this.avisoTurno = data.mensaje
                                || 'Ese horario ya no está disponible. Elige otro turno.';
                            this.cerrarPopup();
                            return;
                        }
                    } catch (e) {
                        this.avisoTurno = 'No pudimos validar la disponibilidad. Intenta nuevamente.';
                        return;
                    } finally {
                        this.validandoSlot = false;
                    }

                    const horaStr = this.horaLabel(this.seleccion.hora);
                    const tusne = this.tusneActivo;

                    const params = new URLSearchParams({
                        sede: String(this.club.id),
                        club: this.club.nombre,
                        direccion: this.club.direccion,
                        imagen: this.club.imagen || '',
                        cancha: this.seleccion.cancha,
                        cancha_id: String(this.seleccion.canchaId),
                        detalle: this.seleccion.detalle,
                        fecha: this.fecha,
                        hora: horaStr,
                        duracion: String(this.seleccion.duracion),
                        precio: String(this.precioDuracion(this.seleccion.duracion).toFixed(2)),
                        deporte: this.deporte,
                        tusne_id: tusne ? String(tusne.id) : '',
                        codigo_tusne: tusne ? String(tusne.codigo) : '',
                        grupo_tusne: tusne ? String(tusne.grupo) : '23',
                        tipo_uso: this.seleccion.tipoUso,
                    });

                    if (this.deporteId) {
                        params.set('deporte_id', String(this.deporteId));
                    } else if (this.seleccion.deporteIds?.length) {
                        params.set('deporte_id', String(this.seleccion.deporteIds[0]));
                    }

                    window.location.href = @json(route('reservar.confirmar')) + '?' + params.toString();
                },
            };
        }
    </script>
</body>
</html>