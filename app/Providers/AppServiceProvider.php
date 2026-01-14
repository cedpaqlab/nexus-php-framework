<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Connectors\PropelConnector;
use App\Services\Security\CsrfService;
use App\Services\Security\HashService;
use App\Services\Security\Validator;
use App\Services\Session\SessionService;
use App\Services\User\UserService;
use App\Services\Auth\AuthService;
use App\Services\Logger\Logger;
use App\Services\View\ViewRenderer;
use App\Services\Helpers\PathHelper;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Http\Middlewares\SuperAdminMiddleware;
use App\Http\Middlewares\AuthMiddleware;
use App\Http\Middlewares\SecurityHeadersMiddleware;
use Container;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerPropel();
        $this->registerSecurity();
        $this->registerServices();
        $this->registerHttp();
        $this->registerUserServices();
    }

    private function registerPropel(): void
    {
        $this->container->singleton(PropelConnector::class, function () {
            return new PropelConnector();
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

        $this->container->singleton(Validator::class, function () {
            return new Validator();
        });
    }

    private function registerServices(): void
    {
        $this->container->singleton(Logger::class, function () {
            return new Logger();
        });

        $this->container->singleton(ViewRenderer::class, function (Container $container) {
            $csrfService = $container->get(CsrfService::class);
            $renderer = new ViewRenderer();
            $renderer->setCsrfService($csrfService);
            return $renderer;
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

    private function registerUserServices(): void
    {
        $this->container->singleton(UserService::class, function (Container $container) {
            return new UserService(
                $container->get(\App\Repositories\User\UserRepository::class),
                $container->get(HashService::class),
                $container->get(Validator::class)
            );
        });

        $this->container->singleton(\App\Repositories\User\UserRepository::class, function (Container $container) {
            return new \App\Repositories\User\UserRepository(
                $container->get(PropelConnector::class)
            );
        });

        $this->container->singleton(SuperAdminMiddleware::class, function (Container $container) {
            return new SuperAdminMiddleware(
                $container->make(Response::class),
                $container->get(SessionService::class)
            );
        });

        $this->container->singleton(AuthMiddleware::class, function (Container $container) {
            return new AuthMiddleware(
                $container->make(Response::class),
                $container->get(SessionService::class)
            );
        });

        $this->container->singleton(AuthService::class, function (Container $container) {
            return new AuthService(
                $container->get(\App\Repositories\User\UserRepository::class),
                $container->get(HashService::class),
                $container->get(SessionService::class)
            );
        });

        $this->container->singleton(CsrfMiddleware::class, function (Container $container) {
            return new CsrfMiddleware(
                $container->get(CsrfService::class),
                $container->make(Response::class)
            );
        });

        $this->container->singleton(SecurityHeadersMiddleware::class, function () {
            return new SecurityHeadersMiddleware();
        });
    }
}
