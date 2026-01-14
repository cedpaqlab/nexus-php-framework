<?php

declare(strict_types=1);

namespace App\Providers;

abstract class ServiceProvider
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    abstract public function register(): void;
}
