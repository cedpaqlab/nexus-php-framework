<?php

declare(strict_types=1);

namespace App\Exceptions;

class BindingNotFoundException extends ContainerException
{
    public function __construct(string $abstract, ?\Throwable $previous = null)
    {
        parent::__construct("No binding found for: {$abstract}", 0, $previous);
    }
}
