<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Controllers\HomeController;

$router->get('/', [HomeController::class, 'index']);
