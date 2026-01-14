<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Container;

class RouterContainerTest extends TestCase
{
    private Router $router;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->router = new Router();
        $this->router->setContainer($this->container);
    }

    public function testRouterUsesContainerForResponse(): void
    {
        // Bind Response in container
        $responseInstance = new Response();
        $this->container->singleton(Response::class, fn() => $responseInstance);

        $this->router->get('/test', function (Request $request) {
            return (new Response())->json(['message' => 'test']);
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        $response = $this->router->dispatch($request);
        
        // Verify response was created (test passes if no exception)
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouterUsesContainerForNotFoundResponse(): void
    {
        $responseInstance = new Response();
        $this->container->singleton(Response::class, fn() => clone $responseInstance);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';
        $request = new Request();

        $response = $this->router->dispatch($request);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRouterUsesContainerForHandlerResolution(): void
    {
        $testController = new class {
            public function handle(Request $request): Response
            {
                return (new Response())->json(['handled' => true]);
            }
        };

        $this->container->singleton('TestController', fn() => $testController);

        $this->router->get('/test', ['TestController', 'handle']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        $response = $this->router->dispatch($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
