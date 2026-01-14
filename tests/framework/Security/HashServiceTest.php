<?php

declare(strict_types=1);

namespace Tests\Framework\Security;

use Tests\Support\TestCase;
use App\Services\Security\HashService;

class HashServiceTest extends TestCase
{
    private HashService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HashService();
    }

    public function testMakeHash(): void
    {
        $hash = $this->service->make('password123');
        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
        $this->assertNotEquals('password123', $hash);
    }

    public function testVerifyCorrectPassword(): void
    {
        $hash = $this->service->make('password123');
        $this->assertTrue($this->service->verify('password123', $hash));
    }

    public function testVerifyIncorrectPassword(): void
    {
        $hash = $this->service->make('password123');
        $this->assertFalse($this->service->verify('wrongpassword', $hash));
    }

    public function testNeedsRehash(): void
    {
        $oldHash = password_hash('password123', PASSWORD_DEFAULT, ['cost' => 4]);
        $this->assertTrue($this->service->needsRehash($oldHash));
    }

    public function testRandomToken(): void
    {
        $token1 = $this->service->randomToken();
        $token2 = $this->service->randomToken(16);
        
        $this->assertIsString($token1);
        $this->assertIsString($token2);
        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1));
        $this->assertEquals(32, strlen($token2));
    }
}
