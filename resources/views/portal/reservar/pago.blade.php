@php
    $urlConfirmar = route('portal.reservar.confirmar') . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
@endphp

<x-portal-reserva-shell
    title="Pago de reserva"
    :step="3"
    :back-href="$urlConfirmar"
    back-label="Volver a confirmar"
    :alpine="true"
>
    <div x-data="pagoPortal()" class="max-w-3xl mx-auto">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900">Pago de reserva</h1>
            <p class="text-sm text-slate-500 mt-1">Completa el pago con tarjeta o QR mediante Niubiz.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 flex flex-col items-center gap-6">
            <div class="text-center">
                <p class="text-sm text-slate-500">Total a pagar</p>
                <p class="text-3xl font-bold text-emerald-800" x-text="'S/ ' + Number(monto).toFixed(2)"></p>
            </div>

            @if (session('error'))
                <p class="text-sm text-red-600 text-center">{{ session('error') }}</p>
            @endif

            <label class="flex items-start gap-3 max-w-md cursor-pointer">
                <input type="checkbox" x-model="aceptaTerminos" class="mt-1 w-4 h-4 rounded text-emerald-700 focus:ring-emerald-700">
                <span class="text-sm text-slate-700">
                    Acepto los
                    <button type="button" @click.prevent="mostrarTerminos = true" class="font-semibold text-emerald-800 underline">términos y condiciones</button>
                    de la reserva y el pago.
                </span>
            </label>

            <div class="w-full max-w-md space-y-3">
                <p x-show="error" x-cloak x-text="error" class="text-sm text-red-600 text-center"></p>
                <p x-show="exito" x-cloak x-text="exito" class="text-sm text-emerald-700 text-center"></p>

                <button type="button" x-show="!sessionKey" @click="iniciarPago()" :disabled="!aceptaTerminos || pagando"
                    class="w-full py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold disabled:opacity-50">
                    <span x-show="!pagando">Pagar con tarjeta / QR</span>
                    <span x-show="pagando" x-cloak>Conectando con pasarela…</span>
                </button>

                <div x-show="sessionKey">
                    <button type="button" @click="reiniciarBoton()" class="text-xs text-slate-500 underline w-full text-right">
                        ¿Problemas? Regenerar botón
                    </button>
                </div>

                <div id="niubiz-container" class="flex justify-center min-h-[52px] w-full"></div>
            </div>
        </div>

        <div x-show="mostrarTerminos" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="mostrarTerminos = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden">
                <div class="px-5 py-4 border-b flex justify-between items-center">
                    <h2 class="font-bold text-slate-900">Términos y condiciones</h2>
                    <button type="button" @click="mostrarTerminos = false"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="px-5 py-4 overflow-y-auto text-sm text-slate-600 space-y-2">
                    <p>Al confirmar y pagar acepta las condiciones del servicio de reserva de canchas de la Municipalidad de La Molina.</p>
                    <p>La reserva se confirma tras acreditarse el pago. Debe presentar documento de identidad al usar la cancha.</p>
                </div>
                <div class="px-5 py-4 border-t flex justify-end">
                    <button type="button" @click="mostrarTerminos = false; aceptaTerminos = true"
                        class="px-5 py-2.5 rounded-xl bg-emerald-700 text-white text-sm font-semibold">Entendido, acepto</button>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
        function pagoPortal() {
            const flashError = @json(session('error'));
            const urlRegistrar = @json(route('reservar.registrar'));
            return {
                aceptaTerminos: false, mostrarTerminos: false, pagando: false,
                error: flashError || '', exito: '', sessionKey: null, monto: 0, reservaId: null,
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
                        sede: p.get('sede')||'', club: p.get('club')||'', cancha: p.get('cancha')||'',
                        cancha_id: p.get('cancha_id')||'', fecha: p.get('fecha')||'', hora: p.get('hora')||'',
                        duracion: parseInt(p.get('duracion')||'60',10), precio: parseFloat(p.get('precio')||'0'),
                        deporte: p.get('deporte')||'', deporte_id: p.get('deporte_id')||'',
                        documento: p.get('documento')||'', email: p.get('email')||'',
                    };
                },
                leerPayload() {
                    let stored = null;
                    try { stored = JSON.parse(sessionStorage.getItem('reserva_pago') || 'null'); } catch (e) {}
                    return Object.assign({}, this.payloadDesdeUrl(), stored || {});
                },
                reiniciarBoton() {
                    this.sessionKey = null;
                    document.getElementById('niubiz-container').innerHTML = '';
                },
                montarBotonNiubiz(detail) {
                    this.$nextTick(() => setTimeout(() => {
                        const container = document.getElementById('niubiz-container');
                        container.innerHTML = '';
                        const form = document.createElement('form');
                        form.action = detail.verifyUrl; form.method = 'POST';
                        const script = document.createElement('script');
                        script.src = (detail.buttonUrl || this.buttonUrl) + '?t=' + Date.now();
                        script.setAttribute('data-sessiontoken', detail.sessionKey);
                        script.setAttribute('data-channel', 'web');
                        script.setAttribute('data-merchantid', String(detail.merchantId || this.merchantId));
                        script.setAttribute('data-purchasenumber', String(detail.purchaseNumber));
                        script.setAttribute('data-amount', Number(detail.amount).toFixed(2));
                        script.setAttribute('data-expirationminutes', '20');
                        script.setAttribute('data-timeouturl', detail.timeoutUrl || window.location.href);
                        script.setAttribute('data-merchantlogo', this.merchantLogo);
                        script.setAttribute('data-formbuttoncolor', '#047857');
                        form.appendChild(script);
                        container.appendChild(form);
                    }, 50));
                },
                async iniciarPago() {
                    if (!this.aceptaTerminos || this.pagando) return;
                    this.pagando = true; this.error = ''; this.exito = '';
                    const payload = this.leerPayload();
                    payload.acepto_terminos = true;
                    if (this.reservaId) payload.reserva_id = this.reservaId;
                    try {
                        const res = await fetch(urlRegistrar, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json', 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Pago-Query': window.location.search ? window.location.search.slice(1) : '',
                            },
                            body: JSON.stringify(payload),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || !data.ok) {
                            const first = data.errors ? Object.values(data.errors)[0] : null;
                            this.error = Array.isArray(first) ? first[0] : (data.mensaje || 'No se pudo iniciar el pago.');
                            return;
                        }
                        if (data.sin_pasarela) {
                            sessionStorage.removeItem('reserva_pago');
                            this.exito = data.mensaje || 'Reserva confirmada.';
                            if (data.redirect) setTimeout(() => { window.location.href = data.redirect; }, 1000);
                            return;
                        }
                        this.reservaId = data.reserva_id;
                        this.sessionKey = data.sessionKey;
                        this.monto = Number(data.amount);
                        const stored = this.leerPayload();
                        stored.reserva_id = data.reserva_id;
                        sessionStorage.setItem('reserva_pago', JSON.stringify(stored));
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
        @endpush
    </div>
</x-portal-reserva-shell>
