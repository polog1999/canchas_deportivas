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

    <x-public-navbar>

        <x-slot:back>

            <a :href="urlVolverHorarios"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#1b5e3b] hover:text-emerald-800 transition">
                <i class="fa-solid fa-arrow-left"></i>
                Volver a horarios
            </a>

        </x-slot:back>

    </x-public-navbar>


    <div class="bg-white border-b border-emerald-100">

        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-semibold">

            <span class="text-emerald-700">
                <i class="fa-solid fa-circle-check mr-1"></i>
                1. Buscar
            </span>

            <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>

            <span class="text-emerald-700">
                <i class="fa-solid fa-circle-check mr-1"></i>
                2. Elegir turno
            </span>

            <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>

            <span class="text-[#1b5e3b] bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                3. Confirmar
            </span>

        </div>

    </div>


    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        <div class="mb-8">

            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-2">
                Paso final
            </p>

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                Revisa y confirma tu reserva
            </h1>

            <p class="text-sm text-slate-500 mt-2 max-w-2xl" x-show="estado !== 'sesion'">
                Verifica el turno de
                <span class="font-semibold text-slate-700" x-text="reserva.club"></span>
                e identifica al titular con su documento.
            </p>

            <p class="text-sm text-slate-500 mt-2 max-w-2xl" x-show="estado === 'sesion'" x-cloak>
                Verifica el turno de
                <span class="font-semibold text-slate-700" x-text="reserva.club"></span>
                y confirma con tu contraseña para continuar al pago.
            </p>

        </div>


        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

            <div class="lg:col-span-3 space-y-5 order-2 lg:order-1">

                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">

                    <div class="flex items-center gap-2 mb-4">

                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-[#1b5e3b] flex items-center justify-center">
                            <i class="fa-solid fa-id-card"></i>
                        </span>

                        <h2 class="font-bold text-slate-900" x-text="estado === 'sesion' ? 'Confirmar identidad' : 'Datos del titular'">
                            Datos del titular
                        </h2>

                    </div>


                    <div x-show="estado === 'sesion'" x-cloak
                        class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-800">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        Reservarás con tu cuenta activa. Ingresa tu contraseña para continuar al pago.
                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- ================================================= --}}
                        {{-- TIPO DE DOCUMENTO --}}
                        {{-- ================================================= --}}

                        <div class="sm:col-span-2" x-show="estado !== 'sesion'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Tipo de documento
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-id-card-clip absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <select x-model="form.tipo_documento_id" @change="onTipoDocumentoChange()"
                                    :disabled="estado === 'sesion'"
                                    class="w-full appearance-none pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white disabled:bg-slate-100 disabled:text-slate-500">

                                    <option value="">
                                        Selecciona tu tipo de documento
                                    </option>
                                    @php
                                        $tipoDocumentos = App\Models\TipoDocumento::get();
                                    @endphp
                                    @foreach ($tipoDocumentos as $tipoDocumento)
                                        <option value="{{ $tipoDocumento->id }}">
                                            {{ $tipoDocumento->abreviatura }}
                                        </option>
                                    @endforeach

                                </select>

                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- DNI / DOCUMENTO --}}
                        {{-- ================================================= --}}

                        <div class="sm:col-span-2" x-show="estado !== 'sesion'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                DNI / documento
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-regular fa-address-card absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="text" x-model="form.documento" @input="onDocumentoInput()"
                                    maxlength="15" inputmode="numeric"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-500"
                                    placeholder="12345678">

                            </div>

                            <p class="text-[11px] mt-1.5" :class="mensajeClase" x-text="mensaje"
                                x-show="mensaje"></p>

                        </div>


                        {{-- Nombres / apellidos --}}

                        <div x-show="estado !== 'pendiente' && estado !== 'sesion'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Nombres
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="text" x-model="form.nombres"
                                    :disabled="estado === 'existe' || estado === 'sesion'"
                                    :placeholder="estado === 'existe' ? 'Se tomará de tu cuenta' : 'Ej. Juan Carlos'"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">

                            </div>

                        </div>


                        <div x-show="estado !== 'pendiente' && estado !== 'sesion'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Apellido paterno
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="text" x-model="form.apellido_paterno"
                                    :disabled="estado === 'existe' || estado === 'sesion'"
                                    :placeholder="estado === 'existe' ? 'Se tomará de tu cuenta' : 'Ej. Pérez'"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">

                            </div>

                        </div>


                        <div class="sm:col-span-2" x-show="estado !== 'pendiente' && estado !== 'sesion'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Apellido materno
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="text" x-model="form.apellido_materno"
                                    :disabled="estado === 'existe' || estado === 'sesion'"
                                    :placeholder="estado === 'existe' ? 'Se tomará de tu cuenta' : 'Ej. García'"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">

                            </div>

                        </div>


                        {{-- Teléfono --}}

                        <div x-show="estado !== 'pendiente' && estado !== 'sesion'" x-cloak
                            :class="estado === 'nuevo' ? '' : 'sm:col-span-2'">

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Teléfono / WhatsApp
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-mobile-screen absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="tel" x-model="form.telefono" :disabled="estado === 'existe'"
                                    :placeholder="estado === 'existe' ? 'Se tomará de tu cuenta registrada' : '999 999 999'"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">

                            </div>

                        </div>


                        {{-- Correo solo si el DNI NO existe --}}

                        <div x-show="estado === 'nuevo'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Correo electrónico
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="email" x-model="form.email"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="correo@ejemplo.com">

                            </div>

                        </div>


                        {{-- Distrito solo si el DNI NO existe --}}

                        <div class="sm:col-span-2" x-show="estado === 'nuevo'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Distrito donde vives
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-map-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <select x-model="form.distrito_id"
                                    class="w-full appearance-none pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">

                                    <option value="">
                                        Selecciona tu distrito
                                    </option>

                                    <template x-for="d in distritos" :key="d.id">
                                        <option :value="String(d.id)" x-text="d.nombre"></option>
                                    </template>

                                </select>

                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>

                            </div>

                        </div>


                        {{-- Contraseña si el DNI existe o hay sesión activa --}}

                        <div class="sm:col-span-2" x-show="estado === 'existe' || estado === 'sesion'" x-cloak>

                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Contraseña
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                                <input type="password" x-model="form.clave" @input="errorConfirmacion = ''"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ingresa tu contraseña">

                            </div>

                            <p class="text-[11px] text-slate-400 mt-1.5" x-show="estado === 'existe'">
                                Ya tienes cuenta. Confirma con tu contraseña; tus datos personales se tomarán de la base
                                de datos.
                            </p>

                            <p class="text-[11px] text-slate-400 mt-1.5" x-show="estado === 'sesion'">
                                Por seguridad, confirma tu identidad con la contraseña de tu cuenta.
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

                    <button type="button" @click="confirmar()" :disabled="!puedeConfirmar || confirmando"
                        class="inline-flex justify-center items-center gap-2 px-8 py-3 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold shadow-sm transition">

                        <i x-show="confirmando" class="fa-solid fa-spinner animate-spin"></i>

                        Confirmar reserva

                        <i x-show="!confirmando" class="fa-solid fa-arrow-right text-xs"></i>

                    </button>

                </div>


                <p class="text-xs text-red-600 text-right" x-show="errorConfirmacion" x-text="errorConfirmacion"
                    x-cloak></p>

            </div>


            <aside class="lg:col-span-2 order-1 lg:order-2">

                <div class="lg:sticky lg:top-6 space-y-4">

                    <div class="rounded-2xl overflow-hidden shadow-md border border-emerald-900/10">

                        <div class="h-28 bg-cover bg-center relative"
                            :style="'background-image:url(' + reserva.imagen + ')'">

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-[#0f3d28] via-[#0f3d28]/70 to-transparent">
                            </div>

                            <div class="absolute bottom-3 left-4 right-4 text-white">

                                <p class="text-[10px] uppercase tracking-wider text-emerald-200 font-semibold"
                                    x-text="reserva.deporte"></p>

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

                                <span class="text-emerald-200 text-xs">
                                    Fecha
                                </span>

                                <span class="font-semibold text-right" x-text="reserva.fechaLabel"></span>

                            </div>

                            <div class="flex justify-between gap-3">

                                <span class="text-emerald-200 text-xs">
                                    Turno
                                </span>

                                <span class="font-semibold text-right" x-text="reserva.turno"></span>

                            </div>

                            <div class="flex justify-between gap-3">

                                <span class="text-emerald-200 text-xs">
                                    Cancha
                                </span>

                                <span class="font-semibold text-right" x-text="reserva.cancha"></span>

                            </div>

                            <p class="text-[11px] text-emerald-200/80" x-text="reserva.detalle"></p>

                            <div class="h-px bg-white/10"></div>

                            <div class="flex justify-between items-end">

                                <span class="text-xs text-emerald-200">
                                    Total estimado
                                </span>

                                <span class="text-2xl font-bold text-emerald-200"
                                    x-text="'S/ ' + reserva.precio"></span>

                            </div>

                        </div>

                    </div>


                    <div
                        class="rounded-xl bg-white border border-dashed border-emerald-200 px-4 py-3 text-[11px] text-slate-500">

                        <i class="fa-solid fa-flask text-emerald-600 mr-1"></i>

                        Maqueta de flujo · la validación de documento consulta la base de datos real

                    </div>

                </div>

            </aside>

        </div>

    </main>


    <footer class="mt-10 border-t border-emerald-100 bg-white py-4">

        <p class="text-center text-xs text-slate-400">
            © {{ date('Y') }} Municipalidad de La Molina
        </p>

    </footer>


    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>


    <script>
        function confirmarMaqueta() {

            const params = new URLSearchParams(window.location.search);

            const club = params.get('club') || 'Complejo Musa';

            const cancha = params.get('cancha') || 'CANCHA # 1 - F7';

            const fecha = params.get('fecha') ||
                new Date().toISOString().slice(0, 10);

            const hora = params.get('hora') || '17:00';

            const duracion = parseInt(
                params.get('duracion') || '60',
                10
            );

            const precio = params.get('precio') || '120';

            const detalle = params.get('detalle') ||
                'Césped sintético | Con iluminación | Descubierta';

            const direccion = params.get('direccion') ||
                'La Molina, Lima';

            const deporte = params.get('deporte') ||
                'Fútbol 7';

            const imagen = params.get('imagen') ||
                'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=800&q=80';

            const sedeId = params.get('sede') || '';

            const deporteId = params.get('deporte_id') || '';

            const volverParams = new URLSearchParams();

            if (sedeId) {
                volverParams.set('sede', sedeId);
            }

            if (deporteId) {
                volverParams.set('deporte_id', deporteId);
            }

            if (fecha) {
                volverParams.set('fecha', fecha);
            }

            const urlVolverHorarios = sedeId ?
                (@json(route('reservar.turno')) + '?' + volverParams.toString()) :
                @json(url('/'));


            const [h, m] = hora.split(':').map(Number);

            const finMin = h * 60 + (m || 0) + duracion;

            const fh = String(
                Math.floor(finMin / 60)
            ).padStart(2, '0');

            const fm = String(
                finMin % 60
            ).padStart(2, '0');


            const fechaDate = new Date(
                fecha + 'T12:00:00'
            );

            const fechaLabel = fechaDate.toLocaleDateString(
                'es-PE', {
                    weekday: 'short',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }
            );


            let debounceTimer = null;

            const usuarioPortal = @json($usuarioPortal ?? null);


            return {

                form: {

                    tipo_documento_id: usuarioPortal?.tipo_documento_id || '',

                    documento: usuarioPortal?.documento || '',

                    nombres: usuarioPortal?.nombres || '',

                    apellido_paterno: usuarioPortal?.apellido_paterno || '',

                    apellido_materno: usuarioPortal?.apellido_materno || '',

                    telefono: '',

                    email: usuarioPortal?.email || '',

                    clave: '',

                    distrito_id: usuarioPortal?.distrito_id || '',

                },


                estado: usuarioPortal ?
                    'sesion' :
                    'pendiente',

                documentoEditable: usuarioPortal?.documento_editable ?? false,

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

                    if (this.mensajeTipo === 'ok') {
                        return 'text-emerald-700';
                    }

                    if (this.mensajeTipo === 'error') {
                        return 'text-red-600';
                    }

                    return 'text-slate-500';

                },


                get puedeConfirmar() {

                    if (this.estado === 'pendiente') {
                        return false;
                    }

                    if (this.estado === 'sesion') {
                        return this.form.clave.trim().length >= 4;
                    }

                    if (this.estado === 'existe') {

                        return this.form.tipo_documento_id &&
                            this.form.documento.replace(/\D/g, '').length >= 8 &&
                            this.form.clave.trim().length >= 4;

                    }

                    return this.form.tipo_documento_id &&
                        this.form.documento.replace(/\D/g, '').length >= 8 &&
                        this.form.nombres.trim().length > 1 &&
                        this.form.apellido_paterno.trim().length > 1 &&
                        this.form.apellido_materno.trim().length > 1 &&
                        this.form.telefono.trim().length > 6 &&
                        this.form.email.includes('@') &&
                        String(this.form.distrito_id).length > 0;

                },


                onTipoDocumentoChange() {

                    if (this.estado === 'sesion') {
                        return;
                    }

                    this.estado = 'pendiente';

                    this.mensaje = '';

                    this.form.documento = '';

                    this.form.nombres = '';

                    this.form.apellido_paterno = '';

                    this.form.apellido_materno = '';

                    this.form.telefono = '';

                    this.form.email = '';

                    this.form.clave = '';

                    this.form.distrito_id = '';

                    this.errorConfirmacion = '';

                    clearTimeout(debounceTimer);

                },


                onDocumentoInput() {

                    if (this.estado === 'sesion') {
                        return;
                    }

                    this.estado = 'pendiente';

                    this.mensaje = '';

                    this.form.nombres = '';

                    this.form.apellido_paterno = '';

                    this.form.apellido_materno = '';

                    this.form.telefono = '';

                    this.form.email = '';

                    this.form.clave = '';

                    this.form.distrito_id = '';

                    this.errorConfirmacion = '';

                    clearTimeout(debounceTimer);


                    const digits =
                        this.form.documento.replace(/\D/g, '');


                    if (
                        this.form.tipo_documento_id &&
                        digits.length >= 8
                    ) {

                        debounceTimer = setTimeout(
                            () => this.buscarDocumento(),
                            450
                        );

                    }

                },


                async buscarDocumento() {

                    const documento =
                        this.form.documento.replace(/\D/g, '');


                    if (
                        !this.form.tipo_documento_id ||
                        documento.length < 8 ||
                        this.buscando
                    ) {
                        return;
                    }


                    this.buscando = true;

                    this.mensaje =
                        'Consultando documento...';

                    this.mensajeTipo = 'info';


                    try {

                        const url =
                            @json(route('reservar.buscar-documento')) +
                            '?tipo_documento_id=' +
                            encodeURIComponent(this.form.tipo_documento_id) +
                            '&documento=' +
                            encodeURIComponent(documento);


                        const res = await fetch(url, {

                            headers: {
                                'Accept': 'application/json',
                            },

                        });


                        const data = await res.json();


                        if (!data.valido) {

                            this.estado = 'pendiente';

                            this.mensaje =
                                data.mensaje ||
                                'Documento inválido.';

                            this.mensajeTipo = 'error';

                            return;

                        }


                        // No listar datos de BD:
                        // limpiar campos visibles

                        this.form.nombres = '';

                        this.form.apellido_paterno = '';

                        this.form.apellido_materno = '';

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

                        this.mensaje =
                            'No se pudo consultar el documento. Intenta de nuevo.';

                        this.mensajeTipo = 'error';

                    } finally {

                        this.buscando = false;

                    }

                },


                async confirmar() {

                    if (
                        !this.puedeConfirmar ||
                        this.confirmando
                    ) {
                        return;
                    }


                    this.confirmando = true;

                    this.errorConfirmacion = '';


                    const guardarYIrAPago = () => {

                        const params =
                            new URLSearchParams(
                                window.location.search || ''
                            );


                        const payload = {

                            sede: params.get('sede') || '',

                            club: params.get('club') || club,

                            direccion: params.get('direccion') || direccion,

                            imagen: params.get('imagen') || imagen,

                            cancha: params.get('cancha') || cancha,

                            cancha_id: params.get('cancha_id') || '',

                            detalle: params.get('detalle') || detalle,

                            fecha: params.get('fecha') || fecha,

                            hora: params.get('hora') || hora,

                            duracion: parseInt(
                                params.get('duracion') ||
                                String(duracion),
                                10
                            ) || 60,

                            precio: parseFloat(
                                params.get('precio') ||
                                String(precio)
                            ) || 0,

                            deporte: params.get('deporte') ||
                                deporte,

                            deporte_id: params.get('deporte_id') ||
                                deporteId,

                            tusne_id: params.get('tusne_id') || '',

                            estado_titular: this.estado === 'sesion' ?
                                'existe' :
                                this.estado,

                            tipo_documento_id: this.form.tipo_documento_id,

                            documento: this.form.documento.replace(/\D/g, ''),

                            nombres: this.form.nombres.trim(),

                            apellido_paterno: this.form.apellido_paterno.trim(),

                            apellido_materno: this.form.apellido_materno.trim(),

                            telefono: this.form.telefono.trim(),

                            email: this.form.email.trim(),

                            distrito_id: String(
                                this.form.distrito_id || ''
                            ),

                        };


                        try {

                            sessionStorage.setItem(
                                'reserva_pago',
                                JSON.stringify(payload)
                            );

                        } catch (e) {
                            /* ignore */
                        }


                        const qs =
                            window.location.search || '';


                        window.location.href =
                            @json(route('reservar.pago')) +
                            qs;

                    };


                    try {

                        if (this.estado === 'existe') {

                            const res = await fetch(
                                @json(route('reservar.verificar-acceso')), {

                                    method: 'POST',

                                    headers: {

                                        'Content-Type': 'application/json',

                                        'Accept': 'application/json',

                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,

                                    },

                                    body: JSON.stringify({

                                        tipo_documento_id: this.form.tipo_documento_id,

                                        documento: this.form.documento.replace(
                                            /\D/g,
                                            ''
                                        ),

                                        clave: this.form.clave,

                                    }),

                                }
                            );


                            const data =
                                await res.json();


                            if (
                                !res.ok ||
                                !data.ok
                            ) {

                                this.errorConfirmacion =
                                    data.mensaje ||
                                    'No se pudo verificar el acceso.';

                                return;

                            }

                        }

                        if (this.estado === 'sesion') {

                            const res = await fetch(
                                @json(route('reservar.verificar-clave-sesion')), {

                                    method: 'POST',

                                    headers: {

                                        'Content-Type': 'application/json',

                                        'Accept': 'application/json',

                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,

                                    },

                                    body: JSON.stringify({

                                        clave: this.form.clave,

                                    }),

                                }
                            );


                            const data =
                                await res.json();


                            if (
                                !res.ok ||
                                !data.ok
                            ) {

                                this.errorConfirmacion =
                                    data.mensaje ||
                                    'No se pudo verificar tu contraseña.';

                                return;

                            }

                        }


                        guardarYIrAPago();


                    } catch (e) {

                        this.errorConfirmacion =
                            'Error de conexión. Intenta nuevamente.';

                    } finally {

                        this.confirmando = false;

                    }

                },

            };

        }
    </script>

</body>

</html>
