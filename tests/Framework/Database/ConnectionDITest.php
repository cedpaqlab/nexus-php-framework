<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Database\Connection;
use PDO;

class ConnectionDITest extends TestCase
{
    private function skipIfNoDatabase(): void
    {
        try {
            Connection::getInstance();
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }

    public function testConnectionCanBeInstantiatedWithDI(): void
    {
        $this->skipIfNoDatabase();
        
        // Connection should be instantiable without static getInstance()
        // Use testing connection configured in setUp()
        $connection = new Connection('testing');
        $pdo = $connection->getPdo();
        
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testConnectionReturnsSamePdoInstance(): void
    {
        $this->skipIfNoDatabase();
        
        $connection = new Connection('testing');
        $pdo1 = $connection->getPdo();
        $pdo2 = $connection->getPdo();
        
        $this->assertSame($pdo1, $pdo2);
    }

    public function testMultipleConnectionInstancesCanExist(): void
    {
        $this->skipIfNoDatabase();
        
        $connection1 = new Connection('testing');
        $connection2 = new Connection('testing');
        
        // They should be able to exist independently
        $this->assertNotSame($connection1, $connection2);
    }

    public function testConnectionCanBeInjected(): void
    {
        $this->skipIfNoDatabase();
        
        // Test that Connection can be injected via constructor
        $testClass = new class($connection = new Connection('testing')) {
            public function __construct(
                private Connection $connection
            ) {
            }
            
            public function getConnection(): Connection
            {
                return $this->connection;
            }
        };
        
        $this->assertInstanceOf(Connection::class, $testClass->getConnection());
    }
}
