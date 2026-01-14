<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Services\Session\SessionService;
use Config\Config;

class CsrfService
{
    private string $tokenName;
    private int $tokenLifetime;
    private SessionService $session;
    private RandomService $randomService;

    public function __construct(?SessionService $session = null, ?RandomService $randomService = null)
    {
        $this->tokenName = Config::get('security.csrf.token_name', '_csrf_token');
        $this->tokenLifetime = Config::get('security.csrf.token_lifetime', 3600);
        $this->session = $session ?? new SessionService();
        $this->randomService = $randomService ?? new RandomService();
    }

    public function generate(): string
    {
        if (!$this->session->has($this->tokenName)) {
            // PHP 8.3: Use RandomService with Random\Randomizer
            $this->session->set($this->tokenName, [
                'token' => $this->randomService->randomToken(32),
                'expires' => time() + $this->tokenLifetime,
            ]);
        }

        $tokenData = $this->session->get($this->tokenName);
        return $tokenData['token'];
    }

    public function validate(string $token): bool
    {
        if (!$this->session->has($this->tokenName)) {
            return false;
        }

        $sessionToken = $this->session->get($this->tokenName);

        if (time() > $sessionToken['expires']) {
            $this->session->remove($this->tokenName);
            return false;
        }

        return hash_equals($sessionToken['token'], $token);
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }
}
