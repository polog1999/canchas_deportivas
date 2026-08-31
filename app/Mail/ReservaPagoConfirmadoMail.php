<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaPagoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $detalle
     */
    public function __construct(
        public Reserva $reserva,
        public array $detalle,
        public bool $usuarioNuevo = false,
        public ?string $usuarioLogin = null,
        public ?string $clavePlana = null,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = $this->usuarioNuevo
            ? 'Reserva confirmada y acceso al sistema — Municipalidad de La Molina'
            : 'Voucher de pago — Reserva de cancha — Municipalidad de La Molina';

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-pago-confirmado',
        );
    }
}
