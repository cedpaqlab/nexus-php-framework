<?php

declare(strict_types=1);

namespace App\Services\Security;

class HashService
{
    private string $algo;
    private array $options;

    public function __construct()
    {
        $config = require __DIR__ . '/../../../config/security.php';
        $this->algo = $config['password']['algo'];
        $this->options = $config['password']['options'];
    }

    public function make(string $password): string
    {
        return password_hash($password, $this->algo, $this->options);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algo, $this->options);
    }

    public function randomToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
