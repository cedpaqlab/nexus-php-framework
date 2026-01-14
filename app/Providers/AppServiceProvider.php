<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Database\Connection;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Services\Security\CsrfService;
use App\Services\Security\HashService;
use App\Services\Security\Validator;
use App\Services\Logger\Logger;
use App\Services\View\ViewRenderer;
use App\Services\Helpers\PathHelper;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerDatabase();
        $this->registerSecurity();
        $this->registerServices();
        $this->registerHttp();
    }

    private function registerDatabase(): void
    {
        $this->container->singleton(Connection::class, function () {
            return new Connection();
        });
        
        // Keep backward compatibility with getInstance()
        $this->container->singleton(PDO::class, function ($container) {
            return $container->get(Connection::class)->getPdo();
        });

        $this->container->singleton(DatabaseConnectorInterface::class, function ($container) {
            $connection = $container->get(Connection::class);
            return ConnectorFactory::create(null, $connection);
        });
    }

    private function registerSecurity(): void
    {
        $this->container->singleton(CsrfService::class, function () {
            return new CsrfService();
        });

        $this->container->singleton(HashService::class, function () {
            return new HashService();
        });

        $this->container->singleton(Validator::class, function () {
            return new Validator();
        });
    }

    private function registerServices(): void
    {
        $this->container->singleton(Logger::class, function () {
            return new Logger();
        });

        $this->container->singleton(ViewRenderer::class, function () {
            return new ViewRenderer();
        });
    }

    private function registerHttp(): void
    {
        $this->container->singleton(Request::class, function () {
            return new Request();
        });

        $this->container->bind(Response::class, function () {
            return new Response();
        });

        $this->container->singleton(Router::class, function ($container) {
            $router = new Router();
            $router->setContainer($container);
            return $router;
        });
    }
}
