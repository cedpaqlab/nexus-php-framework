<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Database\Connection;
use PDO;

class ConnectionTest extends TestCase
{
    private function skipIfNoDatabase(): void
    {
        try {
            Connection::getInstance();
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }

    public function testGetInstanceReturnsPdo(): void
    {
        $this->skipIfNoDatabase();
        $pdo = Connection::getInstance();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $this->skipIfNoDatabase();
        $pdo1 = Connection::getInstance();
        $pdo2 = Connection::getInstance();
        $this->assertSame($pdo1, $pdo2);
    }

    public function testConnectionHasCorrectAttributes(): void
    {
        $this->skipIfNoDatabase();
        $pdo = Connection::getInstance();
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }
}
