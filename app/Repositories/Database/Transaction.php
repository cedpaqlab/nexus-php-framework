<?php

declare(strict_types=1);

namespace App\Repositories\Database;

use PDO;

class Transaction
{
    private readonly PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    public function execute(callable $callback): mixed
    {
        $this->begin();

        try {
            $result = $callback($this->pdo);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
