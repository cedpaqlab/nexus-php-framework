<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;
use App\Repositories\Database\Connection;
use Config;

class MigrationRunner
{
    private DatabaseConnectorInterface $connector;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(?DatabaseConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? $this->createConnector();
        $this->migrationsPath = __DIR__ . '/../../../database/migrations';
    }

    private function createConnector(): DatabaseConnectorInterface
    {
        $connection = new Connection();
        $factory = new ConnectorFactory($connection);
        return $factory->create();
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();
        
        $executedMigrations = $this->getExecutedMigrations();
        $availableMigrations = $this->getAvailableMigrations();
        
        $pendingMigrations = array_diff($availableMigrations, $executedMigrations);
        
        if (empty($pendingMigrations)) {
            echo "No pending migrations.\n";
            return;
        }

        sort($pendingMigrations);

        foreach ($pendingMigrations as $migration) {
            $this->executeMigration($migration);
        }
    }

    public function rollback(?int $steps = 1): void
    {
        $this->ensureMigrationsTable();
        
        $executedMigrations = $this->getExecutedMigrations();
        
        if (empty($executedMigrations)) {
            echo "No migrations to rollback.\n";
            return;
        }

        rsort($executedMigrations);
        $toRollback = array_slice($executedMigrations, 0, $steps);

        foreach ($toRollback as $migration) {
            $this->rollbackMigration($migration);
        }
    }

    private function ensureMigrationsTable(): void
    {
        try {
            $this->connector->findWhere($this->migrationsTable, ['migration' => '__check__']);
        } catch (\Throwable $e) {
            $this->createMigrationsTable();
        }
    }

    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo = Connection::getInstance();
        $pdo->exec($sql);
    }

    private function getExecutedMigrations(): array
    {
        try {
            $results = $this->connector->findAll($this->migrationsTable, [], ['executed_at' => 'ASC']);
            return array_column($results, 'migration');
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getAvailableMigrations(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $basename = basename($file, '.php');
            if (preg_match('/^\d{14}_/', $basename)) {
                $migrations[] = $basename;
            }
        }

        return $migrations;
    }

    private function executeMigration(string $migration): void
    {
        $className = $this->getMigrationClassName($migration);
        $filePath = $this->migrationsPath . '/' . $migration . '.php';

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Migration file not found: {$filePath}");
        }

        require_once $filePath;

        if (!class_exists($className)) {
            throw new \RuntimeException("Migration class not found: {$className}");
        }

        $instance = new $className();

        if (!$instance instanceof MigrationInterface) {
            throw new \RuntimeException("Migration must implement MigrationInterface: {$className}");
        }

        echo "Running migration: {$migration}...\n";

        $this->connector->executeInTransaction(function () use ($instance, $migration) {
            $instance->up($this->connector);
            $this->recordMigration($migration);
        });

        echo "Migration completed: {$migration}\n";
    }

    private function rollbackMigration(string $migration): void
    {
        $className = $this->getMigrationClassName($migration);
        $filePath = $this->migrationsPath . '/' . $migration . '.php';

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Migration file not found: {$filePath}");
        }

        require_once $filePath;

        if (!class_exists($className)) {
            throw new \RuntimeException("Migration class not found: {$className}");
        }

        $instance = new $className();

        if (!$instance instanceof MigrationInterface) {
            throw new \RuntimeException("Migration must implement MigrationInterface: {$className}");
        }

        echo "Rolling back migration: {$migration}...\n";

        try {
            $this->connector->executeInTransaction(function () use ($instance, $migration) {
                $instance->down($this->connector);
                try {
                    $this->removeMigration($migration);
                } catch (\Throwable $e) {
                    if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Base table")) {
                        return;
                    }
                    throw $e;
                }
            });
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Base table")) {
                echo "Migration table already dropped.\n";
                return;
            }
            throw $e;
        }

        echo "Rollback completed: {$migration}\n";
    }

    private function getMigrationClassName(string $migration): string
    {
        $parts = explode('_', $migration);
        array_shift($parts);
        
        $className = implode('', array_map(function ($part) {
            return ucfirst($part);
        }, $parts));

        return 'Database\\Migrations\\' . $className;
    }

    private function recordMigration(string $migration): void
    {
        $this->connector->create($this->migrationsTable, [
            'migration' => $migration,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function removeMigration(string $migration): void
    {
        $this->connector->delete($this->migrationsTable, ['migration' => $migration]);
    }

}
