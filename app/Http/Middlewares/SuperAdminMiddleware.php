<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Services\Session\SessionService;

class SuperAdminMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Response $response,
        private SessionService $session
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->response->unauthorized();
        }

        if (!$this->isSuperAdmin()) {
            return $this->response->forbidden('Access denied. Super admin privileges required.');
        }

        return $next($request);
    }

    private function isAuthenticated(): bool
    {
        return $this->session->has('user_id');
    }

    private function isSuperAdmin(): bool
    {
        $role = $this->session->get('user_role');
        return $role === 'super_admin';
    }
}
