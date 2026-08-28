<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$url = rtrim((string) config('niubiz.api_url'), '/').'/api.security/v1/security';
$user = trim((string) config('niubiz.api_user'));
$pass = trim((string) config('niubiz.api_password'));

$response = Illuminate\Support\Facades\Http::withBasicAuth($user, $pass)
    ->withHeaders(['Accept' => 'text/plain'])
    ->timeout(30)
    ->get($url);

$body = (string) $response->body();
echo 'http='.$response->status()
    .' ok='.($response->successful() ? 'yes' : 'no')
    .' body_len='.strlen($body)
    .' looks_jwt='.(str_starts_with($body, 'eyJ') ? 'yes' : 'no')
    .PHP_EOL;
