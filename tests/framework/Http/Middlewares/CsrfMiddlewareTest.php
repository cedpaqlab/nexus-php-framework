<?php

declare(strict_types=1);

namespace Tests\Framework\Http\Middlewares;

use Tests\Support\TestCase;
use App\Http\Request;
use App\Http\Response;
use App\Http\Middlewares\CsrfMiddleware;
use App\Services\Security\CsrfService;

class CsrfMiddlewareTest extends TestCase
{
    private CsrfService $csrfService;
    private CsrfMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->csrfService = new CsrfService();
        $this->middleware = new CsrfMiddleware($this->csrfService);
    }

    public function testGetRequestPassesThrough(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRequestWithValidToken(): void
    {
        $token = $this->csrfService->generate();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_csrf_token'] = $token;
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRequestWithInvalidToken(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_csrf_token'] = 'invalid-token';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPostRequestWithoutToken(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(403, $response->getStatusCode());
    }
}
