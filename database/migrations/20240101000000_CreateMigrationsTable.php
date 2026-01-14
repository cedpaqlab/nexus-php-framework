<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Database\Migrations\MigrationInterface;
use PDO;

class CreateMigrationsTable implements MigrationInterface
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $sql = "DROP TABLE IF EXISTS migrations";
        $pdo->exec($sql);
    }
}
