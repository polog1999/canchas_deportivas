<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirmar reserva | Municipalidad de La Molina</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#f3f6f4] text-slate-800 antialiased" x-data="confirmarMaqueta()">

    <header class="bg-[#1b5e3b] text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
            <a :href="urlVolverHorarios" class="flex items-center gap-2 text-sm font-semibold hover:text-emerald-200">
                <i class="fa-solid fa-arrow-left"></i>
                Volver a horarios
            </a>
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('logo_municipal_negro.png') }}" alt="La Molina"
                    class="h-8 w-auto bg-white rounded px-1.5 py-0.5 object-contain" onerror="this.style.display='none'">
                <span class="font-bold text-sm hidden sm:inline">La Molina</span>
            </a>
            <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-emerald-200">
                <i class="fa-regular fa-user mr-1"></i> Cuenta
            </a>
        </div>
    </header>

    <div class="bg-white border-b border-emerald-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-semibold">
            <span class="text-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i>1. Buscar</span>
            <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>
            <span class="text-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i>2. Elegir turno</span>
            <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>
            <span class="text-[#1b5e3b] bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">3. Confirmar</span>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-2">Paso final</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Revisa y confirma tu reserva</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-2xl">
                Verifica el turno de <span class="font-semibold text-slate-700" x-text="reserva.club"></span>
                e identifica al titular con su documento.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">
            <div class="lg:col-span-3 space-y-5 order-2 lg:order-1">
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-[#1b5e3b] flex items-center justify-center">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                        <h2 class="font-bold text-slate-900">Datos del titular</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- 1. DNI primero --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">DNI / documento</label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <i class="fa-regular fa-address-card absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input type="text" x-model="form.documento" @input="onDocumentoInput()"
                                        maxlength="15" inputmode="numeric"
                                        class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="12345678">
                                </div>
                                <button type="button" @click="buscarDocumento()"
                                    :disabled="buscando || form.documento.replace(/\D/g,'').length < 8"
                                    class="px-4 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] disabled:opacity-40 text-white text-sm font-semibold shrink-0">
                                    <span x-show="!buscando">Validar</span>
                                    <span x-show="buscando"><i class="fa-solid fa-spinner animate-spin"></i></span>
                                </button>
                            </div>
                            <p class="text-[11px] mt-1.5" :class="mensajeClase" x-text="mensaje" x-show="mensaje"></p>
                        </div>

                        {{-- Nombre: visible tras validar; bloqueado y vacío si existe --}}
                        <div class="sm:col-span-2" x-show="estado !== 'pendiente'" x-cloak>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nombre y apellido</label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" x-model="form.nombre"
                                    :disabled="estado === 'existe'"
                                    :placeholder="estado === 'existe' ? 'Se tomará de tu cuenta registrada' : 'Ej. Juan Pérez'"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                    :class="estado === 'existe' ? 'border-slate-200' : 'border-slate-200'">
                            </div>
                        </div>

                        {{-- Teléfono --}}
                        <div x-show="estado !== 'pendiente'" x-cloak :class="estado === 'nuevo' ? '' : 'sm:col-span-2'">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Teléfono / WhatsApp</label>
                            <div class="relative">
                                <i class="fa-solid fa-mobile-screen absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="tel" x-model="form.telefono"
                                    :disabled="estado === 'existe'"
                                    :placeholder="estado === 'existe' ? 'Se tomará de tu cuenta registrada' : '999 999 999'"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                            </div>
                        </div>

                        {{-- Correo solo si el DNI NO existe --}}
                        <div x-show="estado === 'nuevo'" x-cloak>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Correo electrónico</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="email" x-model="form.email"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        {{-- Distrito solo si el DNI NO existe --}}
                        <div class="sm:col-span-2" x-show="estado === 'nuevo'" x-cloak>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Distrito donde vives</label>
                            <div class="relative">
                                <i class="fa-solid fa-map-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <select x-model="form.distrito_id"
                                    class="w-full appearance-none pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                    <option value="">Selecciona tu distrito</option>
                                    <template x-for="d in distritos" :key="d.id">
                                        <option :value="String(d.id)" x-text="d.nombre"></option>
                                    </template>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        {{-- Contraseña solo si el DNI SÍ existe --}}
                        <div class="sm:col-span-2" x-show="estado === 'existe'" x-cloak>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Contraseña</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="password" x-model="form.clave"
                                    @input="errorConfirmacion = ''"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ingresa tu contraseña">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5">
                                Ya tienes cuenta. Confirma con tu contraseña; tus datos personales se tomarán de la base de datos.
                            </p>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 mt-3" x-show="estado === 'nuevo'" x-cloak>
                        Te enviaremos el voucher digital al correo indicado.
                    </p>
                </section>

                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end pt-2">
                    <a :href="urlVolverHorarios"
                        class="inline-flex justify-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cambiar horario
                    </a>
                    <button type="button" @click="confirmar()"
                        :disabled="!puedeConfirmar || confirmando"
                        class="inline-flex justify-center items-center gap-2 px-8 py-3 rounded-xl bg-lime-500 hover:bg-lime-400 disabled:opacity-40 disabled:cursor-not-allowed text-emerald-950 text-sm font-bold shadow-sm transition">
                        <i x-show="confirmando" class="fa-solid fa-spinner animate-spin"></i>
                        Confirmar reserva
                        <i x-show="!confirmando" class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
                <p class="text-xs text-red-600 text-right" x-show="errorConfirmacion" x-text="errorConfirmacion" x-cloak></p>
            </div>

            <aside class="lg:col-span-2 order-1 lg:order-2">
                <div class="lg:sticky lg:top-6 space-y-4">
                    <div class="rounded-2xl overflow-hidden shadow-md border border-emerald-900/10">
                        <div class="h-28 bg-cover bg-center relative"
                            :style="'background-image:url(' + reserva.imagen + ')'">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0f3d28] via-[#0f3d28]/70 to-transparent"></div>
                            <div class="absolute bottom-3 left-4 right-4 text-white">
                                <p class="text-[10px] uppercase tracking-wider text-emerald-200 font-semibold" x-text="reserva.deporte"></p>
                                <h3 class="font-bold text-lg leading-tight" x-text="reserva.club"></h3>
                            </div>
                        </div>
                        <div class="bg-[#123d2a] text-white p-5 space-y-3 text-sm">
                            <p class="text-emerald-100 text-xs flex items-start gap-2">
                                <i class="fa-solid fa-location-dot mt-0.5"></i>
                                <span x-text="reserva.direccion"></span>
                            </p>
                            <div class="h-px bg-white/10"></div>
                            <div class="flex justify-between gap-3">
                                <span class="text-emerald-200 text-xs">Fecha</span>
                                <span class="font-semibold text-right" x-text="reserva.fechaLabel"></span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span class="text-emerald-200 text-xs">Turno</span>
                                <span class="font-semibold text-right" x-text="reserva.turno"></span>
                            </div>
                            <div class="flex justify-between gap-3">
                                <span class="text-emerald-200 text-xs">Cancha</span>
                                <span class="font-semibold text-right" x-text="reserva.cancha"></span>
                            </div>
                            <p class="text-[11px] text-emerald-200/80" x-text="reserva.detalle"></p>
                            <div class="h-px bg-white/10"></div>
                            <div class="flex justify-between items-end">
                                <span class="text-xs text-emerald-200">Total estimado</span>
                                <span class="text-2xl font-bold text-lime-300" x-text="'S/ ' + reserva.precio"></span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white border border-dashed border-emerald-200 px-4 py-3 text-[11px] text-slate-500">
                        <i class="fa-solid fa-flask text-emerald-600 mr-1"></i>
                        Maqueta de flujo · la validación de DNI consulta la base de datos real
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <footer class="mt-10 border-t border-emerald-100 bg-white py-4">
        <p class="text-center text-xs text-slate-400">© {{ date('Y') }} Municipalidad de La Molina</p>
    </footer>

    <style>[x-cloak]{display:none!important}</style>

    <script>
        function confirmarMaqueta() {
            const params = new URLSearchParams(window.location.search);

            const club = params.get('club') || 'Complejo Musa';
            const cancha = params.get('cancha') || 'CANCHA # 1 - F7';
            const fecha = params.get('fecha') || new Date().toISOString().slice(0, 10);
            const hora = params.get('hora') || '17:00';
            const duracion = parseInt(params.get('duracion') || '60', 10);
            const precio = params.get('precio') || '120';
            const detalle = params.get('detalle') || 'Césped sintético | Con iluminación | Descubierta';
            const direccion = params.get('direccion') || 'La Molina, Lima';
            const deporte = params.get('deporte') || 'Fútbol 7';
            const imagen = params.get('imagen') || 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=800&q=80';
            const sedeId = params.get('sede') || '';
            const deporteId = params.get('deporte_id') || '';

            const volverParams = new URLSearchParams();
            if (sedeId) volverParams.set('sede', sedeId);
            if (deporteId) volverParams.set('deporte_id', deporteId);
            if (fecha) volverParams.set('fecha', fecha);
            const urlVolverHorarios = sedeId
                ? (@json(url('/reservar/turno')) + '?' + volverParams.toString())
                : @json(url('/'));

            const [h, m] = hora.split(':').map(Number);
            const finMin = h * 60 + (m || 0) + duracion;
            const fh = String(Math.floor(finMin / 60)).padStart(2, '0');
            const fm = String(finMin % 60).padStart(2, '0');

            const fechaDate = new Date(fecha + 'T12:00:00');
            const fechaLabel = fechaDate.toLocaleDateString('es-PE', {
                weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric'
            });

            let debounceTimer = null;

            return {
                form: { documento: '', nombre: '', telefono: '', email: '', clave: '', distrito_id: '' },
                estado: 'pendiente', // pendiente | existe | nuevo
                buscando: false,
                confirmando: false,
                mensaje: '',
                mensajeTipo: 'info',
                errorConfirmacion: '',
                urlVolverHorarios,
                distritos: @json($distritos),
                reserva: {
                    club,
                    cancha,
                    fechaLabel,
                    turno: `${hora} a ${fh}:${fm} hs`,
                    precio,
                    detalle,
                    direccion,
                    deporte,
                    imagen,
                },

                get mensajeClase() {
                    if (this.mensajeTipo === 'ok') return 'text-emerald-700';
                    if (this.mensajeTipo === 'error') return 'text-red-600';
                    return 'text-slate-500';
                },

                get puedeConfirmar() {
                    if (this.estado === 'pendiente') return false;

                    if (this.estado === 'existe') {
                        return this.form.documento.replace(/\D/g, '').length >= 8
                            && this.form.clave.trim().length >= 4;
                    }

                    return this.form.documento.replace(/\D/g, '').length >= 8
                        && this.form.nombre.trim().length > 2
                        && this.form.telefono.trim().length > 6
                        && this.form.email.includes('@')
                        && String(this.form.distrito_id).length > 0;
                },

                onDocumentoInput() {
                    this.estado = 'pendiente';
                    this.mensaje = '';
                    this.form.nombre = '';
                    this.form.telefono = '';
                    this.form.email = '';
                    this.form.clave = '';
                    this.form.distrito_id = '';
                    this.errorConfirmacion = '';

                    clearTimeout(debounceTimer);
                    const digits = this.form.documento.replace(/\D/g, '');
                    if (digits.length >= 8) {
                        debounceTimer = setTimeout(() => this.buscarDocumento(), 450);
                    }
                },

                async buscarDocumento() {
                    const documento = this.form.documento.replace(/\D/g, '');
                    if (documento.length < 8 || this.buscando) return;

                    this.buscando = true;
                    this.mensaje = 'Consultando documento...';
                    this.mensajeTipo = 'info';

                    try {
                        const url = @json(route('reservar.buscar-documento')) + '?documento=' + encodeURIComponent(documento);
                        const res = await fetch(url, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();

                        if (!data.valido) {
                            this.estado = 'pendiente';
                            this.mensaje = data.mensaje || 'Documento inválido.';
                            this.mensajeTipo = 'error';
                            return;
                        }

                        // No listar datos de BD: limpiar campos visibles
                        this.form.nombre = '';
                        this.form.telefono = '';
                        this.form.email = '';
                        this.form.clave = '';
                        this.form.distrito_id = '';

                        if (data.existe) {
                            this.estado = 'existe';
                            this.mensajeTipo = 'ok';
                        } else {
                            this.estado = 'nuevo';
                            this.mensajeTipo = 'info';
                        }
                        this.mensaje = data.mensaje;
                    } catch (e) {
                        this.estado = 'pendiente';
                        this.mensaje = 'No se pudo consultar el documento. Intenta de nuevo.';
                        this.mensajeTipo = 'error';
                    } finally {
                        this.buscando = false;
                    }
                },

                async confirmar() {
                    if (!this.puedeConfirmar || this.confirmando) return;
                    this.confirmando = true;
                    this.errorConfirmacion = '';

                    try {
                        if (this.estado === 'existe') {
                            const res = await fetch(@json(route('reservar.verificar-acceso')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    documento: this.form.documento.replace(/\D/g, ''),
                                    clave: this.form.clave,
                                }),
                            });
                            const data = await res.json();
                            if (!res.ok || !data.ok) {
                                this.errorConfirmacion = data.mensaje || 'No se pudo verificar el acceso.';
                                return;
                            }
                            window.location.href = data.redirect || @json(route('dashboard'));
                            return;
                        }

                        // Usuario nuevo (maqueta): aún no crea cuenta, va a registro/login
                        window.location.href = @json(route('login'));
                    } catch (e) {
                        this.errorConfirmacion = 'Error de conexión. Intenta nuevamente.';
                    } finally {
                        this.confirmando = false;
                    }
                },
            };
        }
    </script>
</body>
</html>
