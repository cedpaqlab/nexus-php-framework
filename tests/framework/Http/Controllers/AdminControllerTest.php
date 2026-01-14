<?php

declare(strict_types=1);

namespace Tests\Framework\Http\Controllers;

use Tests\Support\TestCase;
use App\Http\Controllers\AdminController;
use App\Http\Request;
use App\Services\View\ViewRenderer;
use App\Services\User\UserService;
use App\Services\Session\SessionService;
use App\Services\Security\CsrfService;
use App\Http\Response;

class AdminControllerTest extends TestCase
{
    private AdminController $controller;
    private UserService $userService;
    private SessionService $session;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->session = new SessionService();
        $this->userService = $this->createMock(UserService::class);
        
        $csrfService = new CsrfService($this->session);
        $viewRenderer = new ViewRenderer();
        $viewRenderer->setCsrfService($csrfService);
        
        $this->controller = new AdminController(
            $viewRenderer,
            new Response(),
            $this->userService,
            $this->session
        );
    }

    public function testDashboardRendersView(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_email', 'admin@test.com');
        $this->session->set('user_role', 'super_admin');
        
        $this->userService->expects($this->once())
            ->method('getAllUsers')
            ->willReturn([]);
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->controller->dashboard($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUsersListRendersView(): void
    {
        $this->userService->expects($this->once())
            ->method('getAllUsers')
            ->willReturn([]);
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['role' => ''];
        $_POST = [];
        $request = new Request();
        
        $response = $this->controller->users($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateUserReturnsForm(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->controller->createUser($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateUserCreatesUser(): void
    {
        $this->userService->expects($this->once())
            ->method('create')
            ->willReturn(1);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];
        $request = new Request();
        
        $response = $this->controller->createUser($request);
        
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testDeleteUserDeletesUser(): void
    {
        $this->userService->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(1);
        
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = new Request();
        
        $response = $this->controller->deleteUser($request, '1');
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
