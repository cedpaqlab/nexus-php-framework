<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class BaseException extends Exception
{
    protected int $statusCode = 500;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
