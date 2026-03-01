#!/bin/bash
# Start PHP built-in server. Ensure MySQL container is running first (docker compose up -d).

cd "$(dirname "$0")/.." || exit 1

if ! docker compose ps 2>/dev/null | grep -q "Up"; then
    echo "Warning: MySQL container does not seem to be running."
    echo "Start it with: docker compose up -d"
    echo ""
fi

echo "Starting PHP server at http://localhost:8000"
echo "Stop with Ctrl+C"
exec php -S localhost:8000 -t public
