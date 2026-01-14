<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Database\Migrations\MigrationInterface;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Database\Connection;

class CreateMigrationsTable implements MigrationInterface
{
    public function up(DatabaseConnectorInterface $connector): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo = Connection::getInstance();
        $pdo->exec($sql);
    }

    public function down(DatabaseConnectorInterface $connector): void
    {
        $sql = "DROP TABLE IF EXISTS migrations";
        $pdo = Connection::getInstance();
        $pdo->exec($sql);
    }
}
