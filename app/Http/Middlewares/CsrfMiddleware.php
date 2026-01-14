<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Services\Security\CsrfService;

class CsrfMiddleware implements MiddlewareInterface
{
    private CsrfService $csrfService;

    public function __construct(?CsrfService $csrfService = null)
    {
        $this->csrfService = $csrfService ?? new CsrfService();
    }

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        $token = $request->get('_csrf_token') ?? $request->header('X-CSRF-Token');

        if (!$token || !$this->csrfService->validate($token)) {
            return (new Response())->forbidden('Invalid CSRF token');
        }

        return $next($request);
    }
}
