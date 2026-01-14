<?php

declare(strict_types=1);

namespace App\Exceptions;

class InvalidBindingException extends ContainerException
{
    public function __construct(string $abstract, ?\Throwable $previous = null)
    {
        parent::__construct("Invalid binding for: {$abstract}", 0, $previous);
    }
}
