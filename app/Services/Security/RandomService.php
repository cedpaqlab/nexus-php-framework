<?php

declare(strict_types=1);

namespace App\Services\Security;

use Random\Randomizer;
use Random\Engine\Secure;

class RandomService
{
    private Randomizer $randomizer;

    public function __construct(?Randomizer $randomizer = null)
    {
        // PHP 8.3: Use Random\Randomizer with Secure engine
        $this->randomizer = $randomizer ?? new Randomizer(new Secure());
    }

    /**
     * Generate random bytes and return as hex string
     */
    public function randomHex(int $length): string
    {
        $bytes = $this->randomizer->getBytes($length);
        return bin2hex($bytes);
    }

    /**
     * Generate random token (hex string, default 32 bytes = 64 hex chars)
     */
    public function randomToken(int $bytes = 32): string
    {
        return $this->randomHex($bytes);
    }

    /**
     * Generate random integer between min and max (inclusive)
     */
    public function randomInt(int $min, int $max): int
    {
        return $this->randomizer->getInt($min, $max);
    }

    /**
     * Shuffle array
     */
    public function shuffleArray(array $array): array
    {
        return $this->randomizer->shuffleArray($array);
    }
}
