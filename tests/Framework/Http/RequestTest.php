<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\Request;

class RequestTest extends TestCase
{
    public function testMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        $this->assertEquals('POST', $request->method());
    }

    public function testUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/test/path';
        $request = new Request();
        $this->assertEquals('/test/path', $request->uri());
    }

    public function testGet(): void
    {
        $_GET['test'] = 'value';
        $request = new Request();
        $this->assertEquals('value', $request->get('test'));
    }

    public function testGetWithDefault(): void
    {
        $request = new Request();
        $this->assertEquals('default', $request->get('nonexistent', 'default'));
    }

    public function testHas(): void
    {
        $_POST['exists'] = 'value';
        $request = new Request();
        $this->assertTrue($request->has('exists'));
        $this->assertFalse($request->has('nonexistent'));
    }

    public function testAll(): void
    {
        $_GET = ['key1' => 'value1'];
        $_POST = ['key2' => 'value2'];
        $request = new Request();
        $all = $request->all();
        $this->assertArrayHasKey('key1', $all);
        $this->assertArrayHasKey('key2', $all);
    }

    public function testOnly(): void
    {
        $_POST = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
        $request = new Request();
        $only = $request->only(['name', 'email']);
        $this->assertArrayHasKey('name', $only);
        $this->assertArrayHasKey('email', $only);
        $this->assertArrayNotHasKey('age', $only);
    }

    public function testExcept(): void
    {
        $_POST = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
        $request = new Request();
        $except = $request->except(['age']);
        $this->assertArrayHasKey('name', $except);
        $this->assertArrayHasKey('email', $except);
        $this->assertArrayNotHasKey('age', $except);
    }

    public function testIsMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        $this->assertTrue($request->isMethod('GET'));
        $this->assertFalse($request->isMethod('POST'));
    }

    public function testIsAjax(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new Request();
        $this->assertTrue($request->isAjax());
    }

    public function testIsJson(): void
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $request = new Request();
        $this->assertTrue($request->isJson());
    }

    public function testIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $request = new Request();
        $this->assertEquals('192.168.1.1', $request->ip());
    }
}
