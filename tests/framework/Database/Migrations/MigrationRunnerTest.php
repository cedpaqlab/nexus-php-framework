<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\DatabaseTestCase;
use App\Database\Migrations\MigrationRunner;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Repositories\Database\Connection;

class MigrationRunnerTest extends DatabaseTestCase
{
    private MigrationRunner $runner;
    private string $migrationsPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $connection = new Connection();
        $factory = new ConnectorFactory($connection);
        $connector = $factory->create();
        
        $this->runner = new MigrationRunner($connector);
        $this->migrationsPath = __DIR__ . '/../../../../database/migrations';
    }

    protected function tearDown(): void
    {
        $this->cleanupTestMigrations();
        parent::tearDown();
    }

    public function testEnsureMigrationsTableCreatesTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        
        $reflection = new \ReflectionClass($this->runner);
        $method = $reflection->getMethod('ensureMigrationsTable');
        $method->setAccessible(true);
        $method->invoke($this->runner);

        $result = $this->pdo->query("SHOW TABLES LIKE 'migrations'")->fetch();
        $this->assertNotFalse($result);
    }

    public function testGetExecutedMigrationsReturnsEmptyArrayWhenNoMigrations(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->pdo->exec("CREATE TABLE migrations (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $reflection = new \ReflectionClass($this->runner);
        $method = $reflection->getMethod('getExecutedMigrations');
        $method->setAccessible(true);
        
        $migrations = $method->invoke($this->runner);
        
        $this->assertIsArray($migrations);
        $this->assertEmpty($migrations);
    }

    public function testGetExecutedMigrationsReturnsMigrations(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->pdo->exec("CREATE TABLE migrations (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $this->pdo->exec("INSERT INTO migrations (migration) VALUES ('20240101000000_TestMigration')");
        $this->pdo->exec("INSERT INTO migrations (migration) VALUES ('20240101000001_AnotherMigration')");

        $reflection = new \ReflectionClass($this->runner);
        $method = $reflection->getMethod('getExecutedMigrations');
        $method->setAccessible(true);
        
        $migrations = $method->invoke($this->runner);
        
        $this->assertCount(2, $migrations);
        $this->assertContains('20240101000000_TestMigration', $migrations);
        $this->assertContains('20240101000001_AnotherMigration', $migrations);
    }

    public function testGetAvailableMigrationsFindsMigrationFiles(): void
    {
        $reflection = new \ReflectionClass($this->runner);
        $method = $reflection->getMethod('getAvailableMigrations');
        $method->setAccessible(true);
        
        $migrations = $method->invoke($this->runner);
        
        $this->assertIsArray($migrations);
        $this->assertContains('20240101000000_CreateMigrationsTable', $migrations);
        $this->assertContains('20240101000001_CreateUsersTable', $migrations);
    }

    public function testGetMigrationClassNameConvertsFileNameToClassName(): void
    {
        $reflection = new \ReflectionClass($this->runner);
        $method = $reflection->getMethod('getMigrationClassName');
        $method->setAccessible(true);
        
        $className = $method->invoke($this->runner, '20240101000000_CreateMigrationsTable');
        $this->assertEquals('Database\\Migrations\\CreateMigrationsTable', $className);
        
        $className = $method->invoke($this->runner, '20240101000001_CreateUsersTable');
        $this->assertEquals('Database\\Migrations\\CreateUsersTable', $className);
    }

    public function testRunExecutesPendingMigrations(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->runner->run();
        
        $migrations = $this->pdo->query("SELECT migration FROM migrations")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertContains('20240101000000_CreateMigrationsTable', $migrations);
        $this->assertContains('20240101000001_CreateUsersTable', $migrations);
        
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertContains('migrations', $tables);
        $this->assertContains('users', $tables);
    }

    public function testRunDoesNotExecuteAlreadyExecutedMigrations(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->pdo->exec("CREATE TABLE migrations (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->pdo->exec("INSERT INTO migrations (migration) VALUES ('20240101000000_CreateMigrationsTable')");
        
        $initialCount = $this->pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        
        $this->runner->run();
        
        $finalCount = $this->pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        $this->assertGreaterThan($initialCount, $finalCount);
        
        $migrations = $this->pdo->query("SELECT migration FROM migrations")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertCount(1, array_filter($migrations, fn($m) => $m === '20240101000000_CreateMigrationsTable'));
    }

    public function testRollbackRemovesMigrationRecord(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->runner->run();
        
        $initialCount = $this->pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        $this->assertGreaterThan(0, $initialCount);
        
        $this->runner->rollback(1);
        
        $finalCount = $this->pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        $this->assertEquals($initialCount - 1, $finalCount);
    }

    public function testRollbackExecutesDownMethod(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS migrations");
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        
        $this->runner->run();
        
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertContains('users', $tables);
        
        $this->runner->rollback(1);
        
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertNotContains('users', $tables);
    }

    private function cleanupTestMigrations(): void
    {
        try {
            $this->pdo->exec("DROP TABLE IF EXISTS test_migration_table");
            $this->pdo->exec("DELETE FROM migrations WHERE migration LIKE '20240101%Test%'");
        } catch (\Throwable $e) {
        }
    }
}
