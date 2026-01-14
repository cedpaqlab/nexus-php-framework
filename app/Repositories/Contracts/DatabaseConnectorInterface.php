<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface DatabaseConnectorInterface
{
    public function find(string $table, int $id): ?array;

    public function findWhere(string $table, array $conditions): ?array;

    public function findAll(string $table, array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array;

    public function create(string $table, array $data): int;

    public function update(string $table, array $data, array $conditions): int;

    public function delete(string $table, array $conditions): int;

    public function count(string $table, array $conditions = []): int;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function executeInTransaction(callable $callback): mixed;
}
