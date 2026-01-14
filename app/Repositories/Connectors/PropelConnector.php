<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\ServiceContainer\ServiceContainerInterface;

class PropelConnector implements DatabaseConnectorInterface
{
    private \Propel\Runtime\Connection\ConnectionInterface $connection;

    public function __construct()
    {
        $this->initializePropel();
        $this->connection = Propel::getConnection();
    }

    private function initializePropel(): void
    {
        try {
            $serviceContainer = Propel::getServiceContainer();
        } catch (\Throwable $e) {
            Propel::init();
            $serviceContainer = Propel::getServiceContainer();
        }

        if ($serviceContainer->hasConnectionManager('default')) {
            return;
        }

        $serviceContainer->setAdapterClass('default', '\\Propel\\Runtime\\Adapter\\Pdo\\MysqlAdapter');
        $serviceContainer->setDefaultDatasource('default');
        
        $manager = new ConnectionManagerSingle('default');
        
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') {
            $username = $_ENV['DB_TEST_USERNAME'] ?? $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_TEST_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';
        } else {
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
        }
        
        $manager->setConfiguration([
            'dsn' => $this->buildDsn(),
            'user' => $username,
            'password' => $password,
            'settings' => [
                'charset' => 'utf8mb4',
                'queries' => [
                    'utf8' => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
                ],
            ],
        ]);
        
        $serviceContainer->setConnectionManager($manager);
    }

    private function buildDsn(): string
    {
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') {
            $host = $_ENV['DB_TEST_HOST'] ?? $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = (int) ($_ENV['DB_TEST_PORT'] ?? $_ENV['DB_PORT'] ?? 3306);
            $database = $_ENV['DB_TEST_DATABASE'] ?? ($_ENV['DB_DATABASE'] ?? '') . '_test';
        } else {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = (int) ($_ENV['DB_PORT'] ?? 3306);
            $database = $_ENV['DB_DATABASE'] ?? '';
        }
        
        return sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
    }

    public function find(string $table, int $id): ?array
    {
        $query = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', $table);
        $stmt = $this->connection->prepare($query);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findWhere(string $table, array $conditions): ?array
    {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $query = sprintf('SELECT * FROM %s WHERE %s LIMIT 1', $table, implode(' AND ', $whereClause));
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findAll(string $table, array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $query = sprintf('SELECT * FROM %s', $table);
        
        if (!empty($whereClause)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClause);
        }
        
        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                $orderParts[] = "{$column} {$direction}";
            }
            $query .= ' ORDER BY ' . implode(', ', $orderParts);
        }
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT {$limit} OFFSET {$offset}";
        } elseif ($limit !== null) {
            $query .= " LIMIT {$limit}";
        } elseif ($offset !== null) {
            $query .= " LIMIT 18446744073709551615 OFFSET {$offset}";
        }
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute($data);
        
        return (int) $this->connection->lastInsertId();
    }

    public function update(string $table, array $data, array $conditions): int
    {
        $setClause = [];
        $whereClause = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = :set_{$column}";
            $params["set_{$column}"] = $value;
        }
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = :where_{$column}";
            $params["where_{$column}"] = $value;
        }
        
        $query = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setClause),
            implode(' AND ', $whereClause)
        );
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    public function delete(string $table, array $conditions): int
    {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $query = sprintf('DELETE FROM %s WHERE %s', $table, implode(' AND ', $whereClause));
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    public function count(string $table, array $conditions = []): int
    {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }
        
        $query = sprintf('SELECT COUNT(*) as count FROM %s', $table);
        
        if (!empty($whereClause)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int) ($result['count'] ?? 0);
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollBack();
    }

    public function executeInTransaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
