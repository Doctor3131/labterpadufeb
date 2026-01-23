<?php

namespace App\Enums;

enum ConditionEnum: string
{
    case BAIK = 'BAIK';
    case RUSAK = 'RUSAK';
    case HILANG = 'HILANG';
    case MAINTENANCE = 'MAINTENANCE';

    /**
     * Get display label
     */
    public function label(): string
    {
        return match($this) {
            self::BAIK => 'Baik',
            self::RUSAK => 'Rusak',
            self::HILANG => 'Hilang',
            self::MAINTENANCE => 'Maintenance',
        };
    }

    /**
     * Get color class for UI
     */
    public function colorClass(): string
    {
        return match($this) {
            self::BAIK => 'bg-green-100 text-green-800',
            self::RUSAK => 'bg-red-100 text-red-800',
            self::HILANG => 'bg-gray-100 text-gray-800',
            self::MAINTENANCE => 'bg-yellow-100 text-yellow-800',
        };
    }

    /**
     * Check if asset is usable (available for borrowing)
     */
    public function isUsable(): bool
    {
        return $this === self::BAIK;
    }
}
