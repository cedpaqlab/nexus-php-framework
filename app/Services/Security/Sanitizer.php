<?php

declare(strict_types=1);

namespace App\Services\Security;

class Sanitizer
{
    public static function input(string $value): string
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $value;
    }

    public static function output(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function email(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    public static function url(string $url): string
    {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    public static function int(mixed $value): int
    {
        $sanitized = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        return (int) $sanitized;
    }

    public static function float(mixed $value): float
    {
        $sanitized = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return (float) $sanitized;
    }

    public static function array(array $data): array
    {
        return array_map(fn($item) => is_string($item) ? self::input($item) : $item, $data);
    }
}
