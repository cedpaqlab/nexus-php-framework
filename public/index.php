<?php

declare(strict_types=1);

$container = require __DIR__ . '/../bootstrap/app.php';

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;

$request = $container->get(Request::class);
$router = $container->get(Router::class);

require __DIR__ . '/../routes/web.php';

$response = $router->dispatch($request);
$response->send();
