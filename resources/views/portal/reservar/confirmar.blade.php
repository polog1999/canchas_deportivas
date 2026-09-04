@php
    $urlTurno = route('portal.reservar.turno');
    $urlPago = route('portal.reservar.pago');
@endphp

<x-portal-reserva-shell title="Confirmar reserva" :step="3" :alpine="true">
    <div x-data="confirmarPortal()">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Paso 3 de 3</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Revisa y confirma tu reserva</h1>
            <p class="text-sm text-slate-500 mt-1">Reserva con tu sesión activa en el portal municipal.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="lg:col-span-3 space-y-5">
                <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                        <h2 class="font-bold text-slate-900">Datos del titular</h2>
                    </div>

                    <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-800">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        Tus datos se completan con tu cuenta activa.
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">DNI / documento</label>
                            <input type="text" x-model="form.documento" maxlength="15" inputmode="numeric"
                                :disabled="!documentoEditable"
                                :class="documentoEditable ? 'border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500' : 'border-slate-200 bg-slate-100 text-slate-600'"
                                class="w-full px-3 py-2.5 rounded-xl border text-sm"
                                placeholder="12345678">
                            <p x-show="documentoEditable" class="text-[11px] text-amber-700 mt-1.5">
                                Tu cuenta no tiene DNI registrado. Ingrésalo para continuar al pago.
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nombres</label>
                            <input type="text" x-model="form.nombres" disabled class="w-full px-3 py-2.5 rounded-xl border bg-slate-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Apellido paterno</label>
                            <input type="text" x-model="form.apellido_paterno" disabled class="w-full px-3 py-2.5 rounded-xl border bg-slate-100 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Apellido materno</label>
                            <input type="text" x-model="form.apellido_materno" disabled class="w-full px-3 py-2.5 rounded-xl border bg-slate-100 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Correo electrónico</label>
                            <input type="email" x-model="form.email"
                                class="w-full px-3 py-2.5 rounded-xl border bg-slate-100 text-sm" disabled>
                            <p class="text-[11px] text-slate-400 mt-1">Recibirás el voucher en este correo.</p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <a :href="urlVolverHorarios" class="inline-flex justify-center px-5 py-3 rounded-xl border bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cambiar horario
                    </a>
                    <button type="button" @click="confirmar()" :disabled="!puedeConfirmar || confirmando"
                        class="inline-flex justify-center items-center gap-2 px-8 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-40 text-white text-sm font-bold">
                        <i x-show="confirmando" class="fa-solid fa-spinner animate-spin"></i>
                        Continuar al pago
                    </button>
                </div>
                <p class="text-xs text-red-600 text-right" x-show="errorConfirmacion" x-text="errorConfirmacion" x-cloak></p>
            </div>

            <aside class="lg:col-span-2">
                <div class="lg:sticky lg:top-6 rounded-2xl overflow-hidden shadow-md border">
                    <div class="h-28 bg-cover bg-center relative" :style="'background-image:url(' + reserva.imagen + ')'">
                        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/90 to-transparent"></div>
                        <div class="absolute bottom-3 left-4 text-white">
                            <p class="text-[10px] uppercase text-emerald-200" x-text="reserva.deporte"></p>
                            <h3 class="font-bold text-lg" x-text="reserva.club"></h3>
                        </div>
                    </div>
                    <div class="bg-emerald-900 text-white p-5 space-y-2 text-sm">
                        <p class="text-emerald-200 text-xs"><i class="fa-solid fa-location-dot mr-1"></i><span x-text="reserva.direccion"></span></p>
                        <div class="flex justify-between"><span class="text-emerald-200 text-xs">Fecha</span><span x-text="reserva.fechaLabel"></span></div>
                        <div class="flex justify-between"><span class="text-emerald-200 text-xs">Turno</span><span x-text="reserva.turno"></span></div>
                        <div class="flex justify-between"><span class="text-emerald-200 text-xs">Cancha</span><span x-text="reserva.cancha"></span></div>
                        <div class="border-t border-white/10 pt-2 flex justify-between items-end">
                            <span class="text-xs text-emerald-200">Total</span>
                            <span class="text-2xl font-bold" x-text="'S/ ' + reserva.precio"></span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        @push('scripts')
        <script>
        function confirmarPortal() {
            const params = new URLSearchParams(window.location.search);
            const sedeId = params.get('sede') || '';
            const deporteId = params.get('deporte_id') || '';
            const fecha = params.get('fecha') || '';
            const hora = params.get('hora') || '17:00';
            const duracion = parseInt(params.get('duracion') || '60', 10);
            const volverParams = new URLSearchParams();
            if (sedeId) volverParams.set('sede', sedeId);
            if (deporteId) volverParams.set('deporte_id', deporteId);
            if (fecha) volverParams.set('fecha', fecha);
            const urlVolverHorarios = sedeId ? (@json($urlTurno) + '?' + volverParams.toString()) : @json(route('portal.reservar.index'));
            const urlPago = @json($urlPago);
            const [h, m] = hora.split(':').map(Number);
            const finMin = h * 60 + (m || 0) + duracion;
            const fh = String(Math.floor(finMin / 60)).padStart(2, '0');
            const fm = String(finMin % 60).padStart(2, '0');
            const fechaDate = new Date(fecha + 'T12:00:00');
            const usuario = @json($usuarioPortal);

            return {
                form: {
                    documento: usuario?.documento || '',
                    nombres: usuario?.nombres || '',
                    apellido_paterno: usuario?.apellido_paterno || '',
                    apellido_materno: usuario?.apellido_materno || '',
                    email: usuario?.email || '',
                },
                documentoEditable: usuario?.documento_editable ?? true,
                confirmando: false,
                errorConfirmacion: '',
                urlVolverHorarios,
                reserva: {
                    club: params.get('club') || '',
                    cancha: params.get('cancha') || '',
                    fechaLabel: fechaDate.toLocaleDateString('es-PE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' }),
                    turno: `${hora} a ${fh}:${fm} hs`,
                    precio: params.get('precio') || '0',
                    detalle: params.get('detalle') || '',
                    direccion: params.get('direccion') || '',
                    deporte: params.get('deporte') || '',
                    imagen: params.get('imagen') || 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=800&q=80',
                },
                get puedeConfirmar() {
                    const doc = this.form.documento.replace(/\D/g, '');
                    const email = this.form.email.trim();
                    return doc.length >= 8 && email.includes('@') && email.includes('.');
                },
                confirmar() {
                    if (!this.puedeConfirmar || this.confirmando) return;
                    if (this.form.documento.replace(/\D/g, '').length < 8) {
                        this.errorConfirmacion = 'Ingresa un DNI válido (mínimo 8 dígitos).';
                        return;
                    }
                    this.confirmando = true;
                    this.errorConfirmacion = '';
                    const payload = {
                        sede: params.get('sede') || '', club: params.get('club') || '',
                        direccion: params.get('direccion') || '', imagen: params.get('imagen') || '',
                        cancha: params.get('cancha') || '', cancha_id: params.get('cancha_id') || '',
                        detalle: params.get('detalle') || '', fecha, hora,
                        duracion, precio: parseFloat(params.get('precio') || '0'),
                        deporte: params.get('deporte') || '', deporte_id: deporteId,
                        tusne_id: params.get('tusne_id') || '',
                        documento: this.form.documento.replace(/\D/g, ''),
                        nombres: this.form.nombres, apellido_paterno: this.form.apellido_paterno,
                        apellido_materno: this.form.apellido_materno, email: this.form.email.trim(),
                    };
                    try { sessionStorage.setItem('reserva_pago', JSON.stringify(payload)); } catch (e) {}
                    window.location.href = urlPago + window.location.search;
                },
            };
        }
        </script>
        @endpush
    </div>
</x-portal-reserva-shell>
