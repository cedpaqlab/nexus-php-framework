<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\DatabaseTestCase;
use Database\Migrations\CreateUsersTable;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Repositories\Database\Connection;

class CreateUsersTableTest extends DatabaseTestCase
{
    private CreateUsersTable $migration;
    private DatabaseConnectorInterface $connector;

    protected function setUp(): void
    {
        parent::setUp();
        
        $connection = new Connection();
        $factory = new ConnectorFactory($connection);
        $this->connector = $factory->create();
        $this->migration = new CreateUsersTable();
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        parent::tearDown();
    }

    public function testUpCreatesUsersTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        $this->assertNotFalse($result);
    }

    public function testUpCreatesTableWithCorrectColumns(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $columns = $this->pdo->query("DESCRIBE users")->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('id', $columnNames);
        $this->assertContains('email', $columnNames);
        $this->assertContains('password', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('role', $columnNames);
        $this->assertContains('created_at', $columnNames);
        $this->assertContains('updated_at', $columnNames);
    }

    public function testUpSetsEmailAsUnique(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $indexes = $this->pdo->query("SHOW INDEX FROM users WHERE Column_name = 'email'")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($indexes);
        
        $uniqueIndex = array_filter($indexes, fn($idx) => $idx['Non_unique'] == 0);
        $this->assertNotEmpty($uniqueIndex);
    }

    public function testUpCreatesIndexes(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $indexes = $this->pdo->query("SHOW INDEX FROM users")->fetchAll(\PDO::FETCH_ASSOC);
        $indexNames = array_unique(array_column($indexes, 'Key_name'));
        
        $this->assertContains('idx_email', $indexNames);
        $this->assertContains('idx_role', $indexNames);
    }

    public function testUpSetsRoleEnumValues(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $columns = $this->pdo->query("DESCRIBE users WHERE Field = 'role'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertStringContainsString("enum('user','admin','super_admin')", strtolower($columns['Type']));
    }

    public function testUpSetsDefaultRoleToUser(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $columns = $this->pdo->query("DESCRIBE users WHERE Field = 'role'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals('user', $columns['Default']);
    }

    public function testUpEnforcesEmailUniqueness(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->migration->up($this->connector);
        
        $this->pdo->exec("INSERT INTO users (email, password, name) VALUES ('test@example.com', 'hash', 'Test User')");
        
        $this->expectException(\PDOException::class);
        $this->pdo->exec("INSERT INTO users (email, password, name) VALUES ('test@example.com', 'hash2', 'Another User')");
    }

    public function testDownDropsUsersTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        $this->migration->up($this->connector);
        
        $this->migration->down($this->connector);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        $this->assertFalse($result);
    }
}
