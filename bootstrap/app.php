<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/../config/loader.php';
require_once __DIR__ . '/container.php';
require_once __DIR__ . '/propel.php';
require_once __DIR__ . '/error_handler.php';

use App\Services\Helpers\PathHelper;

PathHelper::setBasePath(dirname(__DIR__));

date_default_timezone_set(Config::get('app.timezone', 'UTC'));

if (session_status() === PHP_SESSION_NONE) {
    $sessionConfig = Config::get('security.session');

    session_set_cookie_params([
        'lifetime' => $sessionConfig['lifetime'],
        'path' => '/',
        'domain' => '',
        'secure' => $sessionConfig['cookie_secure'],
        'httponly' => $sessionConfig['cookie_httponly'],
        'samesite' => $sessionConfig['cookie_samesite'],
    ]);

    session_start();
}

$container = new Container();

// Register service providers
$providers = [
    \App\Providers\AppServiceProvider::class,
];

foreach ($providers as $provider) {
    $providerInstance = new $provider($container);
    $providerInstance->register();
}

if (function_exists('setErrorHandlerContainer')) {
    setErrorHandlerContainer($container);
}

return $container;
