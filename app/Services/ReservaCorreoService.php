<?php

namespace App\Services;

use App\Mail\ReservaPagoConfirmadoMail;
use App\Models\Reserva;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservaCorreoService
{
    public function __construct(
        private readonly MailConfigService $mailConfig,
        private readonly ReservaVoucherService $voucherService,
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public function enviarConfirmacionPago(
        Reserva $reserva,
        array $meta = [],
        bool $usuarioNuevo = false,
        ?string $usuarioLogin = null,
        ?string $clavePlana = null,
    ): void {
        $reserva->loadMissing(['usuario.perfil', 'cancha.sede']);

        $usuario = $reserva->usuario;
        $correo = trim((string) ($meta['email'] ?? $usuario?->correo_electronico ?? ''));

        if ($correo === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Log::warning('ReservaCorreo: sin correo válido para enviar confirmación', [
                'reserva_id' => $reserva->id,
            ]);

            return;
        }

        $mailer = $this->mailConfig->mailerActivo();
        $smtpHost = (string) config('mail.mailers.smtp.host', '');

        $voucher = $this->voucherService->datosDesdeReserva($reserva, $meta);

        $detalle = [
            'voucher' => $voucher['codigo_voucher'] ?? $reserva->referencia_pago,
            'monto' => (float) $voucher['monto'],
            'club' => $voucher['sede'] ?? null,
            'cancha' => $voucher['cancha'] ?? null,
            'deporte' => ($voucher['deporte'] ?? null) !== '—' ? $voucher['deporte'] : null,
            'fecha' => $voucher['fecha_turno'] ?? null,
            'hora_inicio' => null,
            'hora_fin' => null,
            'titular' => $voucher['titular'] ?? $usuario?->nombreCompleto(),
        ];

        if (! empty($voucher['horario']) && $voucher['horario'] !== '—') {
            if (preg_match('/^(\d{2}:\d{2})\s+a\s+(\d{2}:\d{2})/', $voucher['horario'], $matches)) {
                $detalle['hora_inicio'] = $matches[1];
                $detalle['hora_fin'] = $matches[2];
            }
        }

        $pdfContenido = null;
        $pdfNombre = null;

        try {
            $pdfContenido = $this->voucherService->contenidoPdf($voucher);
            $pdfNombre = $this->voucherService->nombreArchivo($voucher);
        } catch (\Throwable $e) {
            Log::error('ReservaCorreo: no se pudo generar el PDF del voucher', [
                'reserva_id' => $reserva->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            Mail::mailer($mailer)->to($correo)->send(new ReservaPagoConfirmadoMail(
                reserva: $reserva,
                detalle: $detalle,
                usuarioNuevo: $usuarioNuevo,
                usuarioLogin: $usuarioLogin ?? $usuario?->usuario,
                clavePlana: $clavePlana,
                pdfContenido: $pdfContenido,
                pdfNombre: $pdfNombre,
            ));

            Log::info('ReservaCorreo: correo enviado', [
                'reserva_id' => $reserva->id,
                'usuario_nuevo' => $usuarioNuevo,
                'destino' => $correo,
                'mailer' => $mailer,
                'smtp_host' => $smtpHost,
            ]);
        } catch (\Throwable $e) {
            Log::error('ReservaCorreo: error al enviar correo', [
                'reserva_id' => $reserva->id,
                'destino' => $correo,
                'mailer' => $mailer,
                'smtp_host' => $smtpHost,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
