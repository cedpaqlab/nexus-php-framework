<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/loader.php';

use App\Database\Migrations\MigrationRunner;

$action = $argv[1] ?? 'run';
$steps = isset($argv[2]) ? (int) $argv[2] : 1;

$runner = new MigrationRunner();

try {
    match ($action) {
        'run' => $runner->run(),
        'rollback' => $runner->rollback($steps),
        default => throw new \InvalidArgumentException("Unknown action: {$action}. Use 'run' or 'rollback'"),
    };
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
