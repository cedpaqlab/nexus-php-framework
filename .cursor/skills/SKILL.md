---
name: PHP Framework Development Skills
description: Collection of reusable skills for developing components in the Nexus PHP Framework
---

# Framework Development Skills

## Skill 1: Create PHP Controller

When creating a new Controller:

1. **Location**: Place in `app/Http/Controllers/{Domain}/` following domain structure
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

class {Name}Controller
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Response $response
    ) {
    }

    public function index(Request $request): Response
    {
        // Implementation
        return $this->response->json(['data' => []]);
    }
}
```

4. **Register in Container**: Add binding in `bootstrap/app.php` if needed
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

4. **Register in Container**: Add singleton binding in `bootstrap/app.php`
5. **Create Tests**: Add corresponding test in `tests/framework/Services/{Domain}/`

---

## Skill 3: Create Repository with QueryBuilder

When creating a new Repository:

1. **Location**: Place in `app/Repositories/{Domain}/` following domain structure
2. **Structure**:
   - Inject `Connection` via constructor (use `Connection::getInstance()`)
   - Use `QueryBuilder` for all queries (prepared statements)
   - Wrap multi-step operations in `Transaction`
   - Return domain objects or arrays
   - Handle PDO exceptions properly

3. **Example Pattern**:
```php
<?php

declare(strict_types=1);

namespace App\Repositories\{Domain};

use App\Repositories\Database\Connection;
use App\Repositories\Database\QueryBuilder;
use App\Repositories\Database\Transaction;

class {Name}Repository
{
    private QueryBuilder $query;

    public function __construct()
    {
        $this->query = new QueryBuilder(Connection::getInstance());
    }

    public function findById(int $id): ?array
    {
        return $this->query
            ->table('{table_name}')
            ->where('id', '=', $id)
            ->first();
    }

    public function create(array $data): int
    {
        return Transaction::execute(function () use ($data) {
            return $this->query
                ->table('{table_name}')
                ->insert($data);
        });
    }
}
```

4. **Create Tests**: Add corresponding test in `tests/framework/Repositories/{Domain}/`

---

## Skill 4: Create Request Validation

When creating a new Request validation:

1. **Location**: Place in `app/Http/Requests/{Domain}/` following domain structure
2. **Structure**:
   - Extend `BaseRequest`
   - Implement `rules()` method returning validation rules array
   - Use `validated()` method to get validated data
   - Validation errors automatically return 422 Response

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

## Skill 5: Create Middleware

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

4. **Register in Router**: Use in route definitions or route groups
5. **Create Tests**: Add corresponding test in `tests/framework/Http/Middlewares/`

---

## Skill 6: Create Database Migration

When creating a new migration:

1. **Location**: Place in `database/migrations/`
2. **Naming**: `{timestamp}_{description}.php` (e.g., `20240114120000_create_users_table.php`)
3. **Structure**:
   - Use `QueryBuilder` for all operations
   - Use `Transaction` for multi-step operations
   - Include both `up()` and `down()` methods
   - Use prepared statements (via QueryBuilder)
   - Add proper indexes

4. **Example Pattern**:
```php
<?php

declare(strict_types=1);

use App\Repositories\Database\Connection;
use App\Repositories\Database\QueryBuilder;
use App\Repositories\Database\Transaction;

class {Name}Migration
{
    private QueryBuilder $query;

    public function __construct()
    {
        $this->query = new QueryBuilder(Connection::getInstance());
    }

    public function up(): void
    {
        Transaction::execute(function () {
            $this->query->raw("
                CREATE TABLE {table_name} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    column1 VARCHAR(255) NOT NULL,
                    column2 INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_column1 (column1)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        });
    }

    public function down(): void
    {
        Transaction::execute(function () {
            $this->query->raw("DROP TABLE IF EXISTS {table_name}");
        });
    }
}
```

5. **Security**: Always use prepared statements, never concatenate user input
