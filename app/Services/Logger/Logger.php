<?php

declare(strict_types=1);

namespace App\Services\Logger;

class Logger
{
    private string $channel;
    private string $logPath;
    private string $level;

    public function __construct(string $channel = 'file')
    {
        $this->channel = $channel;
        $this->logPath = __DIR__ . '/../../../storage/logs';
        $this->level = $_ENV['LOG_LEVEL'] ?? 'debug';

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message}{$contextString}" . PHP_EOL;

        $logFile = $this->logPath . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    private function shouldLog(string $level): bool
    {
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];
        $currentLevel = $levels[$this->level] ?? 0;
        $messageLevel = $levels[$level] ?? 0;

        return $messageLevel >= $currentLevel;
    }
}
