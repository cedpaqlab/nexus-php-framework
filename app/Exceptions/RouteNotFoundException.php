<?php

declare(strict_types=1);

namespace App\Exceptions;

class RouteNotFoundException extends \RuntimeException
{
    public function __construct(string $route, ?\Throwable $previous = null)
    {
        parent::__construct("Route not found: {$route}", 0, $previous);
    }
}
