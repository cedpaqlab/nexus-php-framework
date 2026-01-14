<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Database\Transaction;
use App\Repositories\Database\Connection;
use PDO;

class TransactionTest extends TestCase
{
    private PDO $pdo;
    private Transaction $transaction;

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
        $this->createTestTable();
        $this->cleanTable();
    }

    private function cleanTable(): void
    {
        $this->pdo->exec("TRUNCATE TABLE test_transactions");
    }

    private function createTestTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS test_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL
            )
        ");
    }

    public function testTransactionCommit(): void
    {
        $this->transaction->begin();

        $stmt = $this->pdo->prepare("INSERT INTO test_transactions (name) VALUES (?)");
        $stmt->execute(['Test Name']);

        $this->transaction->commit();

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_transactions WHERE name = 'Test Name'");
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testTransactionRollback(): void
    {
        $this->transaction->begin();

        $stmt = $this->pdo->prepare("INSERT INTO test_transactions (name) VALUES (?)");
        $stmt->execute(['Rollback Test']);

        $this->transaction->rollback();

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_transactions WHERE name = 'Rollback Test'");
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testExecuteCallbackCommitsOnSuccess(): void
    {
        $result = $this->transaction->execute(function (PDO $pdo) {
            $stmt = $pdo->prepare("INSERT INTO test_transactions (name) VALUES (?)");
            $stmt->execute(['Execute Test']);
            return 'success';
        });

        $this->assertEquals('success', $result);

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_transactions WHERE name = 'Execute Test'");
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testExecuteCallbackRollsBackOnException(): void
    {
        try {
            $this->transaction->execute(function (PDO $pdo) {
                $stmt = $pdo->prepare("INSERT INTO test_transactions (name) VALUES (?)");
                $stmt->execute(['Exception Test']);
                throw new \RuntimeException('Test exception');
            });
        } catch (\RuntimeException $e) {
            $this->assertEquals('Test exception', $e->getMessage());
        }

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_transactions WHERE name = 'Exception Test'");
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count);
    }
}
