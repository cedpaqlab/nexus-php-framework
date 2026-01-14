<?php

declare(strict_types=1);

namespace App\Services\Helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    public static function now(?string $timezone = null): DateTime
    {
        $tz = $timezone ? new DateTimeZone($timezone) : null;
        return new DateTime('now', $tz);
    }

    public static function format(string|DateTime $date, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date->format($format);
    }

    public static function parse(string $date, ?string $format = null): DateTime
    {
        if ($format !== null) {
            $parsed = DateTime::createFromFormat($format, $date);
            if ($parsed === false) {
                throw new \InvalidArgumentException("Invalid date format: {$date}");
            }
            return $parsed;
        }

        return new DateTime($date);
    }

    public static function diff(string|DateTime $date1, string|DateTime $date2): int
    {
        if (is_string($date1)) {
            $date1 = new DateTime($date1);
        }
        if (is_string($date2)) {
            $date2 = new DateTime($date2);
        }

        return $date1->diff($date2)->days;
    }

    public static function isPast(string|DateTime $date): bool
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date < self::now();
    }

    public static function isFuture(string|DateTime $date): bool
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        return $date > self::now();
    }
}
