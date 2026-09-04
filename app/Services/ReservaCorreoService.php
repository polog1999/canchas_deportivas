<?php

namespace App\Services;

use App\Mail\ReservaPagoConfirmadoMail;
use App\Mail\ReservaReprogramadaMail;
use App\Models\Pago;
use App\Models\Reprogramacion;
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
     * @return array{enviado: bool, destino: string|null, motivo: string|null}
     */
    public function enviarConfirmacionPago(
        Reserva $reserva,
        array $meta = [],
        bool $usuarioNuevo = false,
        ?string $usuarioLogin = null,
        ?string $clavePlana = null,
        ?Pago $pago = null,
    ): array {
        $reserva->loadMissing(['usuario.perfil', 'cancha.sede']);

        $usuario = $reserva->usuario;
        $correo = trim((string) ($meta['email'] ?? $usuario?->correo_electronico ?? ''));

        if ($correo === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Log::warning('ReservaCorreo: sin correo válido para enviar confirmación', [
                'reserva_id' => $reserva->id,
                'correo_recibido' => $correo,
            ]);

            return [
                'enviado' => false,
                'destino' => $correo !== '' ? $correo : null,
                'motivo' => $correo === ''
                    ? 'el titular no tiene correo registrado'
                    : 'el correo del titular no tiene un formato válido',
            ];
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

            return ['enviado' => true, 'destino' => $correo, 'motivo' => null];
        } catch (\Throwable $e) {
            Log::error('ReservaCorreo: error al enviar correo', [
                'reserva_id' => $reserva->id,
                'destino' => $correo,
                'mailer' => $mailer,
                'smtp_host' => $smtpHost,
                'error' => $e->getMessage(),
            ]);

            return ['enviado' => false, 'destino' => $correo, 'motivo' => $e->getMessage()];
        }
    }

    /**
     * Avisa al cliente del cambio de horario con la nueva constancia adjunta.
     */
    public function enviarReprogramacion(Reprogramacion $reprogramacion): void
    {
        $reprogramacion->loadMissing([
            'reserva.usuario.perfil',
            'canchaAnterior',
            'canchaNueva.sede',
        ]);

        $reserva = $reprogramacion->reserva;
        $usuario = $reserva?->usuario;
        $correo = trim((string) ($usuario?->correo_electronico ?? ''));

        if ($correo === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Log::warning('ReservaCorreo: sin correo válido para avisar la reprogramación', [
                'reprogramacion_id' => $reprogramacion->id,
                'reserva_id' => $reserva?->id,
            ]);

            return;
        }

        $mailer = $this->mailConfig->mailerActivo();

        $detalle = [
            'titular' => $usuario?->nombreCompleto(),
            'voucher' => $reserva?->referencia_pago,
            'sede' => $reprogramacion->canchaNueva?->sede?->nombre,
            'cancha' => $reprogramacion->canchaNueva?->nombre ?? '—',
            'fecha' => $reprogramacion->hora_inicio_nueva->format('d/m/Y'),
            'hora_inicio' => $reprogramacion->hora_inicio_nueva->format('H:i'),
            'hora_fin' => $reprogramacion->hora_fin_nueva->format('H:i'),
            'cancha_anterior' => $reprogramacion->canchaAnterior?->nombre ?? '—',
            'turno_anterior' => $reprogramacion->hora_inicio_anterior->format('d/m/Y H:i')
                .' a '.$reprogramacion->hora_fin_anterior->format('H:i').' hs',
            'motivo' => $reprogramacion->motivo,
            'monto' => (float) $reprogramacion->monto_validado,
        ];

        try {
            $pdfAdjunto = $this->constanciaPagoPdf->adjuntoDesdeReprogramacion($reprogramacion);

            Mail::mailer($mailer)->to($correo)->send(new ReservaReprogramadaMail(
                reprogramacion: $reprogramacion,
                detalle: $detalle,
                pdfAdjunto: $pdfAdjunto,
            ));

            Log::info('ReservaCorreo: aviso de reprogramación enviado', [
                'reprogramacion_id' => $reprogramacion->id,
                'reserva_id' => $reserva?->id,
                'destino' => $correo,
                'mailer' => $mailer,
            ]);
        } catch (\Throwable $e) {
            Log::error('ReservaCorreo: error al avisar la reprogramación', [
                'reprogramacion_id' => $reprogramacion->id,
                'destino' => $correo,
                'mailer' => $mailer,
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
