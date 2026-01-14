# Database Connectors

The framework supports multiple database connectors through a modular architecture. You can switch between connectors without changing your repository code.

## Available Connectors

### 1. QueryBuilder (Default)
- **Type**: Custom QueryBuilder
- **Status**: Built-in, always available
- **Use case**: Simple, lightweight, full control

### 2. Propel ORM
- **Type**: ORM (Object-Relational Mapping)
- **Status**: Optional (requires installation)
- **Use case**: Learning Propel, complex relationships, code generation

## Configuration

Set the connector in `.env`:

```env
DB_CONNECTOR=querybuilder  # or 'propel'
```

Or in `config/database.php`:

```php
'connector' => 'querybuilder', // or 'propel'
```

## Installation

### QueryBuilder (Default)
No installation needed - it's built-in.

### Propel (Optional - for learning)
**Note**: Propel 2.0 is currently in beta, but it's the only version compatible with PHP 8.2+.

```bash
composer require --dev propel/propel:^2.0@beta --with-all-dependencies
```

**Why beta?**
- Propel 1.x (stable) doesn't support PHP 8.2+
- Propel 2.0 stable doesn't exist yet
- Beta is fine for learning/development, not recommended for production

If you prefer to avoid beta, stick with `querybuilder` connector.

## Usage

### In Repositories

```php
<?php

namespace App\Repositories\User;

use App\Repositories\Contracts\DatabaseConnectorInterface;
use App\Repositories\Factory\ConnectorFactory;

class UserRepository
{
    private DatabaseConnectorInterface $connector;

    public function __construct(?DatabaseConnectorInterface $connector = null)
    {
        // Automatically uses configured connector
        $this->connector = $connector ?? ConnectorFactory::create();
    }

    public function findById(int $id): ?array
    {
        return $this->connector->find('users', $id);
    }
}
```

### Via Dependency Injection

```php
// In Controller or Service
public function __construct(
    private DatabaseConnectorInterface $connector
) {
}
```

## Switching Connectors

Simply change the `DB_CONNECTOR` environment variable:

```env
# Use QueryBuilder
DB_CONNECTOR=querybuilder

# Use Propel
DB_CONNECTOR=propel
```

Your repositories will automatically use the new connector without code changes!

## Architecture

```
DatabaseConnectorInterface (Contract)
    ├── QueryBuilderConnector (Implementation)
    └── PropelConnector (Implementation)
    
ConnectorFactory (Creates connector based on config)
```

## Benefits

- **Decoupled**: Framework doesn't depend on Propel
- **Swappable**: Change connector via configuration
- **Testable**: Easy to mock connectors
- **Extensible**: Add new connectors easily
