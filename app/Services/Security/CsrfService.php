<?php

declare(strict_types=1);

namespace App\Services\Security;

class CsrfService
{
    private string $tokenName;
    private int $tokenLifetime;

    public function __construct()
    {
        $config = require __DIR__ . '/../../../config/security.php';
        $this->tokenName = $config['csrf']['token_name'];
        $this->tokenLifetime = $config['csrf']['token_lifetime'];
    }

    public function generate(): string
    {
        if (!isset($_SESSION[$this->tokenName])) {
            $_SESSION[$this->tokenName] = [
                'token' => bin2hex(random_bytes(32)),
                'expires' => time() + $this->tokenLifetime,
            ];
        }

        return $_SESSION[$this->tokenName]['token'];
    }

    public function validate(string $token): bool
    {
        if (!isset($_SESSION[$this->tokenName])) {
            return false;
        }

        $sessionToken = $_SESSION[$this->tokenName];

        if (time() > $sessionToken['expires']) {
            unset($_SESSION[$this->tokenName]);
            return false;
        }

        return hash_equals($sessionToken['token'], $token);
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }
}
