<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Database\Connection;
use PDO;

class ConnectionDITest extends TestCase
{
    public function testConnectionCanBeInstantiatedWithDI(): void
    {
        // Connection should be instantiable without static getInstance()
        $connection = new Connection();
        $pdo = $connection->getPdo();
        
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testConnectionReturnsSamePdoInstance(): void
    {
        $connection = new Connection();
        $pdo1 = $connection->getPdo();
        $pdo2 = $connection->getPdo();
        
        $this->assertSame($pdo1, $pdo2);
    }

    public function testMultipleConnectionInstancesCanExist(): void
    {
        $connection1 = new Connection();
        $connection2 = new Connection();
        
        // They should be able to exist independently
        $this->assertNotSame($connection1, $connection2);
    }

    public function testConnectionCanBeInjected(): void
    {
        // Test that Connection can be injected via constructor
        $testClass = new class($connection = new Connection()) {
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
