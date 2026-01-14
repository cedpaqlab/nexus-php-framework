<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../config/loader.php';

use App\Database\Seeders\SeederRunner;

$seeder = $argv[1] ?? null;

$runner = new SeederRunner();

try {
    $runner->run($seeder);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
