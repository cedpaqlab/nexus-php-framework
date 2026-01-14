<?php

declare(strict_types=1);

namespace App\Repositories\Database;

use Config;
use PDO;
use PDOException;
use App\Exceptions\DatabaseConnectionException;

class Connection
{
    private static ?PDO $instance = null;
    private static string $connection = 'mysql';
    private ?PDO $pdo = null;
    private readonly string $connectionName;

    public function __construct(?string $connectionName = null)
    {
        $this->connectionName = $connectionName ?? self::$connection;
    }

    public static function setConnection(string $connection): void
    {
        self::$connection = $connection;
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $connection = new self();
            self::$instance = $connection->getPdo();
        }

        return self::$instance;
    }

    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->create();
        }

        return $this->pdo;
    }

    private function create(): PDO
    {
        require_once __DIR__ . '/../../../config/loader.php';
        $config = Config::get('database.connections');
        $connection = $config[$this->connectionName] ?? $config['mysql'];

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
            throw new DatabaseConnectionException("Database connection failed: " . $e->getMessage(), $e);
        }
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
