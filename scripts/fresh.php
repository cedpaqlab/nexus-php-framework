<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/loader.php';

use App\Database\Migrations\MigrationRunner;
use App\Database\Seeders\SeederRunner;
use App\Repositories\Database\Connection;

$pdo = Connection::getInstance();

echo "Dropping all tables...\n";

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $pdo->exec('DROP TABLE IF EXISTS ' . $table);
    echo "Dropped table: {$table}\n";
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "\nRunning migrations...\n";
$migrationRunner = new MigrationRunner();
$migrationRunner->run();

echo "\nRunning seeders...\n";
$seederRunner = new SeederRunner();
$seederRunner->run();

echo "\nDatabase fresh completed!\n";
