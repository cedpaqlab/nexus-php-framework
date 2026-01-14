<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/loader.php';

use App\Repositories\Database\Connection;

try {
    Connection::reset();
    Connection::setConnection('testing');
    $pdo = Connection::getInstance();
    
    $database = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Test database name: " . $database . PHP_EOL;
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in test DB: " . (empty($tables) ? "none" : implode(", ", $tables)) . PHP_EOL;
    
    $devDb = $_ENV['DB_DATABASE'] ?? 'not set';
    $testDb = $_ENV['DB_TEST_DATABASE'] ?? ($devDb . '_test');
    echo "Expected test DB: " . $testDb . PHP_EOL;
    echo "Dev DB: " . $devDb . PHP_EOL;
    
    if ($database === $testDb || str_contains($database, 'test')) {
        echo "✓ Test database is correctly configured and used" . PHP_EOL;
    } else {
        echo "✗ Test database might not be correct" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
