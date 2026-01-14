<?php

declare(strict_types=1);

namespace App\Exceptions;

class UnknownConnectorException extends \RuntimeException
{
    public function __construct(string $connector, ?\Throwable $previous = null)
    {
        parent::__construct("Unknown database connector: {$connector}", 0, $previous);
    }
}
