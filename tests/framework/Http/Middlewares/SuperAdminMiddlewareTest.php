<?php

declare(strict_types=1);

namespace Tests\Framework\Http\Middlewares;

use Tests\Support\TestCase;
use App\Http\Request;
use App\Http\Response;
use App\Http\Middlewares\SuperAdminMiddleware;
use App\Services\Session\SessionService;

class SuperAdminMiddlewareTest extends TestCase
{
    private SuperAdminMiddleware $middleware;
    private SessionService $session;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->session = new SessionService();
        $this->middleware = new SuperAdminMiddleware(new Response(), $this->session);
    }

    public function testSuperAdminPasses(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_role', 'super_admin');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNonSuperAdminBlocked(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_role', 'admin');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testUnauthenticatedUserBlocked(): void
    {
        $this->session->remove('user_id');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->middleware->handle($request, function ($req) {
            return (new Response())->json(['ok' => true]);
        });
        
        $this->assertEquals(401, $response->getStatusCode());
    }
}
