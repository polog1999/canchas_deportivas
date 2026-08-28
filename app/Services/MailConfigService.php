<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailConfigService
{
    public function aplicarDesdeEnv(): void
    {
        $host = $this->valor('MAIL_HOST');
        if ($host === null || $host === '' || in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return;
        }

        $fromAddress = $this->valor('MAIL_FROM_ADDRESS', 'recoverypass@munimolina.gob.pe');
        $fromName = $this->valor('MAIL_FROM_NAME', $this->valor('APP_NAME', 'Municipalidad de La Molina'));

        config([
            'mail.default' => $this->valor('MAIL_MAILER', 'smtp'),
            'mail.from.address' => trim($fromAddress, '"'),
            'mail.from.name' => trim($fromName, '"'),
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) ($this->valor('MAIL_PORT', '587') ?? 587),
            'mail.mailers.smtp.username' => $this->valor('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => $this->valor('MAIL_PASSWORD'),
            'mail.mailers.smtp.encryption' => $this->valor('MAIL_ENCRYPTION', 'tls'),
            'mail.mailers.smtp.verify_peer' => filter_var($this->valor('MAIL_VERIFY_PEER', 'false'), FILTER_VALIDATE_BOOL),
            'mail.mailers.smtp.auto_tls' => filter_var($this->valor('MAIL_AUTO_TLS', 'true'), FILTER_VALIDATE_BOOL),
            'mail.mailers.smtp.stream.ssl' => [
                'verify_peer' => filter_var($this->valor('MAIL_VERIFY_PEER', 'false'), FILTER_VALIDATE_BOOL),
                'verify_peer_name' => filter_var($this->valor('MAIL_VERIFY_PEER', 'false'), FILTER_VALIDATE_BOOL),
                'allow_self_signed' => filter_var($this->valor('MAIL_ALLOW_SELF_SIGNED', 'true'), FILTER_VALIDATE_BOOL),
            ],
        ]);

        Mail::purge('smtp');
    }

    public function mailerActivo(): string
    {
        $this->aplicarDesdeEnv();

        $host = trim((string) config('mail.mailers.smtp.host', ''));
        if ($host !== '' && ! in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return 'smtp';
        }

        return (string) config('mail.default', 'log');
    }

    private function valor(string $key, ?string $default = null): ?string
    {
        $fromEnv = env($key);
        if (is_string($fromEnv) && $fromEnv !== '') {
            return $fromEnv;
        }

        $fromGetenv = getenv($key);
        if (is_string($fromGetenv) && $fromGetenv !== '') {
            return $fromGetenv;
        }

        static $archivo = null;
        if ($archivo === null) {
            $archivo = $this->leerArchivoEnv();
        }

        if (array_key_exists($key, $archivo)) {
            $v = trim((string) $archivo[$key]);
            if ($v !== '') {
                return trim($v, '"\'');
            }
        }

        return $default;
    }

    /**
     * @return array<string, string>
     */
    private function leerArchivoEnv(): array
    {
        $path = base_path('.env');
        if (! is_readable($path)) {
            return [];
        }

        $vars = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v);
        }

        return $vars;
    }
}
