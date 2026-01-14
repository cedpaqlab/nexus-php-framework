<?php

declare(strict_types=1);

namespace Tests\Framework\Logger;

use Tests\Support\TestCase;
use App\Services\Logger\LogLevel;

class LogLevelTest extends TestCase
{
    public function testLogLevelValues(): void
    {
        $this->assertEquals('debug', LogLevel::DEBUG->value());
        $this->assertEquals('info', LogLevel::INFO->value());
        $this->assertEquals('critical', LogLevel::CRITICAL->value());
    }

    public function testLogLevelPriority(): void
    {
        $this->assertEquals(0, LogLevel::DEBUG->priority());
        $this->assertEquals(1, LogLevel::INFO->priority());
        $this->assertEquals(4, LogLevel::CRITICAL->priority());
    }

    public function testLogLevelPriorityOrdering(): void
    {
        $this->assertLessThan(LogLevel::INFO->priority(), LogLevel::DEBUG->priority());
        $this->assertLessThan(LogLevel::ERROR->priority(), LogLevel::WARNING->priority());
    }
}
