<?php

declare(strict_types=1);

namespace Tests\Framework\Database\Migrations;

use Tests\Support\DatabaseTestCase;
use Database\Migrations\CreateProductsTable;
use PDO;

class CreateProductsTableTest extends DatabaseTestCase
{
    private CreateProductsTable $migration;

    protected function setUp(): void
    {
        parent::setUp();
        
        $migrationFile = __DIR__ . '/../../../../database/migrations/20240101000002_CreateProductsTable.php';
        require_once $migrationFile;
        
        $this->migration = new CreateProductsTable();
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        parent::tearDown();
    }

    public function testUpCreatesProductsTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'products'")->fetch();
        $this->assertNotFalse($result);
    }

    public function testUpCreatesTableWithCorrectColumns(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $columns = $this->pdo->query("DESCRIBE products")->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('id', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('description', $columnNames);
        $this->assertContains('price', $columnNames);
        $this->assertContains('stock', $columnNames);
        $this->assertContains('status', $columnNames);
        $this->assertContains('created_at', $columnNames);
        $this->assertContains('updated_at', $columnNames);
    }

    public function testUpCreatesIndexes(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $indexes = $this->pdo->query("SHOW INDEX FROM products")->fetchAll(\PDO::FETCH_ASSOC);
        $indexNames = array_unique(array_column($indexes, 'Key_name'));
        
        $this->assertContains('idx_name', $indexNames);
        $this->assertContains('idx_status', $indexNames);
    }

    public function testUpSetsStatusEnumValues(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $statusColumn = array_filter($columns, fn($col) => $col['Field'] === 'status');
        $statusColumn = reset($statusColumn);
        $this->assertStringContainsString("enum('active','inactive')", strtolower($statusColumn['Type']));
    }

    public function testUpSetsDefaultStatusToActive(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $statusColumn = array_filter($columns, fn($col) => $col['Field'] === 'status');
        $statusColumn = reset($statusColumn);
        $this->assertEquals('active', $statusColumn['Default']);
    }

    public function testUpSetsDefaultStockToZero(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stockColumn = array_filter($columns, fn($col) => $col['Field'] === 'stock');
        $stockColumn = reset($stockColumn);
        $this->assertEquals('0', $stockColumn['Default']);
    }

    public function testUpEnforcesPricePrecision(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        
        $this->migration->up($this->pdo);
        
        $stmt = $this->pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $priceColumn = array_filter($columns, fn($col) => $col['Field'] === 'price');
        $priceColumn = reset($priceColumn);
        $this->assertStringContainsString('decimal(10,2)', strtolower($priceColumn['Type']));
    }

    public function testDownDropsProductsTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS products");
        $this->migration->up($this->pdo);
        
        $this->migration->down($this->pdo);
        
        $result = $this->pdo->query("SHOW TABLES LIKE 'products'")->fetch();
        $this->assertFalse($result);
    }
}
