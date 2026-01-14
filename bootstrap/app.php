<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/../config/loader.php';
require_once __DIR__ . '/container.php';
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

$container->singleton(\App\Repositories\Database\Connection::class, function () {
    return \App\Repositories\Database\Connection::getInstance();
});

$container->singleton(\App\Services\Security\CsrfService::class, function () {
    return new \App\Services\Security\CsrfService();
});

$container->singleton(\App\Services\Security\HashService::class, function () {
    return new \App\Services\Security\HashService();
});

$container->singleton(\App\Services\Security\Validator::class, function () {
    return new \App\Services\Security\Validator();
});

$container->singleton(\App\Services\Logger\Logger::class, function () {
    return new \App\Services\Logger\Logger();
});

$container->singleton(\App\Services\View\ViewRenderer::class, function () {
    return new \App\Services\View\ViewRenderer();
});

$container->singleton(\App\Http\Request::class, function () {
    return new \App\Http\Request();
});

$container->singleton(\App\Http\Router::class, function (Container $container) {
    $router = new \App\Http\Router();
    $router->setContainer($container);
    return $router;
});

return $container;
