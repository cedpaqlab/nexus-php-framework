<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Services\Security\CsrfService;

class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CsrfService $csrfService,
        private Response $response
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        $token = $request->get('_csrf_token') ?? $request->header('X-CSRF-Token');

        if (!$token || !$this->csrfService->validate($token)) {
            return $this->response->forbidden('Invalid CSRF token');
        }

        return $next($request);
    }
}
