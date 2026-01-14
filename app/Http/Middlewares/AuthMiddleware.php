<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Services\Session\SessionService;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Response $response,
        private SessionService $session
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->isAuthenticated($request)) {
            return $this->response->unauthorized();
        }

        return $next($request);
    }

    private function isAuthenticated(Request $request): bool
    {
        return $this->session->has('user_id');
    }
}
