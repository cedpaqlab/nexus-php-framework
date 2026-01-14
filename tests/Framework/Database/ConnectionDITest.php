<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Database\Connection;
use PDO;

class ConnectionDITest extends TestCase
{
    private function ensureTestDatabase(): void
    {
        $this->createTestDatabaseIfNeeded();
    }
    
    private function createTestDatabaseIfNeeded(): void
    {
        $config = \Config\Config::get('database.connections.testing');
        if (!$config || empty($config['database'])) {
            return;
        }
        
        $dbName = $config['database'];
        $host = $config['host'];
        $port = $config['port'];
        $username = $config['username'];
        $password = $config['password'];
        
        try {
            $adminPdo = new PDO(
                sprintf('mysql:host=%s;port=%d', $host, $port),
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $adminPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
        } catch (\PDOException $e) {
            // Ignore if DB already exists or connection fails
        }
    }

    public function testConnectionCanBeInstantiatedWithDI(): void
    {
        $this->ensureTestDatabase();
        
        // Connection should be instantiable without static getInstance()
        // Use testing connection configured in setUp()
        $connection = new Connection('testing');
        $pdo = $connection->getPdo();
        
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testConnectionReturnsSamePdoInstance(): void
    {
        $this->ensureTestDatabase();
        
        $connection = new Connection('testing');
        $pdo1 = $connection->getPdo();
        $pdo2 = $connection->getPdo();
        
        $this->assertSame($pdo1, $pdo2);
    }

    public function testMultipleConnectionInstancesCanExist(): void
    {
        $this->ensureTestDatabase();
        
        $connection1 = new Connection('testing');
        $connection2 = new Connection('testing');
        
        // They should be able to exist independently
        $this->assertNotSame($connection1, $connection2);
    }

    public function testConnectionCanBeInjected(): void
    {
        $this->ensureTestDatabase();
        
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
