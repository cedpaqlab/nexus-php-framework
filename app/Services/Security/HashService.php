<?php

declare(strict_types=1);

namespace App\Services\Security;

use Config;

class HashService
{
    private string $algo;
    private array $options;

    public function __construct()
    {
        $this->algo = Config::get('security.password.algo', PASSWORD_DEFAULT);
        $this->options = Config::get('security.password.options', []);
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
