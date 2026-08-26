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
<body class="bg-[#eef2ef] text-slate-800 antialiased" x-data="turnoMaqueta()">

    <header class="bg-[#1b5e3b] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-3">
            <a href="{{ route('reservar.deporte', ['sede' => $sede['id'], 'fecha' => $fecha]) }}" class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200">
                <i class="fa-solid fa-arrow-left"></i>
                Volver a deportes
            </a>
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('logo_municipal_negro.png') }}" alt="La Molina"
                    class="h-8 w-auto bg-white rounded px-1.5 object-contain" onerror="this.style.display='none'">
            </a>
            <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-emerald-200">
                <i class="fa-regular fa-user mr-1"></i> Iniciar sesión
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="mb-5">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Paso 2 de 3</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#123d2a]">Elige tu turno</h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-semibold text-slate-700" x-text="club.nombre"></span>
                · <span x-text="club.direccion"></span>
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-800">
                    <i class="fa-solid fa-futbol text-[#1b5e3b]"></i>
                    <span x-text="deporte"></span>
                </div>

                <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden">
                    <button type="button" @click="cambiarDia(-1)" class="px-3 py-2 hover:bg-slate-100 text-slate-500">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <div class="px-3 py-2 text-sm font-bold text-slate-800 min-w-[8.5rem] text-center" x-text="etiquetaFecha"></div>
                    <button type="button" @click="cambiarDia(1)" class="px-3 py-2 hover:bg-slate-100 text-slate-500">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>

                <p class="sm:ml-auto text-xs text-slate-400" x-show="cargandoOcupacion" x-cloak>
                    <i class="fa-solid fa-spinner animate-spin mr-1"></i> Actualizando ocupación...
                </p>
            </div>

            <div class="overflow-x-auto relative" x-show="canchas.length">
                <div class="min-w-[900px] relative" id="grillaTurnos">
                    <div class="grid border-b border-slate-100"
                        :style="'grid-template-columns: 220px repeat(' + horas.length + ', minmax(48px, 1fr))'">
                        <div class="px-4 py-2 text-[10px] font-bold uppercase tracking-wide text-slate-400 bg-slate-50 sticky left-0 z-10">
                            Cancha
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
                                    :disabled="estaOcupado(cancha, h)"
                                    :class="claseCelda(cancha, h)"
                                    @click.stop="seleccionar($event, cancha, h)"
                                    :title="estaOcupado(cancha, h) ? 'No disponible' : ('Reservar ' + String(h).padStart(2,'0') + ':00')">
                                    <span x-show="estaOcupado(cancha, h)"
                                        class="absolute inset-1.5 rounded-md bg-slate-400/80 pointer-events-none"></span>
                                    <span x-show="estaSeleccionado(cancha, h)"
                                        class="absolute inset-1.5 rounded-md bg-lime-400 pointer-events-none"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-6 py-10 text-center text-slate-500" x-show="!canchas.length" x-cloak>
                <p class="font-semibold text-slate-700">Esta sede aún no tiene canchas activas.</p>
                <p class="text-sm mt-1">Regístralas en el portal de administración para habilitar turnos.</p>
            </div>

            <div class="px-4 sm:px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                <p class="text-xs text-slate-500 flex items-start gap-2">
                    <i class="fa-solid fa-circle-info text-sky-600 mt-0.5"></i>
                    La ocupación se valida con las reservas reales (hora inicio / hora fin).
                </p>
                <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-400"></span> No disponible
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-lime-400"></span> Tu reserva
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded border border-slate-200 bg-white"></span> Libre
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row gap-3 sm:justify-end">
            <a href="{{ route('reservar.deporte', ['sede' => $sede['id'], 'fecha' => $fecha]) }}"
                class="inline-flex justify-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Cambiar deporte
            </a>
        </div>
    </main>

    {{-- Popup modal --}}
    <div x-show="popup.visible" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50" @click="cerrarPopup()"></div>
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-emerald-900/10 p-6 sm:p-7"
            @click.stop>
            <button type="button" @click="cerrarPopup()"
                class="absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center justify-between gap-3 pr-10 mb-5 pb-4 border-b border-slate-100">
                <div class="inline-flex items-center gap-2.5 min-w-0">
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-[#1b5e3b] flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-futbol"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-base sm:text-lg font-bold text-slate-900 truncate" x-text="seleccion?.cancha"></p>
                        <p class="text-xs text-slate-500 truncate" x-text="seleccion?.detalle"></p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 shrink-0 text-sm font-semibold text-slate-600 bg-slate-50 px-3 py-2 rounded-xl">
                    <i class="fa-regular fa-clock text-[#1b5e3b]"></i>
                    <span x-text="rangoHora"></span>
                </div>
            </div>

            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-3">Duración</p>
            <div class="space-y-3 mb-6">
                <button type="button"
                    @click="elegirDuracion(60)"
                    class="w-full flex items-center justify-between px-4 py-3.5 rounded-2xl border-2 text-base font-semibold transition"
                    :class="seleccion?.duracion === 60
                        ? 'bg-lime-100 border-[#1b5e3b] text-[#123d2a]'
                        : 'bg-white border-[#1b5e3b]/35 text-slate-700 hover:bg-lime-50'">
                    <span>60 min</span>
                    <span x-text="'PEN ' + precioDuracion(60)"></span>
                </button>
                <button type="button"
                    @click="elegirDuracion(120)"
                    :disabled="!puede120"
                    class="w-full flex items-center justify-between px-4 py-3.5 rounded-2xl border-2 text-base font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="seleccion?.duracion === 120
                        ? 'bg-lime-100 border-[#1b5e3b] text-[#123d2a]'
                        : 'bg-white border-[#1b5e3b]/35 text-slate-700 hover:bg-lime-50'">
                    <span>120 min</span>
                    <span x-text="puede120 ? ('PEN ' + precioDuracion(120)) : 'No disponible'"></span>
                </button>
            </div>

            <button type="button" @click="continuar()"
                class="w-full py-3.5 rounded-full bg-lime-400 hover:bg-lime-300 text-[#123d2a] text-base font-bold shadow-sm transition">
                Continuar - PEN <span x-text="precioDuracion(seleccion?.duracion || 60)"></span>
            </button>
        </div>
    </div>

    <script>
        function turnoMaqueta() {
            const club = @json($sede);
            const deporteParam = @json($deporte);
            const deporteIdParam = @json($deporte_id);
            const fechaParam = @json($fecha);
            const ocupacionUrl = @json(route('reservar.ocupacion'));

            const inicio = parseInt(String(club.hora_inicio || '08:00').split(':')[0], 10);
            const fin = parseInt(String(club.hora_fin || '22:00').split(':')[0], 10);
            const horas = [];
            // Inclusivo: 06:00–18:00 → columnas 06 … 18
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
                    return Math.round(this.seleccion.precioHora * horas);
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
                        // silencioso: se mantienen los ocupados actuales
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
                    if (this.estaOcupado(cancha, h)) {
                        return 'bg-slate-100 cursor-not-allowed';
                    }
                    if (this.estaSeleccionado(cancha, h)) {
                        return 'bg-lime-50';
                    }
                    return 'bg-white hover:bg-lime-100 cursor-pointer';
                },

                seleccionar(event, cancha, h) {
                    if (this.estaOcupado(cancha, h)) return;

                    const puede120 = !this.estaOcupado(cancha, h + 1)
                        && this.horas.includes(h + 1);

                    this.puede120 = puede120;
                    this.seleccion = {
                        canchaId: cancha.id,
                        cancha: cancha.nombre,
                        detalle: cancha.detalle,
                        precioHora: Number(cancha.precio) || 0,
                        hora: h,
                        duracion: 60,
                        deporteIds: cancha.deporte_ids || [],
                    };
                    this.popup.visible = true;
                },

                elegirDuracion(minutos) {
                    if (!this.seleccion) return;
                    if (minutos === 120 && !this.puede120) return;
                    this.seleccion.duracion = minutos;
                },

                cerrarPopup() {
                    this.popup.visible = false;
                    this.seleccion = null;
                    this.puede120 = false;
                },

                continuar() {
                    if (!this.seleccion) return;
                    const horaStr = this.horaLabel(this.seleccion.hora);
                    const params = new URLSearchParams({
                        sede: String(this.club.id),
                        club: this.club.nombre,
                        direccion: this.club.direccion,
                        imagen: this.club.imagen,
                        cancha: this.seleccion.cancha,
                        cancha_id: String(this.seleccion.canchaId),
                        detalle: this.seleccion.detalle,
                        fecha: this.fecha,
                        hora: horaStr,
                        duracion: String(this.seleccion.duracion),
                        precio: String(this.precioDuracion(this.seleccion.duracion)),
                        deporte: this.deporte,
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
