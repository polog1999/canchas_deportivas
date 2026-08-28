<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pago | Municipalidad de La Molina</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#f3f6f4] text-slate-800 antialiased" x-data="pagoReserva()">

    <x-public-navbar
        :back-href="url('/reservar/confirmar') . (request()->getQueryString() ? '?' . request()->getQueryString() : '')"
        back-label="Volver a confirmar"
    />

    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm min-h-[280px] p-6 sm:p-8 flex flex-col items-center justify-center gap-6">

            <div class="w-full max-w-md text-center">
                <p class="text-sm text-slate-500">Total a pagar</p>
                <p class="text-3xl font-bold text-[#1b5e3b]" x-text="'S/ ' + Number(monto).toFixed(2)"></p>
            </div>

            @if (session('error'))
                <p class="text-sm text-red-600 text-center max-w-md">{{ session('error') }}</p>
            @endif

            <label class="flex items-start gap-3 max-w-md cursor-pointer select-none">
                <input type="checkbox" x-model="aceptaTerminos"
                    class="mt-1 w-4 h-4 rounded border-slate-300 text-[#1b5e3b] focus:ring-[#1b5e3b]">
                <span class="text-sm text-slate-700 leading-relaxed">
                    Acepto los
                    <button type="button"
                        @click.prevent="mostrarTerminos = true"
                        class="font-semibold text-[#1b5e3b] underline underline-offset-2 hover:text-[#164d31]">
                        términos y condiciones
                    </button>
                    de la reserva y el pago.
                </span>
            </label>

            <div class="w-full max-w-md space-y-3">
                <p x-show="error" x-cloak x-text="error" class="text-sm text-red-600 text-center"></p>
                <p x-show="exito" x-cloak x-text="exito" class="text-sm text-emerald-700 text-center"></p>

                <button type="button"
                    x-show="!sessionKey"
                    @click="iniciarPago()"
                    :disabled="!aceptaTerminos || pagando"
                    class="w-full py-3 rounded-xl text-white text-sm font-semibold transition
                        bg-[#1b5e3b] hover:bg-[#164d31]
                        disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-[#1b5e3b]">
                    <span x-show="!pagando">Pagar con tarjeta / QR</span>
                    <span x-show="pagando" x-cloak>Conectando con pasarela…</span>
                </button>

                <div x-show="sessionKey" class="space-y-2">
                    <button type="button" @click="reiniciarBoton()"
                        class="text-xs text-slate-500 underline hover:text-red-600 w-full text-right">
                        ¿Problemas con el botón? Regenerar
                    </button>
                </div>

                {{-- Siempre en el DOM (sin x-cloak): Niubiz no pinta el botón si el contenedor está oculto al cargar el script --}}
                <div id="niubiz-container" class="flex justify-center min-h-[52px] w-full"></div>
            </div>
        </div>
    </main>

    {{-- Popup términos y condiciones --}}
    <div x-show="mostrarTerminos" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="mostrarTerminos = false">
        <div class="absolute inset-0 bg-black/50" @click="mostrarTerminos = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden"
            @click.stop
            x-transition>
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
                <h2 class="text-base sm:text-lg font-bold text-slate-900">Términos y condiciones</h2>
                <button type="button" @click="mostrarTerminos = false"
                    class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 flex items-center justify-center"
                    aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-5 py-4 overflow-y-auto text-sm text-slate-600 space-y-3 leading-relaxed">
                <p>
                    Al confirmar y pagar la reserva de canchas deportivas de la Municipalidad de La Molina,
                    usted declara haber leído y aceptado las siguientes condiciones:
                </p>
                <ol class="list-decimal pl-5 space-y-2">
                    <li>La reserva es personal e intransferible y está sujeta a la disponibilidad del horario elegido.</li>
                    <li>El pago debe realizarse a través de la pasarela habilitada. La reserva se confirma únicamente tras la acreditación del pago.</li>
                    <li>Debe presentarse con documento de identidad al momento de hacer uso de la cancha.</li>
                    <li>El retraso o la inasistencia pueden implicar la pérdida del turno sin derecho a reembolso, según la normativa vigente.</li>
                    <li>Está prohibido el uso de la infraestructura para fines distintos a la práctica deportiva autorizada.</li>
                    <li>La Municipalidad se reserva el derecho de suspender o reprogramar turnos por mantenimiento, clima adverso u otras causas de fuerza mayor.</li>
                    <li>Los datos personales proporcionados se usarán únicamente para gestionar la reserva y el pago, conforme a la normativa de protección de datos.</li>
                    <li>Los pagos digitales vía Niubiz están sujetos a validación antifraude. No se almacenan datos sensibles de tarjetas.</li>
                </ol>
                <p class="text-xs text-slate-400 pt-1">
                    Municipalidad de La Molina — Servicio de reserva de canchas deportivas.
                </p>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" @click="mostrarTerminos = false; aceptaTerminos = true"
                    class="px-5 py-2.5 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] text-white text-sm font-semibold transition">
                    Entendido, acepto
                </button>
            </div>
        </div>
    </div>

    <script>
        function pagoReserva() {
            const flashError = @json(session('error'));

            return {
                aceptaTerminos: false,
                mostrarTerminos: false,
                pagando: false,
                error: flashError || '',
                exito: '',
                sessionKey: null,
                purchaseNumber: null,
                monto: 0,
                reservaId: null,
                verifyUrl: null,
                buttonUrl: @json(config('niubiz.button_url')),
                merchantId: @json(config('niubiz.merchant_id')),
                merchantLogo: @json(asset('favicon.png')),

                init() {
                    const p = this.leerPayload();
                    this.monto = Number(p.precio || 0);
                    this.reservaId = p.reserva_id || null;
                },

                payloadDesdeUrl() {
                    const p = new URLSearchParams(window.location.search || '');
                    return {
                        sede: p.get('sede') || '',
                        club: p.get('club') || '',
                        direccion: p.get('direccion') || '',
                        imagen: p.get('imagen') || '',
                        cancha: p.get('cancha') || '',
                        cancha_id: p.get('cancha_id') || '',
                        detalle: p.get('detalle') || '',
                        fecha: p.get('fecha') || '',
                        hora: p.get('hora') || '',
                        duracion: parseInt(p.get('duracion') || '60', 10) || 60,
                        precio: parseFloat(p.get('precio') || '0') || 0,
                        deporte: p.get('deporte') || '',
                        deporte_id: p.get('deporte_id') || '',
                    };
                },

                leerPayload() {
                    let stored = null;
                    try {
                        stored = JSON.parse(sessionStorage.getItem('reserva_pago') || 'null');
                    } catch (e) {
                        stored = null;
                    }
                    return Object.assign({}, this.payloadDesdeUrl(), stored || {});
                },

                reiniciarBoton() {
                    this.sessionKey = null;
                    this.purchaseNumber = null;
                    this.verifyUrl = null;
                    const container = document.getElementById('niubiz-container');
                    if (container) container.innerHTML = '';
                },

                montarBotonNiubiz(detail) {
                    const pintar = () => {
                        const container = document.getElementById('niubiz-container');
                        if (!container) {
                            this.error = 'No se encontró el contenedor del botón de pago.';
                            return;
                        }

                        container.innerHTML = '';

                        const form = document.createElement('form');
                        form.id = 'frmNiubiz';
                        form.action = detail.verifyUrl;
                        form.method = 'POST';

                        const script = document.createElement('script');
                        script.type = 'text/javascript';
                        script.src = (detail.buttonUrl || this.buttonUrl) + '?t=' + Date.now();
                        script.setAttribute('data-sessiontoken', detail.sessionKey);
                        script.setAttribute('data-channel', 'web');
                        script.setAttribute('data-merchantid', String(detail.merchantId || this.merchantId));
                        script.setAttribute('data-purchasenumber', String(detail.purchaseNumber));
                        script.setAttribute('data-amount', Number(detail.amount).toFixed(2));
                        script.setAttribute('data-expirationminutes', '20');
                        script.setAttribute('data-timeouturl', detail.timeoutUrl || window.location.href);
                        script.setAttribute('data-merchantlogo', this.merchantLogo);
                        script.setAttribute('data-formbuttoncolor', '#1b5e3b');
                        script.onerror = () => {
                            this.error = 'No se pudo cargar el script de Niubiz. Revisa tu conexión o regenera el botón.';
                        };

                        form.appendChild(script);
                        container.appendChild(form);
                    };

                    // Esperar a que Alpine muestre el bloque y el layout pinte el contenedor
                    this.$nextTick(() => {
                        setTimeout(pintar, 50);
                    });
                },

                async iniciarPago() {
                    if (!this.aceptaTerminos || this.pagando) return;
                    this.pagando = true;
                    this.error = '';
                    this.exito = '';

                    const payload = this.leerPayload();
                    payload.acepto_terminos = true;
                    if (this.reservaId) payload.reserva_id = this.reservaId;

                    try {
                        const res = await fetch(@json(route('reservar.registrar')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Pago-Query': window.location.search ? window.location.search.slice(1) : '',
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data.ok) {
                            if (data.errors) {
                                const first = Object.values(data.errors)[0];
                                this.error = Array.isArray(first) ? first[0] : String(first);
                            } else {
                                this.error = data.mensaje || data.message
                                    || ('No se pudo iniciar el pago' + (res.status ? ' (HTTP ' + res.status + ')' : '') + '.');
                            }
                            return;
                        }

                        if (data.sin_pasarela) {
                            try { sessionStorage.removeItem('reserva_pago'); } catch (e) {}
                            this.exito = data.mensaje || 'Reserva confirmada.';
                            if (data.redirect) {
                                setTimeout(() => { window.location.href = data.redirect; }, 1000);
                            }
                            return;
                        }

                        this.reservaId = data.reserva_id;
                        this.sessionKey = data.sessionKey;
                        this.purchaseNumber = data.purchaseNumber;
                        this.verifyUrl = data.verifyUrl;
                        this.monto = Number(data.amount);

                        try {
                            const stored = this.leerPayload();
                            stored.reserva_id = data.reserva_id;
                            sessionStorage.setItem('reserva_pago', JSON.stringify(stored));
                        } catch (e) {}

                        this.montarBotonNiubiz(data);
                    } catch (e) {
                        this.error = 'Error de conexión. Intenta nuevamente.';
                    } finally {
                        this.pagando = false;
                    }
                },
            };
        }
    </script>

</body>
</html>
