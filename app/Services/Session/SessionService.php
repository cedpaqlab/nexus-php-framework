<?php

declare(strict_types=1);

namespace App\Services\Session;

class SessionService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function flush(): void
    {
        $_SESSION = [];
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
