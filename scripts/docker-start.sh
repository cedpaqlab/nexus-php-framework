#!/bin/bash

echo "Starting Docker containers..."
docker compose up -d

echo "Waiting for MySQL to be ready..."
sleep 5

if docker compose ps | grep -q "Up"; then
    echo "MySQL container is running!"
    echo "Connection details:"
    echo "  Host: 127.0.0.1"
    echo "  Port: $(grep DB_PORT .env 2>/dev/null | cut -d '=' -f2 || echo '3307')"
    echo "  Database: $(grep DB_DATABASE .env 2>/dev/null | cut -d '=' -f2 || echo 'php_framework')"
    echo "  User: $(grep DB_USERNAME .env 2>/dev/null | cut -d '=' -f2 || echo 'php_framework')"
else
    echo "Error: Container failed to start. Check logs with: docker compose logs"
fi
