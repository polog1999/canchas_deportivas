<?php

namespace App\Mail;

use App\Models\Reprogramacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaReprogramadaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $detalle
     * @param  array{content: string, filename: string}|null  $pdfAdjunto
     */
    public function __construct(
        public Reprogramacion $reprogramacion,
        public array $detalle,
        public ?array $pdfAdjunto = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu reserva fue reprogramada — Municipalidad de La Molina',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-reprogramada',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfAdjunto === null) {
            return [];
        }

        $contenido = $this->pdfAdjunto['content'];
        $nombre = $this->pdfAdjunto['filename'];

        return [
            Attachment::fromData(fn () => $contenido, $nombre)
                ->withMime('application/pdf'),
        ];
    }
}
