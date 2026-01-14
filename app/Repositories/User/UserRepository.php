<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;

class UserRepository
{
    private DatabaseConnectorInterface $connector;
    private string $table = 'users';

    public function __construct(?DatabaseConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? ConnectorFactory::create();
    }

    public function findById(int $id): ?array
    {
        return $this->connector->find($this->table, $id);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->connector->findWhere($this->table, ['email' => $email]);
    }

    public function findAll(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->connector->findAll($this->table, $conditions, $orderBy, $limit, $offset);
    }

    public function create(array $data): int
    {
        return $this->connector->executeInTransaction(function () use ($data) {
            return $this->connector->create($this->table, $data);
        });
    }

    public function update(int $id, array $data): int
    {
        return $this->connector->executeInTransaction(function () use ($id, $data) {
            return $this->connector->update($this->table, $data, ['id' => $id]);
        });
    }

    public function delete(int $id): int
    {
        return $this->connector->executeInTransaction(function () use ($id) {
            return $this->connector->delete($this->table, ['id' => $id]);
        });
    }

    public function count(array $conditions = []): int
    {
        return $this->connector->count($this->table, $conditions);
    }
}
