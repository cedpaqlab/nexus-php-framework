<?php

declare(strict_types=1);

namespace App\Services\Logger;

enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';

    public function value(): string
    {
        return $this->value;
    }

    public function priority(): int
    {
        return match ($this) {
            self::DEBUG => 0,
            self::INFO => 1,
            self::WARNING => 2,
            self::ERROR => 3,
            self::CRITICAL => 4,
        };
    }
}
