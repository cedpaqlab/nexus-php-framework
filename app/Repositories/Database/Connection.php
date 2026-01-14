<?php

declare(strict_types=1);

namespace App\Repositories\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;
    private static string $connection = 'mysql';

    public static function setConnection(string $connection): void
    {
        self::$connection = $connection;
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::create();
        }

        return self::$instance;
    }

    private static function create(): PDO
    {
        $config = require __DIR__ . '/../../../config/database.php';
        $connection = $config['connections'][self::$connection] ?? $config['connections']['mysql'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $connection['host'],
            $connection['port'],
            $connection['database'],
            $connection['charset']
        );

        try {
            $pdo = new PDO(
                $dsn,
                $connection['username'],
                $connection['password'],
                $connection['options']
            );

            return $pdo;
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
