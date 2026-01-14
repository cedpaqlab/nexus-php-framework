# PHP Framework

Modern PHP Framework with MySQL, built with best practices and security in mind.

## Requirements

- PHP 8.2+
- Composer
- Docker & Docker Compose (for MySQL)

## Installation

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your settings
3. Install dependencies: `composer install`
4. Start MySQL container: `./scripts/docker-start.sh` or `docker compose up -d`

## Docker MySQL

The project uses Docker Compose for MySQL:

```bash
# Start MySQL (runs on port 3307)
./scripts/docker-start.sh
# or
docker compose up -d

# Stop MySQL
./scripts/docker-stop.sh
# or
docker compose down

# View logs
./scripts/docker-logs.sh
# or
docker compose logs -f mysql
```

**Note:** MySQL runs on port **3307** (not 3306) to avoid conflicts with local MySQL instances.

## Development Server

```bash
php -S localhost:8000 -t public
```

Access: http://localhost:8000

## Testing

```bash
./vendor/bin/phpunit tests/framework
```

## Project Structure

- `app/` - Application code (Domain, Services, Http, Repositories, etc.)
- `config/` - Configuration files
- `bootstrap/` - Framework bootstrap files
- `public/` - Web entry point
- `routes/` - Route definitions
- `storage/` - Logs, cache, sessions, uploads
- `database/` - Migrations, seeds, fixtures
- `tests/` - Test suites

## License

MIT
