<?php

declare(strict_types=1);

use App\Services\Logger\Logger;

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    $logger = new Logger();
    $logger->error("Error: {$message} in {$file} on line {$line}");

    $config = require __DIR__ . '/../config/app.php';

    if ($config['debug']) {
        echo "<pre>Error: {$message}\nFile: {$file}\nLine: {$line}</pre>";
    }

    return true;
});

set_exception_handler(function (Throwable $exception): void {
    $logger = new Logger();
    $logger->critical("Uncaught exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);

    $config = require __DIR__ . '/../config/app.php';

    http_response_code(500);

    if ($config['debug']) {
        echo "<pre>Uncaught Exception: " . get_class($exception) . "\n";
        echo "Message: {$exception->getMessage()}\n";
        echo "File: {$exception->getFile()}\n";
        echo "Line: {$exception->getLine()}\n";
        echo "Trace:\n{$exception->getTraceAsString()}</pre>";
    } else {
        echo json_encode(['error' => 'Internal Server Error']);
    }
});
