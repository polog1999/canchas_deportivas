<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$to = $argv[1] ?? null;
if (! $to) {
    echo "usage: php mail_probe.php email@example.com\n";
    exit(1);
}

try {
    Illuminate\Support\Facades\Mail::raw('Prueba SMTP canchas deportivas', function ($message) use ($to) {
        $message->to($to)->subject('Prueba SMTP — Municipalidad de La Molina');
    });
    echo "sent_ok\n";
} catch (Throwable $e) {
    echo 'sent_fail: '.$e->getMessage()."\n";
    exit(1);
}
