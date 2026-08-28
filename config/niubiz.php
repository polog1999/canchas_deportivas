<?php

/**
 * Credenciales sandbox públicas de Niubiz (documentación de integración).
 * En producción sobrescribe siempre con variables reales en .env.
 */
$niubiz = static function (string $key, string $default = '') {
    $value = env($key);
    if ($value === null || $value === false) {
        return $default;
    }

    $value = trim((string) $value);

    return $value === '' ? $default : $value;
};

return [
    'merchant_id' => $niubiz('NIUBIZ_MERCHANT_ID', '456879852'),
    'api_user' => $niubiz('NIUBIZ_USER', 'integraciones@niubiz.com.pe'),
    'api_password' => $niubiz('NIUBIZ_PASSWORD', '_7z3@8fF'),
    'antifraud_merchant_id' => $niubiz('NIUBIZ_ANTIFRAUD_MERCHANT_ID', ''),
    'api_url' => rtrim($niubiz('NIUBIZ_API_URL', 'https://apisandbox.vnforappstest.com'), '/'),
    'button_url' => $niubiz(
        'NIUBIZ_BUTTON_URL',
        'https://static-content-qas.vnforapps.com/env/sandbox/js/checkout.js'
    ),
];
