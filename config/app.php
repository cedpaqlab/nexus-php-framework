<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'PHP_Framework',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['TIMEZONE'] ?? 'UTC',
    'locale' => $_ENV['LOCALE'] ?? 'en_US',
];
