<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Response;

$router->get('/', function () {
    return (new Response())->json(['message' => 'Welcome to PHP Framework']);
});
