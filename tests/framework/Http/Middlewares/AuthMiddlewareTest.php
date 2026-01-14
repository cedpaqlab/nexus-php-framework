<?php

declare(strict_types=1);

namespace Tests\Framework\Http\Middlewares;

use Tests\Support\TestCase;
use App\Http\Request;
use App\Http\Response;
use App\Http\Middlewares\AuthMiddleware;

class AuthMiddlewareTest extends TestCase
{
    private AuthMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->middleware = new AuthMiddleware();
    }

    public function testAuthenticatedUserPasses(): void
    {
        $_SESSION['user_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUnauthenticatedUserBlocked(): void
    {
        unset($_SESSION['user_id']);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(401, $response->getStatusCode());
    }
}
