<?php

declare(strict_types=1);

namespace App\Services\Helpers;

use App\Services\Security\RandomService;

class StringHelper
{
    private static ?RandomService $randomService = null;

    public static function random(int $length = 16): string
    {
        // PHP 8.3: Use RandomService with Random\Randomizer
        if (self::$randomService === null) {
            self::$randomService = new RandomService();
        }
        return self::$randomService->randomHex($length / 2);
    }

    public static function slug(string $string, string $separator = '-'): string
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', $separator, $string);
        return trim($string, $separator);
    }

    public static function camel(string $string): string
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        return lcfirst(str_replace(' ', '', $string));
    }

    public static function snake(string $string): string
    {
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
        return strtolower($string);
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    public static function limit(string $string, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }

        return mb_substr($string, 0, $limit) . $end;
    }
}
