<?php

declare(strict_types=1);

namespace App\Services\Helpers;

use App\Exceptions\FileNotFoundException;

class FileHelper
{
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    public static function get(string $path): string
    {
        if (!self::exists($path)) {
            throw new FileNotFoundException($path);
        }

        return file_get_contents($path);
    }

    public static function put(string $path, string $content): int|false
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($path, $content);
    }

    public static function delete(string $path): bool
    {
        if (!self::exists($path)) {
            return false;
        }

        return unlink($path);
    }

    public static function size(string $path): int
    {
        if (!self::exists($path)) {
            return 0;
        }

        return filesize($path);
    }

    public static function mimeType(string $path): string
    {
        if (!self::exists($path)) {
            return '';
        }

        return mime_content_type($path) ?: '';
    }

    public static function extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    public static function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}
