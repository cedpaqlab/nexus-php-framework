<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use Tests\Support\DatabaseTestCase;
use App\Repositories\Database\QueryBuilder;
use App\Repositories\Database\Connection;

class QueryBuilderTest extends DatabaseTestCase
{
    private QueryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new QueryBuilder($this->pdo);
        $this->createTestTable();
    }

    private function createTestTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS test_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                age INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function testInsert(): void
    {
        $id = $this->builder->table('test_users')->insert([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testSelect(): void
    {
        $this->builder->table('test_users')->insert([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'age' => 25,
        ]);

        $results = $this->builder->table('test_users')->get();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testWhere(): void
    {
        $this->builder->table('test_users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 20,
        ]);

        $result = $this->builder->table('test_users')
            ->where('email', '=', 'test@example.com')
            ->first();

        $this->assertNotNull($result);
        $this->assertEquals('Test User', $result['name']);
    }

    public function testWhereIn(): void
    {
        $this->builder->table('test_users')->insert([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'age' => 20,
        ]);

        $this->builder->table('test_users')->insert([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'age' => 25,
        ]);

        $results = $this->builder->table('test_users')
            ->whereIn('age', [20, 25])
            ->get();

        $this->assertCount(2, $results);
    }

    public function testUpdate(): void
    {
        $id = $this->builder->table('test_users')->insert([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30,
        ]);

        $affected = $this->builder->table('test_users')
            ->where('id', '=', $id)
            ->update(['name' => 'Updated Name']);

        $this->assertEquals(1, $affected);

        $result = $this->builder->table('test_users')
            ->where('id', '=', $id)
            ->first();

        $this->assertEquals('Updated Name', $result['name']);
    }

    public function testDelete(): void
    {
        $id = $this->builder->table('test_users')->insert([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'age' => 30,
        ]);

        $affected = $this->builder->table('test_users')
            ->where('id', '=', $id)
            ->delete();

        $this->assertEquals(1, $affected);

        $result = $this->builder->table('test_users')
            ->where('id', '=', $id)
            ->first();

        $this->assertNull($result);
    }

    public function testCount(): void
    {
        $this->builder->table('test_users')->insert([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'age' => 20,
        ]);

        $this->builder->table('test_users')->insert([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'age' => 25,
        ]);

        $count = $this->builder->table('test_users')->count();
        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function testOrderBy(): void
    {
        $this->builder->table('test_users')->insert([
            'name' => 'User A',
            'email' => 'usera@example.com',
            'age' => 30,
        ]);

        $this->builder->table('test_users')->insert([
            'name' => 'User B',
            'email' => 'userb@example.com',
            'age' => 20,
        ]);

        $results = $this->builder->table('test_users')
            ->orderBy('age', 'ASC')
            ->get();

        $this->assertNotEmpty($results);
        if (count($results) >= 2) {
            $this->assertLessThanOrEqual($results[1]['age'], $results[0]['age']);
        }
    }

    public function testLimit(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->builder->table('test_users')->insert([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'age' => 20 + $i,
            ]);
        }

        $results = $this->builder->table('test_users')
            ->limit(2)
            ->get();

        $this->assertLessThanOrEqual(2, count($results));
    }
}
