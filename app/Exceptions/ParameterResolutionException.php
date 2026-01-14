<?php

declare(strict_types=1);

namespace App\Exceptions;

class ParameterResolutionException extends ContainerException
{
    public function __construct(string $parameter, ?\Throwable $previous = null)
    {
        parent::__construct("Cannot resolve parameter: {$parameter}", 0, $previous);
    }
}
