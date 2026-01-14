<?php

declare(strict_types=1);

namespace App\Http;

use Container;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $groupMiddleware = [];
    private string $prefix = '';
    private ?Container $container = null;

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function get(string $path, callable|string|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|string|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|string|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|string|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|string|array $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $oldPrefix = $this->prefix;
        $oldMiddleware = $this->groupMiddleware;

        $this->prefix = $oldPrefix . $prefix;
        $this->groupMiddleware = array_merge($oldMiddleware, $middleware);

        $callback($this);

        $this->prefix = $oldPrefix;
        $this->groupMiddleware = $oldMiddleware;
    }

    public function middleware(array $middleware): void
    {
        $this->middlewares = array_merge($this->middlewares, $middleware);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $params = array_values($matches);

                $allMiddleware = array_merge($this->middlewares, $route['middleware']);
                $handler = $route['handler'];

                return $this->runMiddleware($allMiddleware, $request, function (Request $req) use ($handler, $params) {
                    return $this->callHandler($handler, $req, $params);
                });
            }
        }

        return (new Response())->notFound();
    }

    private function addRoute(string $method, string $path, callable|string|array $handler, array $middleware): void
    {
        $fullPath = $this->prefix . $path;
        $allMiddleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $allMiddleware,
        ];
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function runMiddleware(array $middleware, Request $request, callable $next): Response
    {
        if (empty($middleware)) {
            return $next($request);
        }

        $current = array_shift($middleware);
        
        if (is_string($current)) {
            if ($this->container !== null && $this->container->has($current)) {
                $middlewareInstance = $this->container->get($current);
            } else {
                $middlewareInstance = new $current();
            }
        } else {
            $middlewareInstance = $current;
        }

        if (!($middlewareInstance instanceof Middlewares\MiddlewareInterface)) {
            throw new \RuntimeException('Middleware must implement MiddlewareInterface');
        }

        return $middlewareInstance->handle($request, function (Request $req) use ($middleware, $next) {
            return $this->runMiddleware($middleware, $req, $next);
        });
    }

    private function callHandler(callable|string|array $handler, Request $request, array $params): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = $this->resolveInstance($class);
            $result = $instance->$method($request, ...$params);
        } elseif (is_string($handler)) {
            [$class, $method] = explode('@', $handler);
            $instance = $this->resolveInstance($class);
            $result = $instance->$method($request, ...$params);
        } else {
            $result = $handler($request, ...$params);
        }

        if ($result instanceof Response) {
            return $result;
        }

        return (new Response())->json($result);
    }

    private function resolveInstance(string $class): object
    {
        if ($this->container !== null && $this->container->has($class)) {
            return $this->container->get($class);
        }

        return new $class();
    }
}
