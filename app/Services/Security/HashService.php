<?php

declare(strict_types=1);

namespace App\Services\Security;

use Config\Config;

class HashService
{
    private string $algo;
    private array $options;
    private RandomService $randomService;

    public function __construct(?RandomService $randomService = null)
    {
        $this->algo = Config::get('security.password.algo', PASSWORD_DEFAULT);
        $this->options = Config::get('security.password.options', []);
        $this->randomService = $randomService ?? new RandomService();
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
        // PHP 8.3: Use RandomService with Random\Randomizer
        return $this->randomService->randomToken($length);
    }
}
