<?php

declare(strict_types=1);

use App\Services\Logger\Logger;

static $logger = null;
static $errorHandlerContainer = null;

function setErrorHandlerContainer(Container $containerInstance): void
{
    global $errorHandlerContainer;
    $errorHandlerContainer = $containerInstance;
}

if ($logger === null) {
    $logger = function () {
        global $errorHandlerContainer;
        if ($errorHandlerContainer !== null && $errorHandlerContainer->has(Logger::class)) {
            return $errorHandlerContainer->get(Logger::class);
        }
        return new Logger();
    };
}

set_error_handler(function (int $severity, string $message, string $file, int $line) use (&$logger): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    $loggerInstance = is_callable($logger) ? $logger() : $logger;
    $loggerInstance->error("Error: {$message} in {$file} on line {$line}");

    $debug = \Config::get('app.debug', false);

    if ($debug) {
        echo "<pre>Error: {$message}\nFile: {$file}\nLine: {$line}</pre>";
    }

    return true;
});

set_exception_handler(function (Throwable $exception) use (&$logger): void {
    $loggerInstance = is_callable($logger) ? $logger() : $logger;
    $loggerInstance->critical("Uncaught exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);

    http_response_code(500);

    $debug = \Config::get('app.debug', false);

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
