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
        $this->pdo = Connection::getInstance();
        $this->transaction = new Transaction($this->pdo);
        $this->transaction->begin();
    }

    protected function tearDown(): void
    {
        $this->transaction->rollback();
        parent::tearDown();
    }
}
