<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use App\Models\User;
use App\Models\UserQuery;
use App\Repositories\Connectors\PropelInitializer;
use Propel\Runtime\Propel;
use Propel\Runtime\Exception\PropelException;

class PropelConnector
{
    public function __construct()
    {
        PropelInitializer::initialize();
    }

    public function findUserById(int $id): ?User
    {
        try {
            return UserQuery::create()->findPk($id);
        } catch (PropelException $e) {
            return null;
        }
    }

    public function findUserByEmail(string $email): ?User
    {
        try {
            return UserQuery::create()->findOneByEmail($email);
        } catch (PropelException $e) {
            return null;
        }
    }

    public function findAllUsers(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        try {
            $query = $this->buildQuery($conditions, $orderBy, $limit, $offset);
            return $query->find()->toArray();
        } catch (PropelException $e) {
            return [];
        }
    }

    public function createUser(array $data): User
    {
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setName($data['name']);
        $user->setRole($data['role'] ?? 'user');
        $user->save();

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }
        $user->save();

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function countUsers(array $conditions = []): int
    {
        try {
            $query = $this->buildQuery($conditions, [], null, null);
            return $query->count();
        } catch (PropelException $e) {
            return 0;
        }
    }

    public function beginTransaction(): void
    {
        Propel::getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        Propel::getConnection()->commit();
    }

    public function rollback(): void
    {
        Propel::getConnection()->rollBack();
    }

    public function executeInTransaction(callable $callback): mixed
    {
        $connection = Propel::getConnection();
        $connection->beginTransaction();

        try {
            $result = $callback();
            $connection->commit();
            return $result;
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    private function buildQuery(array $conditions, array $orderBy, ?int $limit, ?int $offset): UserQuery
    {
        $query = UserQuery::create();

        foreach ($conditions as $column => $value) {
            $method = 'filterBy' . $this->camelize($column);
            if (method_exists($query, $method)) {
                $query->$method($value);
            }
        }

        foreach ($orderBy as $column => $direction) {
            $method = 'orderBy' . $this->camelize($column);
            if (method_exists($query, $method)) {
                $query->$method($direction);
            }
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query;
    }

    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
