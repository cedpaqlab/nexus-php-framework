<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/../.env')) {
    throw new RuntimeException('.env file not found. Please copy .env.example to .env');
}

$lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);

    if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
        putenv(sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
