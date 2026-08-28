<?php

namespace App\Mail;

use Illuminate\Mail\MailManager;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class CustomMailManager extends MailManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config): EsmtpTransport
    {
        $transport = parent::configureSmtpTransport($transport, $config);

        $stream = $transport->getStream();

        if ($stream instanceof SocketStream && ! empty($config['stream']['ssl'])) {
            $stream->setStreamOptions(['ssl' => $config['stream']['ssl']]);
        }

        return $transport;
    }
}
