<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\Request;

class RequestReadonlyTest extends TestCase
{
    public function testRequestPropertiesAreImmutable(): void
    {
        $_GET = ['test' => 'value'];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        
        $request = new Request();
        $initialData = $request->all();
        
        // Modify superglobals after Request creation
        $_GET['new'] = 'new-value';
        $_POST['post'] = 'post-value';
        
        // Request data should not change (immutable)
        $this->assertEquals($initialData, $request->all());
        $this->assertArrayNotHasKey('new', $request->all());
    }
}
