<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar cancha | Municipalidad de La Molina</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="reservaMaqueta()">

    <x-public-navbar back-href="/" back-label="Volver a sedes" />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 sm:p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-stretch gap-2 bg-slate-100 rounded-2xl p-2 sm:p-1.5 sm:pl-4">
                <div class="flex items-center gap-2 flex-1 min-w-0 px-2 py-1.5 sm:py-0">
                    <i class="fa-solid fa-location-dot text-[#1b5e3b]"></i>
                    <input type="text" x-model="filtros.distrito" class="bg-transparent w-full text-sm font-medium focus:outline-none truncate"
                        placeholder="Buscar complejo o sede">
                </div>
                <div class="hidden sm:block w-px bg-slate-300 my-2"></div>
                <div class="flex items-center gap-2 flex-1 min-w-0 px-2 py-1.5 sm:py-0">
                    <i class="fa-solid fa-futbol text-[#1b5e3b]"></i>
                    <select x-model="filtros.deporteId" class="bg-transparent w-full text-sm font-medium focus:outline-none">
                        <option value="">Todos los deportes</option>
                        <template x-for="d in deportes" :key="d.id">
                            <option :value="String(d.id)" x-text="d.nombre"></option>
                        </template>
                    </select>
                </div>
                <div class="hidden sm:block w-px bg-slate-300 my-2"></div>
                <div class="flex items-center gap-2 flex-1 min-w-0 px-2 py-1.5 sm:py-0">
                    <i class="fa-regular fa-calendar text-[#1b5e3b]"></i>
                    <input type="date" x-model="filtros.fecha" class="bg-transparent w-full text-sm font-medium focus:outline-none">
                </div>
                <div class="hidden sm:block w-px bg-slate-300 my-2"></div>
                <div class="flex items-center gap-2 flex-1 min-w-0 px-2 py-1.5 sm:py-0">
                    <i class="fa-regular fa-clock text-[#1b5e3b]"></i>
                    <select x-model="filtros.hora" class="bg-transparent w-full text-sm font-medium focus:outline-none">
                        <option value="">Cualquier hora</option>
                        <template x-for="h in horasOpciones" :key="h">
                            <option :value="h" x-text="h + 'hs'"></option>
                        </template>
                    </select>
                </div>
                <button type="button" @click="buscar()"
                    class="bg-[#1b5e3b] hover:bg-[#164d31] text-white font-bold text-sm px-6 py-2.5 rounded-xl transition shrink-0">
                    Buscar
                </button>
            </div>
        </div>

        <p class="text-sm font-bold text-slate-800 mb-5">
            <span x-text="clubesVisibles.length"></span> complejos encontrados
            <span x-show="deporteActivo" class="font-normal text-slate-500">
                · <span class="font-semibold text-slate-700" x-text="deporteActivo"></span>
            </span>
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <template x-for="club in clubesVisibles" :key="club.id">
                <article class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition group">
                    <button type="button" @click="irATurnos(club)" class="block w-full text-left">
                        <div class="relative aspect-[16/10] bg-slate-200 overflow-hidden">
                            <img :src="club.imagen" :alt="club.nombre"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1459865266369-566976b10f9e?auto=format&fit=crop&w=800&q=80'">
                            <div class="absolute bottom-2 right-2 bg-black/75 text-white text-xs font-bold px-2.5 py-1 rounded-lg"
                                x-text="club.precioDesde > 0 ? ('desde S/ ' + club.precioDesde) : 'Ver turnos'"></div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-slate-900 truncate group-hover:text-[#1b5e3b]" x-text="club.nombre"></h3>
                            <p class="text-xs text-slate-500 mt-1 flex items-start gap-1">
                                <i class="fa-solid fa-location-dot mt-0.5 text-slate-400"></i>
                                <span x-text="club.direccion"></span>
                            </p>
                        </div>
                    </button>
                </article>
            </template>
        </div>

        <p class="mt-10 text-center text-sm text-slate-400" x-show="clubesVisibles.length === 0" x-cloak>
            No hay complejos para los filtros seleccionados.
        </p>
    </main>

    <script>
        function reservaMaqueta() {
            const params = new URLSearchParams(window.location.search);

            return {
                filtros: {
                    distrito: '',
                    deporteId: @json($deporte_id ? (string) $deporte_id : ''),
                    fecha: @json($fecha),
                    hora: params.get('hora') || '',
                },
                deportes: @json($deportes),
                clubes: @json($sedes),
                horasOpciones: ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00'],

                get deporteActivo() {
                    if (!this.filtros.deporteId) return '';
                    const d = this.deportes.find(x => String(x.id) === String(this.filtros.deporteId));
                    return d ? d.nombre : '';
                },

                get clubesVisibles() {
                    const q = this.filtros.distrito.trim().toLowerCase();
                    const dep = this.filtros.deporteId;

                    return this.clubes.filter(c => {
                        if (q && !c.nombre.toLowerCase().includes(q) && !c.direccion.toLowerCase().includes(q)) {
                            return false;
                        }
                        if (dep && !(c.deporte_ids || []).map(String).includes(String(dep))) {
                            return false;
                        }
                        return true;
                    });
                },

                buscar() {
                    const params = new URLSearchParams();
                    if (this.filtros.deporteId) params.set('deporte_id', this.filtros.deporteId);
                    if (this.filtros.fecha) params.set('fecha', this.filtros.fecha);
                    if (this.filtros.hora) params.set('hora', this.filtros.hora);
                    const qs = params.toString();
                    window.location.href = @json(route('reservar')) + (qs ? '?' + qs : '');
                },

                irATurnos(club) {
                    let deporteId = this.filtros.deporteId;
                    if (!deporteId && club.deporte_ids?.length) {
                        deporteId = String(club.deporte_ids[0]);
                    }
                    const params = new URLSearchParams({
                        sede: club.id,
                        fecha: this.filtros.fecha,
                    });
                    if (deporteId) params.set('deporte_id', deporteId);
                    window.location.href = @json(route('reservar.turno')) + '?' + params.toString();
                },
            };
        }
    </script>
</body>
</html>
