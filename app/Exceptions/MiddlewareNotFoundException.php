<?php

declare(strict_types=1);

namespace App\Exceptions;

class MiddlewareNotFoundException extends \RuntimeException
{
    public function __construct(string $middleware, ?\Throwable $previous = null)
    {
        parent::__construct("Middleware not found: {$middleware}", 0, $previous);
    }
}
