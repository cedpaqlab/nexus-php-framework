<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Database\Connection;
use App\Repositories\Database\QueryBuilder;
use App\Repositories\Database\Transaction;

class QueryBuilderConnector implements DatabaseConnectorInterface
{
    private QueryBuilder $query;
    private Transaction $transaction;

    public function __construct(Connection $connection)
    {
        $pdo = $connection->getPdo();
        $this->query = new QueryBuilder($pdo);
        $this->transaction = new Transaction($pdo);
    }

    public function find(string $table, int $id): ?array
    {
        return $this->query
            ->table($table)
            ->where('id', '=', $id)
            ->first();
    }

    public function findWhere(string $table, array $conditions): ?array
    {
        $query = $this->query->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }

        return $query->first();
    }

    public function findAll(string $table, array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $query = $this->query->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }

        if (!empty($orderBy)) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function create(string $table, array $data): int
    {
        return $this->query
            ->table($table)
            ->insert($data);
    }

    public function update(string $table, array $data, array $conditions): int
    {
        $query = $this->query->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }

        return $query->update($data);
    }

    public function delete(string $table, array $conditions): int
    {
        $query = $this->query->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }

        return $query->delete();
    }

    public function count(string $table, array $conditions = []): int
    {
        $query = $this->query->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }

        return $query->count();
    }

    public function beginTransaction(): void
    {
        $this->transaction->begin();
    }

    public function commit(): void
    {
        $this->transaction->commit();
    }

    public function rollback(): void
    {
        $this->transaction->rollback();
    }

    public function executeInTransaction(callable $callback): mixed
    {
        return $this->transaction->execute($callback);
    }
}
