<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Helper class for day-related operations
 * Centralized to avoid duplicate day mapping across the codebase
 */
class DayHelper
{
    /**
     * Indonesian day names (indexed by Carbon dayOfWeek: 0=Sunday, 1=Monday, etc.)
     */
    public const DAYS_BY_INDEX = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    /**
     * English to Indonesian day mapping
     */
    public const DAYS_ENGLISH_TO_INDO = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    /**
     * Days available for scheduling (excludes Sunday)
     */
    public const SCHEDULE_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /**
     * Day order for sorting (Monday first)
     */
    public const DAY_ORDER = [
        'Senin' => 1,
        'Selasa' => 2,
        'Rabu' => 3,
        'Kamis' => 4,
        'Jumat' => 5,
        'Sabtu' => 6,
        'Minggu' => 7,
    ];

    /**
     * Get Indonesian day name from Carbon date
     */
    public static function fromDate(Carbon $date): string
    {
        return self::DAYS_BY_INDEX[$date->dayOfWeek];
    }

    /**
     * Get Indonesian day name from dayOfWeek index (0-6)
     */
    public static function fromIndex(int $index): string
    {
        return self::DAYS_BY_INDEX[$index] ?? 'Unknown';
    }

    /**
     * Get Indonesian day name from English day name
     */
    public static function fromEnglish(string $englishDay): string
    {
        return self::DAYS_ENGLISH_TO_INDO[$englishDay] ?? 'Unknown';
    }

    /**
     * Get Indonesian day name from date string (Y-m-d format)
     */
    public static function fromDateString(string $dateString): string
    {
        return self::fromDate(Carbon::parse($dateString));
    }

    /**
     * Get sort order for a day name
     */
    public static function getOrder(string $day): int
    {
        return self::DAY_ORDER[$day] ?? 99;
    }
}
