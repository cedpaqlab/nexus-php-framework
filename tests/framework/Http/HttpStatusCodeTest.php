<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use Tests\Support\TestCase;
use App\Http\HttpStatusCode;

class HttpStatusCodeTest extends TestCase
{
    public function testHttpStatusCodeValues(): void
    {
        $this->assertEquals(200, HttpStatusCode::OK->value());
        $this->assertEquals(404, HttpStatusCode::NOT_FOUND->value());
        $this->assertEquals(500, HttpStatusCode::INTERNAL_SERVER_ERROR->value());
    }

    public function testHttpStatusCodeCanBeUsedInResponse(): void
    {
        $statusCode = HttpStatusCode::NOT_FOUND;
        $this->assertEquals(404, $statusCode->value);
    }
}
