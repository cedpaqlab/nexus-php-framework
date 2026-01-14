<?php

declare(strict_types=1);

spl_autoload_register(function (string $className): void {
    // Initialize Propel before loading App\Models classes
    // Only if Config is already loaded (needed by PropelInitializer)
    if (strpos($className, 'App\\Models\\') === 0) {
        static $propelInitialized = false;
        if (!$propelInitialized && class_exists('Config') && file_exists(__DIR__ . '/propel.php')) {
            require_once __DIR__ . '/propel.php';
            $propelInitialized = true;
        }
    }

    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }

    $relativeClass = substr($className, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
