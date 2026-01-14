<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Repositories\Database\Connection;
use PDO;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
        $this->migrationsPath = __DIR__ . '/../../../database/migrations';
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();
        
        $executedMigrations = $this->getExecutedMigrations();
        $availableMigrations = $this->getAvailableMigrations();
        
        $pendingMigrations = array_diff($availableMigrations, $executedMigrations);
        
        if (empty($pendingMigrations)) {
            if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
                echo "No pending migrations.\n";
            }
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
            if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
                echo "No migrations to rollback.\n";
            }
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
            $stmt = $this->pdo->query("SELECT 1 FROM {$this->migrationsTable} LIMIT 1");
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

        $this->pdo->exec($sql);
    }

    private function getExecutedMigrations(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT migration FROM {$this->migrationsTable} ORDER BY executed_at ASC");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
            echo "Running migration: {$migration}...\n";
        }

        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        $this->pdo->beginTransaction();
        try {
            $instance->up($this->pdo);
            $this->recordMigration($migration);
            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                try {
                    $this->pdo->rollBack();
                } catch (\PDOException $rollbackException) {
                }
            }
            throw $e;
        }

        if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
            echo "Migration completed: {$migration}\n";
        }
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

        if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
            echo "Rolling back migration: {$migration}...\n";
        }

        try {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->pdo->beginTransaction();
            try {
                $instance->down($this->pdo);
                $this->removeMigration($migration);
                if ($this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    try {
                        $this->pdo->rollBack();
                    } catch (\PDOException $rollbackException) {
                    }
                }
                if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Base table")) {
                    if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
                        echo "Migration table already dropped.\n";
                    }
                    return;
                }
                throw $e;
            }
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Base table")) {
                if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
                    echo "Migration table already dropped.\n";
                }
                return;
            }
            throw $e;
        }

        if (!isset($_ENV['PHPUNIT_RUNNING']) || $_ENV['PHPUNIT_RUNNING'] !== '1') {
            echo "Rollback completed: {$migration}\n";
        }
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
        $stmt = $this->pdo->prepare("INSERT INTO {$this->migrationsTable} (migration, executed_at) VALUES (?, ?)");
        $stmt->execute([$migration, date('Y-m-d H:i:s')]);
    }

    private function removeMigration(string $migration): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$migration]);
    }
}
