@php
    $urlDeporte = route('portal.reservar.deporte', ['sede' => $sede['id'], 'fecha' => $fecha]);
    $urlConfirmar = route('portal.reservar.confirmar');
    $urlOcupacion = route('reservar.ocupacion');
@endphp

<x-portal-reserva-shell
    title="Elige tu turno"
    :step="2"
    :back-href="$urlDeporte"
    back-label="Volver a deportes"
    :alpine="true"
>
    <div x-data="turnoPortal()">
        <div class="mb-5">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Paso 2 de 3</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Elige tu turno</h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-semibold text-slate-700" x-text="club.nombre"></span>
                · <span x-text="club.direccion"></span>
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-800">
                    <i class="fa-solid fa-futbol text-emerald-800"></i>
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
                <div class="min-w-[900px]">
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
                                    :disabled="estaBloqueado(cancha, h)"
                                    :class="claseCelda(cancha, h)"
                                    @click.stop="seleccionar($event, cancha, h)"
                                    :title="tituloCelda(cancha, h)">
                                    
                                    <!-- 1. Hora pasada (deshabilitada) -->
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
                                        class="absolute inset-1.5 rounded-md bg-emerald-700 pointer-events-none"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-6 py-10 text-center text-slate-500" x-show="!canchas.length" x-cloak>
                Esta sede aún no tiene canchas activas para este deporte.
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
                        <span class="w-4 h-4 rounded bg-emerald-700"></span> Tu reserva
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded border border-slate-200 bg-white"></span> Disponible
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-5 flex justify-end">
            <a href="{{ $urlDeporte }}" class="px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                Cambiar deporte
            </a>
        </div>

        @if (!empty($sede['mapa_embed']) || !empty($sede['enlace_mapas']) || !empty($sede['direccion']))
            <section class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ubicación</h2>
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

        @include('portal.reservar.partials.modal-tusne')

        @push('scripts')
        <script>
        function turnoPortal() {
            const club = @json($sede);
            const deporteParam = @json($deporte);
            const deporteIdParam = @json($deporte_id);
            const fechaParam = @json($fecha);
            const ocupacionUrl = @json($urlOcupacion);
            const urlConfirmar = @json($urlConfirmar);
            const inicio = parseInt(String(club.hora_inicio || '08:00').split(':')[0], 10);
            const fin = parseInt(String(club.hora_fin || '22:00').split(':')[0], 10);
            const horas = [];
            for (let h = inicio; h <= fin; h++) horas.push(h);

            return {
                club,
                deporte: deporteParam,
                deporteId: deporteIdParam,
                fecha: fechaParam,
                horas: horas.length ? horas : Array.from({length: 14}, (_, i) => i + 8),
                canchas: club.canchas || [],
                seleccion: null,
                popup: { visible: false },
                cargandoOcupacion: false,
                puede120: false,

                get canchaSeleccionada() {
                    if (!this.seleccion) return null;
                    return this.canchas.find(item => item.id === this.seleccion.canchaId) || null;
                },

                // VALIDACIÓN DE HORAS PASADAS (Ej: a las 15:25 bloquea 08:00 a 14:00; 15:00 en adelante disponible)
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

                // SOLO DÍA Y NOCHE (Día < 18:00, Noche >= 18:00)
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

                // Bloqueado si: pasó la hora, está reservado o no tiene TUSNE para este horario
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
                    const hoy = new Date(); hoy.setHours(12,0,0,0);
                    const manana = new Date(hoy); manana.setDate(manana.getDate() + 1);
                    const label = d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
                    if (d.toDateString() === hoy.toDateString()) return 'Hoy ' + label;
                    if (d.toDateString() === manana.toDateString()) return 'Mañana ' + label;
                    return d.toLocaleDateString('es-PE', { weekday: 'short', day: '2-digit', month: '2-digit' });
                },

                horaLabel(h) { return String(h).padStart(2,'0') + ':00'; },

                get rangoHora() {
                    if (!this.seleccion) return '';
                    const fin = this.seleccion.hora + (this.seleccion.duracion || 60) / 60;
                    return this.horaLabel(this.seleccion.hora) + ' a ' + this.horaLabel(fin);
                },

                precioDuracion(min) {
                    if (!this.seleccion) return 0;
                    return this.precioHoraActual * ((min || 60) / 60);
                },

                cambiarTipoUso(tipo) {
                    if (this.seleccion) this.seleccion.tipoUso = tipo;
                },

                async cambiarDia(delta) {
                    const d = new Date(this.fecha + 'T12:00:00');
                    d.setDate(d.getDate() + delta);
                    const hoy = new Date(); hoy.setHours(0,0,0,0);
                    const max = new Date(hoy); max.setDate(max.getDate() + 6);
                    if (d < hoy || d > max) return;
                    this.fecha = d.toISOString().slice(0,10);
                    this.cerrarPopup();
                    await this.cargarOcupacion();
                },

                async cargarOcupacion() {
                    this.cargandoOcupacion = true;
                    try {
                        const params = new URLSearchParams({ sede: String(this.club.id), fecha: this.fecha });
                        if (this.deporteId) params.set('deporte_id', String(this.deporteId));
                        const res = await fetch(ocupacionUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } });
                        const data = await res.json();
                        if (!data.ok) return;
                        const mapa = data.ocupados || {};
                        this.canchas = this.canchas.map(c => ({ ...c, ocupados: mapa[c.id] || mapa[String(c.id)] || [] }));
                    } finally {
                        this.cargandoOcupacion = false;
                    }
                },

                estaOcupado(c, h) {
                    return (c.ocupados || []).includes(h);
                },

                estaSeleccionado(c, h) {
                    if (!this.seleccion || this.seleccion.canchaId !== c.id) return false;
                    const bloques = (this.seleccion.duracion || 60) / 60;
                    return h >= this.seleccion.hora && h < this.seleccion.hora + bloques;
                },

                claseCelda(c, h) {
                    if (this.esHoraPasada(h)) {
                        return 'bg-slate-100 cursor-not-allowed opacity-40';
                    }
                    if (this.estaOcupado(c, h)) {
                        return 'bg-slate-100 cursor-not-allowed';
                    }
                    if (!this.tieneTarifaEnHora(c, h)) {
                        return 'bg-slate-50 cursor-not-allowed opacity-50';
                    }
                    if (this.estaSeleccionado(c, h)) {
                        return 'bg-emerald-700/10';
                    }
                    return 'bg-white hover:bg-emerald-700/10 cursor-pointer';
                },

                tituloCelda(c, h) {
                    if (this.esHoraPasada(h)) return 'Horario pasado (No disponible)';
                    if (this.estaOcupado(c, h)) return 'No disponible (Reservado)';
                    if (!this.tieneTarifaEnHora(c, h)) {
                        const turno = this.turnoDeHora(h);
                        return turno === 'dia' ? 'Tarifa disponible solo en horario nocturno' : 'Tarifa disponible solo en horario diurno';
                    }
                    return 'Reservar ' + this.horaLabel(h);
                },

                seleccionar(event, cancha, h) {
                    if (this.estaBloqueado(cancha, h)) return;

                    const puede120 = !this.estaBloqueado(cancha, h + 1) && this.horas.includes(h + 1);

                    // Auto-seleccionar la modalidad que tenga arancel válido para ese turno (Día o Noche)
                    const turno = this.turnoDeHora(h);
                    const tusnesValidosTurno = (cancha.tusnes || []).filter(t => t.horario_turno === turno || t.horario_turno === 'todos');
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
                        tipoUso: modalidadSeleccionada,
                        deporteIds: cancha.deporte_ids || []
                    };
                    this.popup.visible = true;
                },

                elegirDuracion(m) {
                    if (!this.seleccion) return;
                    if (m === 120 && !this.puede120) return;
                    this.seleccion.duracion = m;
                },

                cerrarPopup() {
                    this.popup.visible = false;
                    this.seleccion = null;
                    this.puede120 = false;
                },

                continuar() {
                    if (!this.seleccion) return;
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

                    window.location.href = urlConfirmar + '?' + params.toString();
                },
            };
        }
        </script>
        @endpush
    </div>
</x-portal-reserva-shell>