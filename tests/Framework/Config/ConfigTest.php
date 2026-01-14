<?php

declare(strict_types=1);

namespace Tests\Framework\Config;

use Tests\Support\TestCase;
use Config\Config;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['APP_NAME'] = 'TestApp';
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
    }

    public function testGetReturnsValue(): void
    {
        $value = Config::get('app.name');
        $this->assertIsString($value);
    }

    public function testGetReturnsDefaultWhenKeyNotFound(): void
    {
        $value = Config::get('app.nonexistent', 'default');
        $this->assertEquals('default', $value);
    }

    public function testGetNestedValue(): void
    {
        $value = Config::get('database.connections.mysql.host');
        $this->assertIsString($value);
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $this->assertTrue(Config::has('app.name'));
    }

    public function testHasReturnsFalseForNonExistentKey(): void
    {
        $this->assertFalse(Config::has('app.nonexistent'));
    }
}
