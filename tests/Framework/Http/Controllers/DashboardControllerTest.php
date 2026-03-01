<?php

declare(strict_types=1);

namespace Tests\Framework\Http\Controllers;

use Tests\Support\TestCase;
use App\Http\Controllers\DashboardController;
use App\Http\Request;
use App\Services\View\ViewRenderer;
use App\Services\Session\SessionService;
use App\Services\Security\CsrfService;
use App\Services\Helpers\PathHelper;
use App\Http\Response;

class DashboardControllerTest extends TestCase
{
    private DashboardController $controller;
    private SessionService $session;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->session = new SessionService();
        
        $csrfService = new CsrfService($this->session);
        $blade = $this->createBlade(
            PathHelper::resourcesPath('views'),
            PathHelper::storagePath('framework/views')
        );
        $viewRenderer = new ViewRenderer($blade, $csrfService);
        
        $this->controller = new DashboardController(
            $viewRenderer,
            new Response(),
            $this->session
        );
    }

    public function testIndexRendersView(): void
    {
        $this->session->set('user_id', 1);
        $this->session->set('user_email', 'user@test.com');
        $this->session->set('user_role', 'user');
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
