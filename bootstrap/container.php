<?php

declare(strict_types=1);

use App\Exceptions\BindingNotFoundException;
use App\Exceptions\InvalidBindingException;
use App\Exceptions\ClassNotInstantiableException;
use App\Exceptions\ParameterResolutionException;

class Container
{
    private array $bindings = [];
    private array $singletons = [];
    private array $factories = [];

    public function bind(string $abstract, string|callable $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, string|callable $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function factory(string $abstract, callable $factory): void
    {
        $this->factories[$abstract] = $factory;
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }

        if (isset($this->factories[$abstract])) {
            return ($this->factories[$abstract])($this);
        }

        if (!isset($this->bindings[$abstract])) {
            if (class_exists($abstract)) {
                return $this->resolve($abstract);
            }
            throw new BindingNotFoundException($abstract);
        }

        $binding = $this->bindings[$abstract];
        $concrete = $binding['concrete'];

        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->resolve($concrete);
        } else {
            throw new InvalidBindingException($abstract);
        }

        if ($binding['singleton']) {
            $this->singletons[$abstract] = $instance;
        }

        return $instance;
    }

    public function get(string $abstract): mixed
    {
        return $this->make($abstract);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) 
            || isset($this->factories[$abstract]) 
            || isset($this->singletons[$abstract])
            || class_exists($abstract);
    }

    private function resolve(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new ClassNotInstantiableException($class);
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null || !$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new ParameterResolutionException($parameter->getName());
                }
            } else {
                $dependencies[] = $this->make($type->getName());
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
