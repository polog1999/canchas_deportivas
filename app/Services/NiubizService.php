<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NiubizService
{
    private function apiUrl(): string
    {
        return rtrim(trim((string) config('niubiz.api_url')), '/');
    }

    private function merchantId(): string
    {
        return trim((string) config('niubiz.merchant_id'));
    }

    private function apiUser(): string
    {
        return trim((string) config('niubiz.api_user'));
    }

    private function apiPassword(): string
    {
        return trim((string) config('niubiz.api_password'));
    }

    /**
     * PASO 1: Token de seguridad.
     */
    private function getSecurityToken(): ?string
    {
        $securityUrl = $this->apiUrl().'/api.security/v1/security';

        $user = $this->apiUser();
        $password = $this->apiPassword();

        Log::channel('niubiz')->info('STEP 1: Solicitando Security Token...', [
            'url' => $securityUrl,
            'user_len' => strlen($user),
            'pass_len' => strlen($password),
            'merchant_len' => strlen($this->merchantId()),
        ]);

        if ($user === '' || $password === '') {
            Log::channel('niubiz')->error('STEP 1: Credenciales Niubiz vacías en config/.env');

            return null;
        }

        // withBasicAuth evita problemas de encoding con caracteres especiales (@, _)
        $response = Http::withBasicAuth($user, $password)
            ->withHeaders([
                'Accept' => 'text/plain',
            ])
            ->timeout(30)
            ->get($securityUrl);

        if (! $response->successful()) {
            Log::channel('niubiz')->error('STEP 1: Falla al obtener token de seguridad', [
                'status' => $response->status(),
                'url' => $securityUrl,
                'body' => $response->body(),
            ]);

            return null;
        }

        $token = trim((string) $response->body());
        if ($token === '') {
            Log::channel('niubiz')->error('STEP 1: Token vacío en respuesta');

            return null;
        }

        Log::channel('niubiz')->info('STEP 1: Security Token obtenido.', [
            'token_len' => strlen($token),
        ]);

        return $token;
    }

    /**
     * PASO 2: Session token para el botón de pago.
     */
    public function createSessionToken(Reserva $reserva, float $finalAmount, ?Usuario $usuario = null): ?string
    {
        $usuario = $usuario ?? $reserva->usuario;
        $purchaseNumber = (string) $reserva->id;

        Log::channel('niubiz')->info("[Session] Iniciando sesión para compra #{$purchaseNumber}");

        $securityToken = $this->getSecurityToken();
        if (! $securityToken) {
            return null;
        }

        $sessionUrl = $this->apiUrl().'/api.ecommerce/v2/ecommerce/token/session/'.$this->merchantId();

        $email = $usuario?->correo_electronico ?: 'reservas@munilamolina.gob.pe';
        $diasRegistro = 1;
        if ($usuario?->creado_en) {
            $diasRegistro = max(1, (int) $usuario->creado_en->diffInDays(now()));
        }

        $requestBody = [
            'channel' => 'web',
            'amount' => round($finalAmount, 2),
            'antifraud' => [
                'clientIp' => request()->ip() ?? '127.0.0.1',
                'merchantDefineData' => [
                    'MDD4' => $email,
                    'MDD32' => (string) ($usuario?->id ?? '0'),
                    'MDD75' => $usuario ? 'Registrado' : 'Invitado',
                    'MDD77' => $diasRegistro,
                ],
            ],
            'dataMap' => [
                'cardholderCity' => 'Lima',
                'cardholderCountry' => 'PE',
                'cardholderAddress' => 'Av. Ricardo Elías Aparicio 740, La Molina 15026',
                'cardholderPostalCode' => '15026',
                'cardholderState' => 'LIM',
                'cardholderPhoneNumber' => '017544000',
            ],
        ];

        Log::channel('niubiz')->info('STEP 2: Creando Session Token', [
            'purchaseNumber' => $purchaseNumber,
            'amount_sent' => $requestBody['amount'],
        ]);

        $response = Http::withHeaders([
            'Authorization' => $securityToken,
            'Content-Type' => 'application/json',
        ])->post($sessionUrl, $requestBody);

        if (! $response->successful()) {
            Log::channel('niubiz')->error('STEP 2: Error al crear token de sesión', [
                'status' => $response->status(),
                'request_body' => $requestBody,
                'response_body' => $response->json() ?? $response->body(),
            ]);

            return null;
        }

        $token = $response->json('sessionKey');

        Log::channel('niubiz')->info('STEP 2: Session Token creado', [
            'token_preview' => $token ? substr((string) $token, 0, 10) : null,
        ]);

        return $token;
    }

    /**
     * PASO 4: Autorización de la transacción.
     *
     * @return array<string, mixed>|null
     */
    public function authorizeTransaction(string $transactionToken, Reserva $reserva, float $finalAmount): ?array
    {
        $purchaseNumber = (string) $reserva->id;

        Log::channel('niubiz')->info("[Auth] Autorizando compra #{$purchaseNumber}");

        $securityToken = $this->getSecurityToken();
        if (! $securityToken) {
            return null;
        }

        $authUrl = $this->apiUrl().'/api.authorization/v3/authorization/ecommerce/'.$this->merchantId();

        $requestBody = [
            'channel' => 'web',
            'captureType' => 'manual',
            'countable' => true,
            'order' => [
                'tokenId' => $transactionToken,
                'purchaseNumber' => $purchaseNumber,
                'amount' => round($finalAmount, 2),
                'currency' => 'PEN',
            ],
            'dataMap' => [
                'urlAddress' => config('app.url'),
                'serviceLocationCityName' => 'Lima',
                'serviceLocationCountrySubdivisionCode' => 'LIM',
                'serviceLocationCountryCode' => 'PER',
                'serviceLocationPostalCode' => '15024',
            ],
        ];

        Log::channel('niubiz')->info('STEP 4: Autorizando', [
            'purchaseNumber' => $purchaseNumber,
            'amount_to_authorize' => $requestBody['order']['amount'],
        ]);

        $response = Http::withHeaders([
            'Authorization' => $securityToken,
            'Content-Type' => 'application/json',
        ])->post($authUrl, $requestBody);

        $jsonResponse = $response->json();

        Log::channel('niubiz')->info('STEP 4: Respuesta de autorización', [
            'status_code' => $response->status(),
            'niubiz_response' => $jsonResponse,
        ]);

        if ($response->serverError()) {
            Log::channel('niubiz')->error('STEP 4: Error de servidor Niubiz', [
                'body' => $response->body(),
            ]);

            return null;
        }

        return is_array($jsonResponse) ? $jsonResponse : null;
    }
}
