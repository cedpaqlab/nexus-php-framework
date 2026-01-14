<?php

declare(strict_types=1);

namespace Tests\Framework\Helpers;

use Tests\Support\TestCase;
use App\Services\Helpers\DateHelper;
use DateTime;

class DateHelperTest extends TestCase
{
    public function testNow(): void
    {
        $now = DateHelper::now();
        $this->assertInstanceOf(DateTime::class, $now);
    }

    public function testFormat(): void
    {
        $date = new DateTime('2024-01-15 10:30:00');
        $formatted = DateHelper::format($date, 'Y-m-d');
        $this->assertEquals('2024-01-15', $formatted);
    }

    public function testFormatString(): void
    {
        $formatted = DateHelper::format('2024-01-15 10:30:00', 'Y-m-d');
        $this->assertEquals('2024-01-15', $formatted);
    }

    public function testParse(): void
    {
        $date = DateHelper::parse('2024-01-15');
        $this->assertInstanceOf(DateTime::class, $date);
    }

    public function testDiff(): void
    {
        $date1 = '2024-01-01';
        $date2 = '2024-01-10';
        $diff = DateHelper::diff($date1, $date2);
        $this->assertEquals(9, $diff);
    }

    public function testIsPast(): void
    {
        $past = '2020-01-01';
        $this->assertTrue(DateHelper::isPast($past));
    }

    public function testIsFuture(): void
    {
        $future = '2030-01-01';
        $this->assertTrue(DateHelper::isFuture($future));
    }
}
