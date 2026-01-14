<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Database\Connection;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Services\Security\CsrfService;
use App\Services\Security\HashService;
use App\Services\Security\Validator;
use App\Services\Session\SessionService;
use App\Services\Logger\Logger;
use App\Services\View\ViewRenderer;
use App\Services\Helpers\PathHelper;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Container;
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
        $this->container->singleton(PDO::class, function (Container $container) {
            return $container->get(Connection::class)->getPdo();
        });

        $this->container->singleton(DatabaseConnectorInterface::class, function (Container $container) {
            $connection = $container->get(Connection::class);
            $factory = new ConnectorFactory($connection);
            return $factory->create();
        });
    }

    private function registerSecurity(): void
    {
        $this->container->singleton(SessionService::class, function () {
            return new SessionService();
        });

        $this->container->singleton(CsrfService::class, function (Container $container) {
            $session = $container->get(SessionService::class);
            return new CsrfService($session);
        });

        $this->container->singleton(HashService::class, function () {
            return new HashService();
        });

        $this->container->singleton(Validator::class, function (Container $container) {
            $connector = $container->has(DatabaseConnectorInterface::class) 
                ? $container->get(DatabaseConnectorInterface::class) 
                : null;
            return new Validator($connector);
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

        $this->container->singleton(Router::class, function (Container $container) {
            $router = new Router();
            $router->setContainer($container);
            return $router;
        });
    }
}
