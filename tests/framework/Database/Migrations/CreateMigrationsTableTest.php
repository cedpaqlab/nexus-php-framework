<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\DatabaseTestCase;
use Database\Migrations\CreateMigrationsTable;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Repositories\Database\Connection;

class CreateMigrationsTableTest extends DatabaseTestCase
{
    private CreateMigrationsTable $migration;
    private DatabaseConnectorInterface $connector;

    protected function setUp(): void
    {
        parent::setUp();
        
        $migrationFile = __DIR__ . '/../../../../database/migrations/20240101000000_CreateMigrationsTable.php';
        require_once $migrationFile;
        
        $connection = new Connection();
        $factory = new ConnectorFactory($connection);
        $this->connector = $factory->create();
        $this->migration = new CreateMigrationsTable();
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        parent::tearDown();
    }

    public function testUpCreatesMigrationsTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        
        $this->migration->up($this->connector);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'migrations'")->fetch();
        $this->assertNotFalse($result);
    }

    public function testUpCreatesTableWithCorrectStructure(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        
        $this->migration->up($this->connector);
        
        $columns = $this->pdo->query("DESCRIBE migrations")->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('migration', $columnNames);
        $this->assertContains('executed_at', $columnNames);
    }

    public function testUpSetsPrimaryKeyOnMigration(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        
        $this->migration->up($this->connector);
        
        $indexes = $this->pdo->query("SHOW INDEX FROM migrations WHERE Key_name = 'PRIMARY'")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($indexes);
        $this->assertEquals('migration', $indexes[0]['Column_name']);
    }

    public function testDownDropsMigrationsTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->migration->up($this->connector);
        
        $this->migration->down($this->connector);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'migrations'")->fetch();
        $this->assertFalse($result);
    }
}
