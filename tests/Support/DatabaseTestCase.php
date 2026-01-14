<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Repositories\Database\Connection;
use App\Repositories\Database\Transaction;
use Config\Config;
use PDO;
use PDOException;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure test database exists
        $this->ensureTestDatabaseExists();
        
        try {
            $this->pdo = Connection::getInstance();
        } catch (\RuntimeException $e) {
            // If connection still fails, create DB and retry
            $this->createTestDatabase();
            $this->pdo = Connection::getInstance();
        }
        
        $this->transaction = new Transaction($this->pdo);
        $this->transaction->begin();
    }
    
    private function ensureTestDatabaseExists(): void
    {
        $config = Config::get('database.connections.testing');
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
        } catch (PDOException $e) {
            // Ignore if DB already exists or connection fails
        }
    }
    
    private function createTestDatabase(): void
    {
        $config = Config::get('database.connections.testing');
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
        } catch (PDOException $e) {
            throw new \RuntimeException("Cannot create test database: " . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->transaction->rollback();
        }
        parent::tearDown();
    }
}
