<?php

declare(strict_types=1);

class Config
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        [$file, $path] = explode('.', $key, 2);
        $configFile = __DIR__ . '/' . $file . '.php';

        if (!file_exists($configFile)) {
            return $default;
        }

        $config = require $configFile;
        self::$cache[$file] = $config;

        $keys = explode('.', $path);
        $value = $config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }
}
