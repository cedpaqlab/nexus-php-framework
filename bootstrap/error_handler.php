<?php

declare(strict_types=1);

use App\Services\Logger\Logger;
use Config;

static $logger = null;

if ($logger === null) {
    $logger = new Logger();
}

set_error_handler(function (int $severity, string $message, string $file, int $line) use (&$logger): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    $logger->error("Error: {$message} in {$file} on line {$line}");

    $debug = Config::get('app.debug', false);

    if ($debug) {
        echo "<pre>Error: {$message}\nFile: {$file}\nLine: {$line}</pre>";
    }

    return true;
});

set_exception_handler(function (Throwable $exception) use (&$logger): void {
    $logger->critical("Uncaught exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);

    http_response_code(500);

    $debug = Config::get('app.debug', false);

    if ($debug) {
        echo "<pre>Uncaught Exception: " . get_class($exception) . "\n";
        echo "Message: {$exception->getMessage()}\n";
        echo "File: {$exception->getFile()}\n";
        echo "Line: {$exception->getLine()}\n";
        echo "Trace:\n{$exception->getTraceAsString()}</pre>";
    } else {
        echo json_encode(['error' => 'Internal Server Error']);
    }
});
