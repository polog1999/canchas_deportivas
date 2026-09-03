<?php

namespace App\Services;

use App\Mail\ReservaPagoConfirmadoMail;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservaCorreoService
{
    public function __construct(
        private readonly MailConfigService $mailConfig,
        private readonly ConstanciaPagoPdfService $constanciaPagoPdf,
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
        ?Pago $pago = null,
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

        $detalle = [
            'voucher' => $reserva->referencia_pago,
            'monto' => (float) $reserva->precio_total,
            'club' => $meta['club'] ?? $reserva->cancha?->sede?->nombre,
            'cancha' => $meta['cancha'] ?? $reserva->cancha?->nombre,
            'deporte' => $meta['deporte'] ?? null,
            'fecha' => $reserva->hora_inicio?->format('d/m/Y'),
            'hora_inicio' => $reserva->hora_inicio?->format('H:i'),
            'hora_fin' => $reserva->hora_fin?->format('H:i'),
            'titular' => $usuario?->nombreCompleto(),
        ];

        $identificadorAcceso = $this->identificadorAcceso($usuario, $usuarioLogin, $correo);

        try {
            $pdfAdjunto = $pago
                ? $this->constanciaPagoPdf->adjuntoDesdePago($pago)
                : $this->constanciaPagoPdf->adjuntoDesdeReserva($reserva);

            Mail::mailer($mailer)->to($correo)->send(new ReservaPagoConfirmadoMail(
                reserva: $reserva,
                detalle: $detalle,
                usuarioNuevo: $usuarioNuevo,
                usuarioLogin: $identificadorAcceso,
                clavePlana: $clavePlana,
                pdfAdjunto: $pdfAdjunto,
            ));

            Log::info('ReservaCorreo: correo enviado', [
                'reserva_id' => $reserva->id,
                'usuario_nuevo' => $usuarioNuevo,
                'destino' => $correo,
                'mailer' => $mailer,
                'smtp_host' => $smtpHost,
                'pdf_adjunto' => $pdfAdjunto !== null,
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

    private function identificadorAcceso(?Usuario $usuario, ?string $usuarioLogin, string $correoDestino): string
    {
        $correoUsuario = trim((string) ($usuario?->correo_electronico ?? ''));

        if ($correoUsuario !== '' && filter_var($correoUsuario, FILTER_VALIDATE_EMAIL)) {
            return $correoUsuario;
        }

        if (filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
            return $correoDestino;
        }

        return trim((string) ($usuarioLogin ?? $usuario?->usuario ?? '—'));
    }
}
