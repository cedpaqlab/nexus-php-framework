<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\TestCase;
use App\Repositories\Connectors\PropelConnector;
use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Database\Connection;

class PropelConnectorTest extends TestCase
{
    private PropelConnector $connector;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!class_exists(\Propel\Runtime\Propel::class)) {
            $this->markTestSkipped('Propel is not installed');
            return;
        }

        try {
            $this->connector = new PropelConnector();
            $this->createTestTable();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Propel connector initialization failed: ' . $e->getMessage());
            return;
        }
    }

    private function createTestTable(): void
    {
        try {
            $pdo = Connection::getInstance();
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS test_propel_users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    age INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }

    public function testImplementsDatabaseConnectorInterface(): void
    {
        $this->assertInstanceOf(DatabaseConnectorInterface::class, $this->connector);
    }

    public function testFind(): void
    {
        $id = $this->connector->create('test_propel_users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $result = $this->connector->find('test_propel_users', $id);

        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals(30, (int) $result['age']);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $result = $this->connector->find('test_propel_users', 99999);
        $this->assertNull($result);
    }

    public function testFindWhere(): void
    {
        $this->connector->create('test_propel_users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'age' => 25,
        ]);

        $result = $this->connector->findWhere('test_propel_users', [
            'email' => 'jane@example.com',
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('Jane Doe', $result['name']);
    }

    public function testFindWhereReturnsNullWhenNotFound(): void
    {
        $result = $this->connector->findWhere('test_propel_users', [
            'email' => 'nonexistent@example.com',
        ]);
        $this->assertNull($result);
    }

    public function testFindAll(): void
    {
        $this->connector->create('test_propel_users', [
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'age' => 20,
        ]);

        $this->connector->create('test_propel_users', [
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'age' => 25,
        ]);

        $results = $this->connector->findAll('test_propel_users');

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results));
    }

    public function testFindAllWithConditions(): void
    {
        $this->connector->create('test_propel_users', [
            'name' => 'Filtered User',
            'email' => 'filtered@example.com',
            'age' => 30,
        ]);

        $results = $this->connector->findAll('test_propel_users', [
            'age' => 30,
        ]);

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertEquals(30, (int) $result['age']);
        }
    }

    public function testFindAllWithOrderBy(): void
    {
        $this->connector->create('test_propel_users', [
            'name' => 'User A',
            'email' => 'usera@example.com',
            'age' => 30,
        ]);

        $this->connector->create('test_propel_users', [
            'name' => 'User B',
            'email' => 'userb@example.com',
            'age' => 20,
        ]);

        $results = $this->connector->findAll('test_propel_users', [], ['age' => 'ASC']);

        $this->assertNotEmpty($results);
        if (count($results) >= 2) {
            $this->assertLessThanOrEqual((int) $results[1]['age'], (int) $results[0]['age']);
        }
    }

    public function testFindAllWithLimit(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->connector->create('test_propel_users', [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'age' => 20 + $i,
            ]);
        }

        $results = $this->connector->findAll('test_propel_users', [], [], 2);

        $this->assertLessThanOrEqual(2, count($results));
    }

    public function testFindAllWithOffset(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->connector->create('test_propel_users', [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'age' => 20 + $i,
            ]);
        }

        $allResults = $this->connector->findAll('test_propel_users');
        $offsetResults = $this->connector->findAll('test_propel_users', [], [], null, 2);

        $this->assertLessThanOrEqual(count($allResults) - 2, count($offsetResults));
    }

    public function testCreate(): void
    {
        $id = $this->connector->create('test_propel_users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'age' => 28,
        ]);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertEquals('New User', $result['name']);
    }

    public function testUpdate(): void
    {
        $id = $this->connector->create('test_propel_users', [
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30,
        ]);

        $affected = $this->connector->update('test_propel_users', [
            'name' => 'Updated Name',
        ], [
            'id' => $id,
        ]);

        $this->assertEquals(1, $affected);

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertEquals('Updated Name', $result['name']);
    }

    public function testUpdateWithMultipleConditions(): void
    {
        $id = $this->connector->create('test_propel_users', [
            'name' => 'Multi Condition',
            'email' => 'multi@example.com',
            'age' => 25,
        ]);

        $affected = $this->connector->update('test_propel_users', [
            'age' => 26,
        ], [
            'id' => $id,
            'email' => 'multi@example.com',
        ]);

        $this->assertEquals(1, $affected);

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertEquals(26, (int) $result['age']);
    }

    public function testDelete(): void
    {
        $id = $this->connector->create('test_propel_users', [
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'age' => 30,
        ]);

        $affected = $this->connector->delete('test_propel_users', [
            'id' => $id,
        ]);

        $this->assertEquals(1, $affected);

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertNull($result);
    }

    public function testDeleteWithMultipleConditions(): void
    {
        $id = $this->connector->create('test_propel_users', [
            'name' => 'Multi Delete',
            'email' => 'multidelete@example.com',
            'age' => 25,
        ]);

        $affected = $this->connector->delete('test_propel_users', [
            'id' => $id,
            'email' => 'multidelete@example.com',
        ]);

        $this->assertEquals(1, $affected);
    }

    public function testCount(): void
    {
        $this->connector->create('test_propel_users', [
            'name' => 'Count User 1',
            'email' => 'count1@example.com',
            'age' => 20,
        ]);

        $this->connector->create('test_propel_users', [
            'name' => 'Count User 2',
            'email' => 'count2@example.com',
            'age' => 25,
        ]);

        $count = $this->connector->count('test_propel_users');
        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function testCountWithConditions(): void
    {
        $this->connector->create('test_propel_users', [
            'name' => 'Filtered Count',
            'email' => 'filteredcount@example.com',
            'age' => 30,
        ]);

        $count = $this->connector->count('test_propel_users', [
            'age' => 30,
        ]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testBeginTransaction(): void
    {
        $this->connector->beginTransaction();

        $id = $this->connector->create('test_propel_users', [
            'name' => 'Transaction User',
            'email' => 'transaction@example.com',
            'age' => 30,
        ]);

        $this->connector->rollback();

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertNull($result);
    }

    public function testCommit(): void
    {
        $this->connector->beginTransaction();

        $id = $this->connector->create('test_propel_users', [
            'name' => 'Committed User',
            'email' => 'committed@example.com',
            'age' => 30,
        ]);

        $this->connector->commit();

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertNotNull($result);
        $this->assertEquals('Committed User', $result['name']);
    }

    public function testExecuteInTransactionCommitsOnSuccess(): void
    {
        $id = $this->connector->executeInTransaction(function () {
            return $this->connector->create('test_propel_users', [
                'name' => 'Callback User',
                'email' => 'callback@example.com',
                'age' => 30,
            ]);
        });

        $result = $this->connector->find('test_propel_users', $id);
        $this->assertNotNull($result);
        $this->assertEquals('Callback User', $result['name']);
    }

    public function testExecuteInTransactionRollsBackOnFailure(): void
    {
        try {
            $this->connector->executeInTransaction(function () {
                $id = $this->connector->create('test_propel_users', [
                    'name' => 'Failed User',
                    'email' => 'failed@example.com',
                    'age' => 30,
                ]);

                throw new \RuntimeException('Test exception');
            });
        } catch (\RuntimeException $e) {
            // Expected exception
        }

        $results = $this->connector->findAll('test_propel_users', [
            'email' => 'failed@example.com',
        ]);

        $this->assertEmpty($results);
    }
}
