<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Container;

class RouterContainerStrictTest extends TestCase
{
    private Router $router;
    private Container $container;
    private int $responseCreationCount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->router = new Router();
        $this->router->setContainer($this->container);
        
        // Track Response creation through container
        $this->responseCreationCount = 0;
        $this->container->bind(Response::class, function () {
            $this->responseCreationCount++;
            return new Response();
        }, false); // Not singleton to track each creation
    }

    public function testRouterUsesContainerForNotFoundResponse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';
        $request = new Request();

        $initialCount = $this->responseCreationCount;
        $response = $this->router->dispatch($request);
        
        // If Router uses container, count should increase
        // If Router uses 'new Response()', count stays same
        $this->assertGreaterThan($initialCount, $this->responseCreationCount, 
            'Router should use container to create Response for not found');
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRouterUsesContainerForHandlerResponse(): void
    {
        $this->router->get('/test', function (Request $request) {
            // Handler returns Response - Router should not create new one
            return (new Response())->json(['test' => true]);
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        $initialCount = $this->responseCreationCount;
        $response = $this->router->dispatch($request);
        
        // Handler creates its own Response, Router should not create another
        // But if handler doesn't return Response, Router creates one via container
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouterUsesContainerWhenHandlerReturnsNonResponse(): void
    {
        $this->router->get('/test', function (Request $request) {
            // Return non-Response - Router should create Response via container
            return ['data' => 'test'];
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        $initialCount = $this->responseCreationCount;
        $response = $this->router->dispatch($request);
        
        // Router should create Response via container when handler returns non-Response
        $this->assertGreaterThan($initialCount, $this->responseCreationCount,
            'Router should use container to create Response when handler returns non-Response');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
