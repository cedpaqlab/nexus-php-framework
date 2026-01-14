<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Repositories\Database\Connection;
use App\Repositories\Database\Transaction;
use PDO;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->pdo = Connection::getInstance();
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
            return;
        }
        $this->transaction = new Transaction($this->pdo);
        $this->transaction->begin();
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->transaction->rollback();
        }
        parent::tearDown();
    }
}
