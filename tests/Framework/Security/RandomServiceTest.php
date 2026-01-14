<?php

declare(strict_types=1);

namespace Tests\Framework\Security;

use Tests\Support\TestCase;
use App\Services\Security\RandomService;
use Random\Randomizer;
use Random\Engine\Secure;

class RandomServiceTest extends TestCase
{
    private RandomService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RandomService();
    }

    public function testRandomHex(): void
    {
        $hex1 = $this->service->randomHex(16);
        $hex2 = $this->service->randomHex(16);
        
        $this->assertIsString($hex1);
        $this->assertIsString($hex2);
        $this->assertNotEquals($hex1, $hex2);
        $this->assertEquals(32, strlen($hex1)); // 16 bytes = 32 hex chars
        $this->assertEquals(32, strlen($hex2));
    }

    public function testRandomToken(): void
    {
        $token1 = $this->service->randomToken();
        $token2 = $this->service->randomToken(16);
        
        $this->assertIsString($token1);
        $this->assertIsString($token2);
        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
        $this->assertEquals(32, strlen($token2)); // 16 bytes = 32 hex chars
    }

    public function testRandomInt(): void
    {
        $int1 = $this->service->randomInt(1, 100);
        $int2 = $this->service->randomInt(1, 100);
        
        $this->assertIsInt($int1);
        $this->assertIsInt($int2);
        $this->assertGreaterThanOrEqual(1, $int1);
        $this->assertLessThanOrEqual(100, $int1);
        $this->assertGreaterThanOrEqual(1, $int2);
        $this->assertLessThanOrEqual(100, $int2);
    }

    public function testShuffleArray(): void
    {
        $original = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $shuffled = $this->service->shuffleArray($original);
        
        $this->assertIsArray($shuffled);
        $this->assertCount(10, $shuffled);
        $this->assertEqualsCanonicalizing($original, $shuffled); // Same elements
        // Note: Very unlikely but possible that shuffle returns same order
    }

    public function testCanBeInjectedWithCustomRandomizer(): void
    {
        $customRandomizer = new Randomizer(new Secure());
        $service = new RandomService($customRandomizer);
        
        $token = $service->randomToken();
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
    }
}
