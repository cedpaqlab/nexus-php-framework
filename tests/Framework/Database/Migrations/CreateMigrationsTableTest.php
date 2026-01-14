<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\DatabaseTestCase;
use Database\Migrations\CreateMigrationsTable;
use PDO;

class CreateMigrationsTableTest extends DatabaseTestCase
{
    private CreateMigrationsTable $migration;

    protected function setUp(): void
    {
        parent::setUp();
        
        $migrationFile = __DIR__ . '/../../../../database/migrations/20240101000000_CreateMigrationsTable.php';
        require_once $migrationFile;
        
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
        
        $this->migration->up($this->pdo);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'migrations'")->fetch();
        $this->assertNotFalse($result);
    }

    public function testUpCreatesTableWithCorrectStructure(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        
        $this->migration->up($this->pdo);
        
        $columns = $this->pdo->query("DESCRIBE migrations")->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('migration', $columnNames);
        $this->assertContains('executed_at', $columnNames);
    }

    public function testUpSetsPrimaryKeyOnMigration(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        
        $this->migration->up($this->pdo);
        
        $indexes = $this->pdo->query("SHOW INDEX FROM migrations WHERE Key_name = 'PRIMARY'")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($indexes);
        $this->assertEquals('migration', $indexes[0]['Column_name']);
    }

    public function testDownDropsMigrationsTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->migration->up($this->pdo);
        
        $this->migration->down($this->pdo);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'migrations'")->fetch();
        $this->assertFalse($result);
    }
}
