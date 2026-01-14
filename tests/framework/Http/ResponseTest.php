<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\Response;

class ResponseTest extends TestCase
{
    public function testStatus(): void
    {
        $response = new Response();
        $response->status(404);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHeader(): void
    {
        $response = new Response();
        $response->header('X-Custom', 'value');
        $response->send();
        $this->assertTrue(true);
    }

    public function testJson(): void
    {
        $response = new Response();
        $response->json(['message' => 'test'], 200);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testHtml(): void
    {
        $response = new Response();
        $response->html('<h1>Test</h1>');
        $this->assertEquals('<h1>Test</h1>', $response->getContent());
    }

    public function testText(): void
    {
        $response = new Response();
        $response->text('Plain text');
        $this->assertEquals('Plain text', $response->getContent());
    }

    public function testRedirect(): void
    {
        $response = new Response();
        $response->redirect('/new-location', 301);
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testNotFound(): void
    {
        $response = new Response();
        $response->notFound();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUnauthorized(): void
    {
        $response = new Response();
        $response->unauthorized();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testForbidden(): void
    {
        $response = new Response();
        $response->forbidden();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testServerError(): void
    {
        $response = new Response();
        $response->serverError();
        $this->assertEquals(500, $response->getStatusCode());
    }
}
