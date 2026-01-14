<?php

declare(strict_types=1);

namespace Tests\Framework\Security;

use Tests\Support\TestCase;
use App\Services\Security\CsrfService;

class CsrfServiceTest extends TestCase
{
    private CsrfService $service;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->service = new CsrfService();
        $_SESSION = [];
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
        $_SESSION['_csrf_token']['expires'] = time() - 1;
        $this->assertFalse($this->service->validate($token));
    }

    public function testGetTokenName(): void
    {
        $name = $this->service->getTokenName();
        $this->assertIsString($name);
        $this->assertEquals('_csrf_token', $name);
    }
}
