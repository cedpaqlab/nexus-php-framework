<?php

declare(strict_types=1);

namespace App\Exceptions;

class DatabaseConnectionException extends \RuntimeException
{
    public function __construct(string $message = 'Database connection failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
