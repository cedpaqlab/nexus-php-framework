<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Container;

class RouterTest extends TestCase
{
    private Router $router;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->container->bind(Response::class, function () {
            return new Response();
        });
        $this->router = new Router();
        $this->router->setContainer($this->container);
    }

    public function testGetRoute(): void
    {
        $this->router->get('/test', function () {
            return (new Response())->json(['message' => 'test']);
        });
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();
        
        $response = $this->router->dispatch($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRoute(): void
    {
        $this->router->post('/test', function () {
            return (new Response())->json(['message' => 'posted']);
        });
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();
        
        $response = $this->router->dispatch($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteWithParameters(): void
    {
        $this->router->get('/user/{id}', function (Request $request, string $id) {
            return (new Response())->json(['id' => $id]);
        });
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/user/123';
        $request = new Request();
        
        $response = $this->router->dispatch($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNotFoundRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';
        $request = new Request();
        
        $response = $this->router->dispatch($request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGroupRoutes(): void
    {
        $this->router->group('/api', function (Router $router) {
            $router->get('/users', function () {
                return (new Response())->json(['users' => []]);
            });
        });
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/users';
        $request = new Request();
        
        $response = $this->router->dispatch($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
