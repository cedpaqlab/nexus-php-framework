<?php

declare(strict_types=1);

namespace Tests\Framework\Security;

use Tests\Support\TestCase;
use App\Services\Security\CsrfService;
use App\Services\Session\SessionService;

class CsrfServiceTest extends TestCase
{
    private CsrfService $service;
    private SessionService $session;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->session = new SessionService();
        $this->service = new CsrfService($this->session);
        $this->session->flush();
    }

    public function testGenerateToken(): void
    {
        $token = $this->service->generate();
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateValidToken(): void
    {
        $token = $this->service->generate();
        $this->assertTrue($this->service->validate($token));
    }

    public function testValidateInvalidToken(): void
    {
        $this->service->generate();
        $this->assertFalse($this->service->validate('invalid-token'));
    }

    public function testValidateExpiredToken(): void
    {
        $token = $this->service->generate();
        $tokenData = $this->session->get('_csrf_token');
        $tokenData['expires'] = time() - 1;
        $this->session->set('_csrf_token', $tokenData);
        $this->assertFalse($this->service->validate($token));
    }

    public function testGetTokenName(): void
    {
        $name = $this->service->getTokenName();
        $this->assertIsString($name);
        $this->assertEquals('_csrf_token', $name);
    }
}
