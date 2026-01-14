<?php

declare(strict_types=1);

namespace App\Services\Security;

use Config;

class CsrfService
{
    private string $tokenName;
    private int $tokenLifetime;

    public function __construct()
    {
        $this->tokenName = Config::get('security.csrf.token_name', '_csrf_token');
        $this->tokenLifetime = Config::get('security.csrf.token_lifetime', 3600);
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
