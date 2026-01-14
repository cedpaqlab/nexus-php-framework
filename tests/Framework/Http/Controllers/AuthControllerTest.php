<?php

declare(strict_types=1);

namespace Tests\Framework\Http\Controllers;

use Tests\Support\TestCase;
use App\Http\Controllers\AuthController;
use App\Http\Request;
use App\Services\View\ViewRenderer;
use App\Services\Auth\AuthService;
use App\Services\Session\SessionService;
use App\Services\Security\CsrfService;
use App\Http\Response;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authService = $this->createMock(AuthService::class);
        
        $session = new SessionService();
        $csrfService = new CsrfService($session);
        $viewRenderer = new ViewRenderer();
        $viewRenderer->setCsrfService($csrfService);
        
        $this->controller = new AuthController(
            $viewRenderer,
            new Response(),
            $this->authService
        );
    }

    public function testShowLoginRendersView(): void
    {
        $this->authService->expects($this->once())
            ->method('check')
            ->willReturn(false);
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];
        $_POST = [];
        $request = new Request();
        
        $response = $this->controller->showLogin($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowLoginRedirectsIfAuthenticated(): void
    {
        $this->authService->expects($this->once())
            ->method('check')
            ->willReturn(true);
        
        $this->authService->expects($this->once())
            ->method('isSuperAdmin')
            ->willReturn(false);
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];
        $_POST = [];
        $request = new Request();
        
        $response = $this->controller->showLogin($request);
        
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testLoginReturnsErrorOnInvalidCredentials(): void
    {
        $this->authService->expects($this->once())
            ->method('attempt')
            ->with('test@example.com', 'wrongpassword')
            ->willReturn(false);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [];
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];
        $request = new Request();
        
        $response = $this->controller->login($request);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testLogoutLogsOutUser(): void
    {
        $this->authService->expects($this->once())
            ->method('logout');
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [];
        $_POST = [];
        $request = new Request();
        
        $response = $this->controller->logout($request);
        
        $this->assertEquals(302, $response->getStatusCode());
    }
}
