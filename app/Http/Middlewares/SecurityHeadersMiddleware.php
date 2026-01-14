<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;
use Config;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        $securityConfig = Config::get('security.headers', []);

        foreach ($securityConfig as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}
