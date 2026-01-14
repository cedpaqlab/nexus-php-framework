<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Config;

$container = require __DIR__ . '/../bootstrap/app.php';
$request = new Request();
$router = new Router();
$router->setContainer($container);

$securityConfig = Config::get('security');
foreach ($securityConfig['headers'] as $header => $value) {
    header("{$header}: {$value}");
}

require __DIR__ . '/../routes/web.php';

$response = $router->dispatch($request);
$response->send();
