<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Database\Connection;
use PDO;

class ConnectionTest extends TestCase
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

    public function testGetInstanceReturnsPdo(): void
    {
        $this->ensureTestDatabase();
        $pdo = Connection::getInstance();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $this->ensureTestDatabase();
        $pdo1 = Connection::getInstance();
        $pdo2 = Connection::getInstance();
        $this->assertSame($pdo1, $pdo2);
    }

    public function testConnectionHasCorrectAttributes(): void
    {
        $this->ensureTestDatabase();
        $pdo = Connection::getInstance();
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }
}
