---
name: PHP Framework Development Skills
description: Collection of reusable skills for developing components in the Nexus PHP Framework
---

# Framework Development Skills

## Skill 1: Create PHP Controller

When creating a new Controller:

1. **Location**: Place in `app/Http/Controllers/` or `app/Http/Controllers/{Domain}/` following domain structure
2. **Structure**:
   - Use constructor injection for dependencies (ViewRenderer, Response, Services, Repositories)
   - Methods receive `Request $request` as first parameter
   - Return `Response` instance
   - Use fluent Response methods (json, html, redirect, etc.)

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\{Domain};

use App\Http\Request;
use App\Http\Response;
use App\Services\View\ViewRenderer;
use App\Services\{Domain}\{Name}Service;

class {Name}Controller
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response,
        private {Name}Service $service
    ) {
    }

    public function index(Request $request): Response
    {
        $data = $this->service->getAll();
        return $this->response->json(['data' => $data]);
    }
}
```

4. **Register in Container**: Add binding in `app/Providers/AppServiceProvider.php` if needed
5. **Add Route**: Define route in `routes/web.php`
6. **Create Tests**: Add corresponding test in `tests/framework/Http/Controllers/`

---

## Skill 2: Create Domain Service

When creating a new Service:

1. **Location**: Place in `app/Services/{Domain}/` following domain structure
2. **Structure**:
   - Single Responsibility Principle (one reason to change)
   - Constructor injection for dependencies (Repositories, other Services, Logger)
   - Use typed exceptions for error handling
   - Methods should be focused and testable

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Services\{Domain};

use App\Repositories\{Domain}\{Name}Repository;
use App\Services\Logger\Logger;
use App\Exceptions\{Domain}\{Name}Exception;

class {Name}Service
{
    public function __construct(
        private {Name}Repository $repository,
        private Logger $logger
    ) {
    }

    public function doSomething(string $param): mixed
    {
        // Implementation with validation, logging, error handling
        return $result;
    }
}
```

4. **Register in Container**: Add singleton binding in `app/Providers/AppServiceProvider.php`
5. **Create Tests**: Add corresponding test in `tests/framework/Services/{Domain}/`

---

## Skill 3: Create Repository with Propel ORM

**CRITICAL: Framework uses 100% Propel ORM. Never use QueryBuilder or raw SQL for data operations.**

When creating a new Repository:

1. **Location**: Place in `app/Repositories/{Domain}/` following domain structure
2. **Structure**:
   - Inject `PropelConnector` (or domain-specific connector) via constructor
   - All data operations go through Propel models
   - Use `executeInTransaction()` for multi-step operations
   - Convert Propel models to arrays using `toArray()` helper method
   - Never use QueryBuilder or raw SQL for data access

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Repositories\{Domain};

use App\Repositories\Connectors\{Name}Connector;
use App\Models\{Name};

class {Name}Repository
{
    public function __construct(
        private {Name}Connector $connector
    ) {
    }

    public function findById(int $id): ?array
    {
        $model = $this->connector->find{Name}ById($id);
        return $model ? $this->toArray($model) : null;
    }

    public function findAll(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $models = $this->connector->findAll{Name}s($conditions, $orderBy, $limit, $offset);
        return array_map([$this, 'toArray'], $models);
    }

    public function create(array $data): int
    {
        return $this->connector->executeInTransaction(function () use ($data) {
            $model = $this->connector->create{Name}($data);
            return $model->getId();
        });
    }

    public function update(int $id, array $data): int
    {
        return $this->connector->executeInTransaction(function () use ($id, $data) {
            $model = $this->get{Name}OrFail($id);
            $this->connector->update{Name}($model, $data);
            return 1;
        });
    }

    public function delete(int $id): int
    {
        return $this->connector->executeInTransaction(function () use ($id) {
            $model = $this->get{Name}OrFail($id);
            $this->connector->delete{Name}($model);
            return 1;
        });
    }

    private function get{Name}OrFail(int $id): {Name}
    {
        $model = $this->connector->find{Name}ById($id);
        if ($model === null) {
            throw new \RuntimeException("{Name} with ID {$id} not found");
        }
        return $model;
    }

    private function toArray({Name} $model): array
    {
        return [
            'id' => $model->getId(),
            // Map all properties
            'created_at' => $model->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $model->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
```

4. **Create Tests**: Add corresponding test in `tests/framework/Repositories/{Domain}/`

---

## Skill 4: Create Propel Connector

When creating a new Propel Connector:

1. **Location**: Place in `app/Repositories/Connectors/`
2. **Structure**:
   - Initialize Propel in constructor via `PropelInitializer::initialize()`
   - Use Propel Query classes (e.g., `UserQuery`, `ProductQuery`)
   - Use Propel Model classes (e.g., `User`, `Product`)
   - Wrap queries in `executeQuery()` for error handling
   - Use `executeInTransaction()` for write operations
   - Never use raw SQL or QueryBuilder

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Repositories\Connectors;

use App\Models\{Name};
use App\Models\{Name}Query;
use App\Repositories\Connectors\PropelInitializer;
use Propel\Runtime\Propel;
use Propel\Runtime\Exception\PropelException;

class {Name}Connector
{
    public function __construct()
    {
        PropelInitializer::initialize();
    }

    public function find{Name}ById(int $id): ?{Name}
    {
        return $this->executeQuery(fn() => {Name}Query::create()->findPk($id));
    }

    public function find{Name}By{Field}(string $value): ?{Name}
    {
        return $this->executeQuery(fn() => {Name}Query::create()->findOneBy{Field}($value));
    }

    public function findAll{Name}s(array $conditions = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        return $this->executeQuery(
            fn() => $this->buildQuery($conditions, $orderBy, $limit, $offset)->find()->getData(),
            []
        );
    }

    public function create{Name}(array $data): {Name}
    {
        $model = new {Name}();
        $model->set{Field}($data['field']);
        // Set all fields
        $model->save();
        return $model;
    }

    public function update{Name}({Name} $model, array $data): {Name}
    {
        if (isset($data['field'])) {
            $model->set{Field}($data['field']);
        }
        $model->save();
        return $model;
    }

    public function delete{Name}({Name} $model): void
    {
        $model->delete();
    }

    public function executeInTransaction(callable $callback): mixed
    {
        $connection = Propel::getConnection();
        $connection->beginTransaction();
        try {
            $result = $callback();
            $connection->commit();
            return $result;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    private function buildQuery(array $conditions, array $orderBy, ?int $limit, ?int $offset): {Name}Query
    {
        $query = {Name}Query::create();
        
        foreach ($conditions as $field => $value) {
            $method = 'filterBy' . ucfirst($field);
            if (method_exists($query, $method)) {
                $query->$method($value);
            }
        }
        
        foreach ($orderBy as $field => $direction) {
            $method = 'orderBy' . ucfirst($field);
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

    private function executeQuery(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (PropelException $e) {
            error_log("Propel error: " . $e->getMessage());
            return $default;
        }
    }
}
```

4. **Create Tests**: Add corresponding test in `tests/framework/Repositories/Connectors/`

---

## Skill 5: Use Propel ORM Models

When working with Propel models:

1. **Always use Propel Query classes for reads**:
```php
use App\Models\User;
use App\Models\UserQuery;

// Find by primary key
$user = UserQuery::create()->findPk(1);

// Find by unique field
$user = UserQuery::create()->findOneByEmail('user@example.com');

// Filter and order
$admins = UserQuery::create()
    ->filterByRole('admin')
    ->orderByCreatedAt('DESC')
    ->find();

// Count
$count = UserQuery::create()->filterByRole('user')->count();
```

2. **Always use Propel Model classes for writes**:
```php
// Create
$user = new User();
$user->setEmail('new@example.com');
$user->setPassword($hashedPassword);
$user->setName('New User');
$user->save();

// Update
$user = UserQuery::create()->findPk(1);
$user->setName('Updated Name');
$user->save();

// Delete
$user = UserQuery::create()->findPk(1);
$user->delete();
```

3. **Never use raw SQL or QueryBuilder for data operations**
4. **Use `getData()` on collections, not `toArray()`** (returns Collection of models)

---

## Skill 6: Create Request Validation

When creating a new Request validation:

1. **Location**: Place in `app/Http/Requests/{Domain}/` following domain structure
2. **Structure**:
   - Extend `BaseRequest`
   - Implement `rules()` method returning validation rules array
   - Use `validated()` method to get validated data
   - Validation errors automatically return 422 Response
   - Use whitelist approach (only allow specified fields)

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\{Domain};

use App\Http\Requests\BaseRequest;

class {Name}Request extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'field1' => ['required', 'string', 'min:3', 'max:255'],
            'field2' => ['required', 'email'],
            'field3' => ['numeric', 'min:0'],
            'field4' => ['required', 'in:value1,value2'],
        ];
    }
}
```

4. **Usage in Controller**:
```php
$request = new {Name}Request($request, $validator, $response);
$data = $request->validated(); // Returns array or sends 422 Response
```

5. **Create Tests**: Add corresponding test in `tests/framework/Http/Requests/{Domain}/`

---

## Skill 7: Create Middleware

When creating a new Middleware:

1. **Location**: Place in `app/Http/Middlewares/`
2. **Structure**:
   - Implement `MiddlewareInterface`
   - Inject `Response` via constructor
   - `handle()` method receives `Request` and `callable $next`
   - Return `Response` from `$next($request)` or error response

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Http\Response;
use App\Http\Middlewares\MiddlewareInterface;

class {Name}Middleware implements MiddlewareInterface
{
    public function __construct(
        private Response $response
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        // Pre-processing logic
        
        if (!$this->shouldProceed($request)) {
            return $this->response->forbidden('Access denied');
        }

        $response = $next($request);

        // Post-processing logic (optional)

        return $response;
    }

    private function shouldProceed(Request $request): bool
    {
        // Validation logic
        return true;
    }
}
```

4. **Register in Router**: Use in route definitions or route groups in `routes/web.php`
5. **Create Tests**: Add corresponding test in `tests/framework/Http/Middlewares/`

---

## Skill 8: Create Database Migration

When creating a new migration:

1. **Location**: Place in `database/migrations/`
2. **Naming**: `{timestamp}_{description}.php` (e.g., `20240114120000_create_users_table.php`)
3. **Structure**:
   - Implement `MigrationInterface`
   - Use direct PDO via `Connection::getInstance()` for DDL operations
   - Include both `up()` and `down()` methods
   - Use raw SQL for CREATE/ALTER/DROP (DDL operations)
   - Add proper indexes and foreign keys
   - Use transactions for multi-step operations

4. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Database\Migrations\MigrationInterface;
use App\Repositories\Database\Connection;
use PDO;

class {Name}Migration implements MigrationInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE {table_name} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                column1 VARCHAR(255) NOT NULL,
                column2 INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_column1 (column1)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {table_name}");
    }
}
```

5. **Security**: Migrations use DDL (CREATE/ALTER/DROP) - no user input involved
6. **Create Tests**: Add corresponding test in `tests/framework/Database/Migrations/`

---

## Skill 9: Create Database Seeder

When creating a new seeder:

1. **Location**: Place in `database/seeders/`
2. **Naming**: `{timestamp}_{description}.php` (e.g., `20240101000000_DefaultUsersSeeder.php`)
3. **Structure**:
   - Implement `SeederInterface`
   - Use `PropelConnector` (or domain-specific connector) for all data operations
   - Check for existing records before creating (update if exists)
   - Use Propel models, never raw SQL

4. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Database\Seeders\SeederInterface;
use App\Repositories\Connectors\PropelConnector;
use App\Services\Security\HashService;

class {Name}Seeder implements SeederInterface
{
    private HashService $hashService;

    public function __construct()
    {
        $this->hashService = new HashService();
    }

    public function run(PropelConnector $connector): void
    {
        $items = [
            [
                'field1' => 'value1',
                'field2' => 'value2',
            ],
        ];

        foreach ($items as $itemData) {
            $existing = $connector->find{Name}By{Field}($itemData['field']);
            
            if ($existing === null) {
                $connector->create{Name}($itemData);
            } else {
                // Update existing record
                $connector->update{Name}($existing, $itemData);
            }
        }
    }
}
```

5. **Create Tests**: Add corresponding test in `tests/framework/Database/Seeders/`

---

## Skill 10: Modify Propel Schema

When modifying the database schema:

1. **Location**: Edit `schema.xml` at project root
2. **Structure**:
   - Use Propel XML schema format
   - Use `LONGVARCHAR` instead of `TEXT` for compatibility
   - Define foreign keys and relationships
   - Use ENUM types for constrained values

3. **After modifying schema.xml**:
```bash
# Generate Propel configuration
vendor/bin/propel config:convert

# Generate models
vendor/bin/propel model:build --schema-dir=. --output-dir=app
```

4. **Important**: 
   - Generated files in `app/Models/Base/` are auto-generated - manual fixes will be overwritten
   - If ENUM handling needs fixes, re-apply after each `model:build`
   - Create migration for schema changes if needed

---

## Skill 11: Create PHPUnit Test

When creating a new test:

1. **Location**: Place in `tests/framework/{Category}/` matching app structure
2. **Structure**:
   - Extend `Tests\Support\TestCase`
   - Use `setUp()` and `tearDown()` for test isolation
   - Use database transactions or fresh database for each test
   - Follow AAA pattern (Arrange, Act, Assert)

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace Tests\Framework\{Category};

use Tests\Support\TestCase;
use App\{Category}\{Name};

class {Name}Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup test data
    }

    public function testSomething(): void
    {
        // Arrange
        $input = 'value';
        
        // Act
        $result = $this->subject->method($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

4. **Run tests**: `vendor/bin/phpunit` or `composer test`

---

## Key Principles

1. **100% Propel ORM**: Never use QueryBuilder or raw SQL for data operations. Only use direct PDO for DDL in migrations.

2. **Architecture**: Controller → Service → Repository → Connector (Propel) → Database

3. **SOLID Principles**: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion

4. **Security**: 
   - CSRF protection on all forms
   - Input validation with whitelist approach
   - Password hashing (bcrypt)
   - SQL injection protection (Propel ORM)
   - XSS protection (input sanitization)

5. **Code Quality**: DRY, KISS, YAGNI, "Sur la coche" (all quality rules applied)

6. **Testing**: Comprehensive test coverage, TDD when appropriate
