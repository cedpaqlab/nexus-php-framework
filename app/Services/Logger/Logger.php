<?php

declare(strict_types=1);

namespace App\Services\Logger;

use App\Services\Helpers\PathHelper;
use App\Services\Logger\LogLevel;

class Logger
{
    private string $channel;
    private string $logPath;
    private LogLevel $level;

    public function __construct(string $channel = 'file')
    {
        $this->channel = $channel;
        $this->logPath = PathHelper::storagePath('logs');
        require_once __DIR__ . '/../../../config/loader.php';
        $logLevel = \Config\Config::get('app.log_level', $_ENV['LOG_LEVEL'] ?? 'debug');
        $this->level = LogLevel::tryFrom($logLevel) ?? LogLevel::DEBUG;

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    private function log(LogLevel $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level->value()}: {$message}{$contextString}" . PHP_EOL;

        $logFile = $this->logPath . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    private function shouldLog(LogLevel $level): bool
    {
        return $level->priority() >= $this->level->priority();
    }
}
