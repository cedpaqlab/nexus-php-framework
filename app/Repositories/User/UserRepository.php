<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Repositories\Connectors\PropelConnector;
use App\Models\User;

class UserRepository
{
    public function __construct(
        private PropelConnector $connector
    ) {
    }

    public function findById(int $id): ?array
    {
        $user = $this->connector->findUserById($id);
        return $user ? $this->toArray($user) : null;
    }

    public function findByEmail(string $email): ?array
    {
        $user = $this->connector->findUserByEmail($email);
        return $user ? $this->toArray($user) : null;
    }

    public function findAll(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $users = $this->connector->findAllUsers($conditions, $orderBy, $limit, $offset);
        return array_map([$this, 'toArray'], $users);
    }

    public function create(array $data): int
    {
        return $this->connector->executeInTransaction(function () use ($data) {
            $user = $this->connector->createUser($data);
            return $user->getId();
        });
    }

    public function update(int $id, array $data): int
    {
        return $this->connector->executeInTransaction(function () use ($id, $data) {
            $user = $this->getUserOrFail($id);
            $this->connector->updateUser($user, $data);
            return 1;
        });
    }

    public function delete(int $id): int
    {
        return $this->connector->executeInTransaction(function () use ($id) {
            $user = $this->getUserOrFail($id);
            $this->connector->deleteUser($user);
            return 1;
        });
    }

    public function count(array $conditions = []): int
    {
        return $this->connector->countUsers($conditions);
    }

    private function getUserOrFail(int $id): User
    {
        $user = $this->connector->findUserById($id);
        if ($user === null) {
            throw new \RuntimeException("User with ID {$id} not found");
        }
        return $user;
    }

    private function toArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
