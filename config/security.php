<?php

declare(strict_types=1);

return [
    'csrf' => [
        'enabled' => filter_var($_ENV['SECURITY_CSRF_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'token_lifetime' => (int) ($_ENV['SECURITY_CSRF_TOKEN_LIFETIME'] ?? 3600),
        'token_name' => '_csrf_token',
    ],
    'session' => [
        'lifetime' => (int) ($_ENV['SECURITY_SESSION_LIFETIME'] ?? 7200),
        'cookie_name' => 'PHPSESSID',
        'cookie_httponly' => true,
        'cookie_secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
        'cookie_samesite' => 'Strict',
    ],
    'password' => [
        'algo' => $_ENV['SECURITY_PASSWORD_ALGO'] ?? 'argon2id',
        'options' => [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ],
    ],
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'",
    ],
];
