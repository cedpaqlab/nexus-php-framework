<?php

declare(strict_types=1);

namespace Tests\Framework\Exceptions;

use Tests\Support\TestCase;
use App\Exceptions\ViewNotFoundException;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\MiddlewareNotFoundException;
use App\Exceptions\DatabaseConnectionException;

class TypedExceptionsTest extends TestCase
{
    public function testViewNotFoundException(): void
    {
        $exception = new ViewNotFoundException('home');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('View not found: home', $exception->getMessage());
        $this->assertStringContainsString('home', $exception->getMessage());
    }

    public function testRouteNotFoundException(): void
    {
        $exception = new RouteNotFoundException('/api/users');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Route not found: /api/users', $exception->getMessage());
    }

    public function testMiddlewareNotFoundException(): void
    {
        $exception = new MiddlewareNotFoundException('AuthMiddleware');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Middleware not found: AuthMiddleware', $exception->getMessage());
    }

    public function testDatabaseConnectionException(): void
    {
        $previous = new \PDOException('Connection failed');
        $exception = new DatabaseConnectionException('Failed to connect', $previous);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Failed to connect', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
