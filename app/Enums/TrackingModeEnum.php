<?php

namespace App\Enums;

enum TrackingModeEnum: string
{
    case STRUCTURED_TAG = 'STRUCTURED_TAG';
    case SEAT_NUMBER = 'SEAT_NUMBER';
    case AGGREGATE = 'AGGREGATE';

    /**
     * Get display label
     */
    public function label(): string
    {
        return match($this) {
            self::STRUCTURED_TAG => 'Structured Tag',
            self::SEAT_NUMBER => 'Seat Number',
            self::AGGREGATE => 'Aggregate',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::STRUCTURED_TAG => 'Tracking per unit dengan format tag terstruktur (PC, Laptop, TV, dll)',
            self::SEAT_NUMBER => 'Tracking per unit dengan nomor kursi/meja (Mouse, Keyboard, dll)',
            self::AGGREGATE => 'Tracking agregat tanpa identifier unit (Router, AC, dll)',
        };
    }

    /**
     * Check if mode tracks individual units
     */
    public function hasIndividualUnits(): bool
    {
        return $this !== self::AGGREGATE;
    }
}
