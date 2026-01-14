<?php

declare(strict_types=1);

namespace Tests\Framework\Helpers;

use Tests\Support\TestCase;
use App\Services\Helpers\StringHelper;

class StringHelperTest extends TestCase
{
    public function testRandom(): void
    {
        $random1 = StringHelper::random();
        $random2 = StringHelper::random();
        $this->assertNotEquals($random1, $random2);
        $this->assertEquals(16, strlen($random1));
    }

    public function testSlug(): void
    {
        $slug = StringHelper::slug('Hello World Test');
        $this->assertEquals('hello-world-test', $slug);
    }

    public function testCamel(): void
    {
        $camel = StringHelper::camel('hello-world-test');
        $this->assertEquals('helloWorldTest', $camel);
    }

    public function testSnake(): void
    {
        $snake = StringHelper::snake('HelloWorldTest');
        $this->assertEquals('hello_world_test', $snake);
    }

    public function testStartsWith(): void
    {
        $this->assertTrue(StringHelper::startsWith('Hello World', 'Hello'));
        $this->assertFalse(StringHelper::startsWith('Hello World', 'World'));
    }

    public function testEndsWith(): void
    {
        $this->assertTrue(StringHelper::endsWith('Hello World', 'World'));
        $this->assertFalse(StringHelper::endsWith('Hello World', 'Hello'));
    }

    public function testContains(): void
    {
        $this->assertTrue(StringHelper::contains('Hello World', 'World'));
        $this->assertFalse(StringHelper::contains('Hello World', 'Test'));
    }

    public function testLimit(): void
    {
        $limited = StringHelper::limit('This is a long string', 10);
        $this->assertEquals('This is a ...', $limited);
    }

    public function testLimitWithShortString(): void
    {
        $limited = StringHelper::limit('Short', 10);
        $this->assertEquals('Short', $limited);
    }
}
