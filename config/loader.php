<?php

declare(strict_types=1);

// Load Config class (now in Config namespace)
require_once __DIR__ . '/Config.php';

// Backward compatibility: create global Config alias if not using namespace
if (!class_exists('Config', false)) {
    class_alias('Config\Config', 'Config');
}
