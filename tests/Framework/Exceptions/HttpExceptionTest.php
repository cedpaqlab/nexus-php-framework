<?php

declare(strict_types=1);

namespace Tests\Framework\Exceptions;

use Tests\Support\TestCase;
use App\Exceptions\HttpException;

class HttpExceptionTest extends TestCase
{
    public function testNotFound(): void
    {
        $exception = HttpException::notFound();
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('Not Found', $exception->getMessage());
    }

    public function testUnauthorized(): void
    {
        $exception = HttpException::unauthorized();
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('Unauthorized', $exception->getMessage());
    }

    public function testForbidden(): void
    {
        $exception = HttpException::forbidden();
        $this->assertEquals(403, $exception->getStatusCode());
        $this->assertEquals('Forbidden', $exception->getMessage());
    }

    public function testBadRequest(): void
    {
        $exception = HttpException::badRequest();
        $this->assertEquals(400, $exception->getStatusCode());
        $this->assertEquals('Bad Request', $exception->getMessage());
    }

    public function testServerError(): void
    {
        $exception = HttpException::serverError();
        $this->assertEquals(500, $exception->getStatusCode());
        $this->assertEquals('Internal Server Error', $exception->getMessage());
    }

    public function testCustomMessage(): void
    {
        $exception = HttpException::notFound('Custom message');
        $this->assertEquals('Custom message', $exception->getMessage());
    }
}
