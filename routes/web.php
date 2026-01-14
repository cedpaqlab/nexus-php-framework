<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Middlewares\SuperAdminMiddleware;
use App\Http\Middlewares\AuthMiddleware;
use App\Http\Middlewares\CsrfMiddleware;

$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

$router->group('/admin', function (Router $router) {
    $router->get('/', [AdminController::class, 'dashboard']);
    $router->get('/users', [AdminController::class, 'users']);
    $router->get('/users/create', [AdminController::class, 'createUser']);
    $router->post('/users', [AdminController::class, 'createUser'], [CsrfMiddleware::class]);
    $router->get('/users/{id}/edit', [AdminController::class, 'editUser']);
    $router->put('/users/{id}', [AdminController::class, 'editUser'], [CsrfMiddleware::class]);
    $router->post('/users/{id}', [AdminController::class, 'editUser'], [CsrfMiddleware::class]);
    $router->delete('/users/{id}', [AdminController::class, 'deleteUser'], [CsrfMiddleware::class]);
    $router->post('/users/{id}/role', [AdminController::class, 'updateRole'], [CsrfMiddleware::class]);
}, [SuperAdminMiddleware::class]);

$router->group('/dashboard', function (Router $router) {
    $router->get('/', [DashboardController::class, 'index']);
}, [AuthMiddleware::class]);
