<?php

declare(strict_types=1);

namespace Tests\Framework\Logger;

use Tests\Support\TestCase;
use App\Services\Logger\Logger;

class LoggerTest extends TestCase
{
    private string $logPath;
    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logPath = __DIR__ . '/../../../storage/logs';
        $this->logger = new Logger('test');
    }

    protected function tearDown(): void
    {
        $logFile = $this->logPath . '/' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        parent::tearDown();
    }

    public function testDebugLog(): void
    {
        $this->logger->debug('Debug message', ['key' => 'value']);
        $this->assertLogFileExists();
    }

    public function testInfoLog(): void
    {
        $this->logger->info('Info message');
        $this->assertLogFileExists();
    }

    public function testWarningLog(): void
    {
        $this->logger->warning('Warning message');
        $this->assertLogFileExists();
    }

    public function testErrorLog(): void
    {
        $this->logger->error('Error message', ['error' => 'details']);
        $this->assertLogFileExists();
    }

    public function testCriticalLog(): void
    {
        $this->logger->critical('Critical message');
        $this->assertLogFileExists();
    }

    private function assertLogFileExists(): void
    {
        $logFile = $this->logPath . '/' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);
    }
}
