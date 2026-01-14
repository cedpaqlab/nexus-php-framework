<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\TestCase;
use App\Database\Migrations\MigrationRunner;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Repositories\Database\Connection;

class MigrationIntegrationTest extends TestCase
{
    private function skipIfNoDatabase(): void
    {
        try {
            Connection::reset();
            Connection::setConnection('testing');
            Connection::getInstance();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }

    public function testMigrationRunnerCanBeInstantiated(): void
    {
        $this->skipIfNoDatabase();
        
        $connection = new Connection();
        $factory = new ConnectorFactory($connection);
        $connector = $factory->create();
        
        $runner = new MigrationRunner($connector);
        
        $this->assertInstanceOf(MigrationRunner::class, $runner);
    }

    public function testMigrationInterfaceExists(): void
    {
        $this->assertTrue(interface_exists('App\Database\Migrations\MigrationInterface'));
    }

    public function testCreateMigrationsTableClassExists(): void
    {
        $migrationFile = __DIR__ . '/../../../../database/migrations/20240101000000_CreateMigrationsTable.php';
        require_once $migrationFile;
        $this->assertTrue(class_exists('Database\Migrations\CreateMigrationsTable'));
    }

    public function testCreateUsersTableClassExists(): void
    {
        $migrationFile = __DIR__ . '/../../../../database/migrations/20240101000001_CreateUsersTable.php';
        require_once $migrationFile;
        $this->assertTrue(class_exists('Database\Migrations\CreateUsersTable'));
    }

    public function testMigrationsImplementInterface(): void
    {
        $migrationFile1 = __DIR__ . '/../../../../database/migrations/20240101000000_CreateMigrationsTable.php';
        require_once $migrationFile1;
        $migrationsTable = new \Database\Migrations\CreateMigrationsTable();
        $this->assertInstanceOf(\App\Database\Migrations\MigrationInterface::class, $migrationsTable);
        
        $migrationFile2 = __DIR__ . '/../../../../database/migrations/20240101000001_CreateUsersTable.php';
        require_once $migrationFile2;
        $usersTable = new \Database\Migrations\CreateUsersTable();
        $this->assertInstanceOf(\App\Database\Migrations\MigrationInterface::class, $usersTable);
    }
}
