<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use App\Repositories\Database\Connection;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure PHPUnit environment variables are loaded first
        if (getenv('DB_TEST_DATABASE')) {
            $_ENV['DB_TEST_DATABASE'] = getenv('DB_TEST_DATABASE');
            $_SERVER['DB_TEST_DATABASE'] = getenv('DB_TEST_DATABASE');
        }
        if (getenv('DB_TEST_USERNAME')) {
            $_ENV['DB_TEST_USERNAME'] = getenv('DB_TEST_USERNAME');
            $_SERVER['DB_TEST_USERNAME'] = getenv('DB_TEST_USERNAME');
        }
        if (getenv('DB_TEST_PASSWORD')) {
            $_ENV['DB_TEST_PASSWORD'] = getenv('DB_TEST_PASSWORD');
            $_SERVER['DB_TEST_PASSWORD'] = getenv('DB_TEST_PASSWORD');
        }
        if (getenv('DB_TEST_HOST')) {
            $_ENV['DB_TEST_HOST'] = getenv('DB_TEST_HOST');
            $_SERVER['DB_TEST_HOST'] = getenv('DB_TEST_HOST');
        }
        if (getenv('DB_TEST_PORT')) {
            $_ENV['DB_TEST_PORT'] = getenv('DB_TEST_PORT');
            $_SERVER['DB_TEST_PORT'] = getenv('DB_TEST_PORT');
        }
        
        // Load environment variables from .env if not already loaded
        if (!isset($_ENV['DB_HOST'])) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), '#')) {
                        continue;
                    }
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                        putenv(sprintf('%s=%s', $key, $value));
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    }
                }
            }
        }
        
        // Load config if not already loaded
        if (!class_exists('Config')) {
            require_once __DIR__ . '/../../config/loader.php';
        }
        
        // Clear config cache to force reload with new env vars
        \Config\Config::clearCache();
        
        // Use testing database connection for tests
        Connection::reset();
        Connection::setConnection('testing');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
