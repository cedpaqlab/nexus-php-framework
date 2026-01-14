<?php

declare(strict_types=1);

namespace App\Exceptions;

class ClassNotInstantiableException extends ContainerException
{
    public function __construct(string $class, ?\Throwable $previous = null)
    {
        parent::__construct("Class {$class} is not instantiable", 0, $previous);
    }
}
