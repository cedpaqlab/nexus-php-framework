<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if (!$this->isAuthenticated($request)) {
            return (new Response())->unauthorized();
        }

        return $next($request);
    }

    private function isAuthenticated(Request $request): bool
    {
        return isset($_SESSION['user_id']);
    }
}
