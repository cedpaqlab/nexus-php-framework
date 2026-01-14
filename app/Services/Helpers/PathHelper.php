<?php

declare(strict_types=1);

namespace App\Services\Helpers;

class PathHelper
{
    private static ?string $basePath = null;

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/') . '/';
    }

    public static function basePath(string $path = ''): string
    {
        if (self::$basePath === null) {
            self::$basePath = dirname(__DIR__, 3) . '/';
        }

        return self::$basePath . ltrim($path, '/');
    }

    public static function appPath(string $path = ''): string
    {
        return self::basePath('app/' . ltrim($path, '/'));
    }

    public static function configPath(string $path = ''): string
    {
        return self::basePath('config/' . ltrim($path, '/'));
    }

    public static function storagePath(string $path = ''): string
    {
        return self::basePath('storage/' . ltrim($path, '/'));
    }

    public static function resourcesPath(string $path = ''): string
    {
        return self::basePath('resources/' . ltrim($path, '/'));
    }

    public static function publicPath(string $path = ''): string
    {
        return self::basePath('public/' . ltrim($path, '/'));
    }
}
