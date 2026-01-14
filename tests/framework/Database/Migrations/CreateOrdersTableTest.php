<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\DatabaseTestCase;
use Database\Migrations\CreateOrdersTable;
use Database\Migrations\CreateUsersTable;
use Database\Migrations\CreateProductsTable;
use PDO;

class CreateOrdersTableTest extends DatabaseTestCase
{
    private CreateOrdersTable $migration;

    protected function setUp(): void
    {
        parent::setUp();
        
        $migrationFile = __DIR__ . '/../../../../database/migrations/20240101000003_CreateOrdersTable.php';
        require_once $migrationFile;
        
        $usersMigrationFile = __DIR__ . '/../../../../database/migrations/20240101000001_CreateUsersTable.php';
        require_once $usersMigrationFile;
        
        $productsMigrationFile = __DIR__ . '/../../../../database/migrations/20240101000002_CreateProductsTable.php';
        require_once $productsMigrationFile;
        
        $this->migration = new CreateOrdersTable();
        
        // Create dependencies
        $usersMigration = new CreateUsersTable();
        $usersMigration->up($this->pdo);
        
        $productsMigration = new CreateProductsTable();
        $productsMigration->up($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        parent::tearDown();
    }

    public function testUpCreatesOrdersTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'orders'")->fetch();
        $this->assertNotFalse($result);
    }

    public function testUpCreatesTableWithCorrectColumns(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $columns = $this->pdo->query("DESCRIBE orders")->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('id', $columnNames);
        $this->assertContains('user_id', $columnNames);
        $this->assertContains('product_id', $columnNames);
        $this->assertContains('quantity', $columnNames);
        $this->assertContains('total_price', $columnNames);
        $this->assertContains('status', $columnNames);
        $this->assertContains('created_at', $columnNames);
        $this->assertContains('updated_at', $columnNames);
    }

    public function testUpCreatesIndexes(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $indexes = $this->pdo->query("SHOW INDEX FROM orders")->fetchAll(\PDO::FETCH_ASSOC);
        $indexNames = array_unique(array_column($indexes, 'Key_name'));
        
        $this->assertContains('idx_user_id', $indexNames);
        $this->assertContains('idx_product_id', $indexNames);
        $this->assertContains('idx_status', $indexNames);
    }

    public function testUpSetsStatusEnumValues(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $statusColumn = array_filter($columns, fn($col) => $col['Field'] === 'status');
        $statusColumn = reset($statusColumn);
        $this->assertStringContainsString("enum('pending','completed','cancelled')", strtolower($statusColumn['Type']));
    }

    public function testUpSetsDefaultStatusToPending(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $statusColumn = array_filter($columns, fn($col) => $col['Field'] === 'status');
        $statusColumn = reset($statusColumn);
        $this->assertEquals('pending', $statusColumn['Default']);
    }

    public function testUpCreatesForeignKeyToUsers(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'orders'
            AND COLUMN_NAME = 'user_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $foreignKey = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($foreignKey);
        $this->assertEquals('users', $foreignKey['REFERENCED_TABLE_NAME']);
        $this->assertEquals('id', $foreignKey['REFERENCED_COLUMN_NAME']);
    }

    public function testUpCreatesForeignKeyToProducts(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'orders'
            AND COLUMN_NAME = 'product_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $foreignKey = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($foreignKey);
        $this->assertEquals('products', $foreignKey['REFERENCED_TABLE_NAME']);
        $this->assertEquals('id', $foreignKey['REFERENCED_COLUMN_NAME']);
    }

    public function testUpEnforcesForeignKeyCascadeDelete(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        // Insert test data
        $this->pdo->exec("INSERT INTO users (email, password, name) VALUES ('test@example.com', 'hash', 'Test User')");
        $this->pdo->exec("INSERT INTO products (name, price) VALUES ('Test Product', 10.00)");
        $userId = $this->pdo->lastInsertId();
        $productId = $this->pdo->lastInsertId();
        
        $this->pdo->exec("INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES ($userId, $productId, 1, 10.00)");
        
        // Delete user should cascade delete order
        $this->pdo->exec("DELETE FROM users WHERE id = $userId");
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = $userId");
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testUpEnforcesPricePrecision(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $priceColumn = array_filter($columns, fn($col) => $col['Field'] === 'total_price');
        $priceColumn = reset($priceColumn);
        $this->assertStringContainsString('decimal(10,2)', strtolower($priceColumn['Type']));
    }

    public function testDownDropsOrdersTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS orders");
        $this->migration->up($this->pdo);
        
        $this->migration->down($this->pdo);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'orders'")->fetch();
        $this->assertFalse($result);
    }
}
