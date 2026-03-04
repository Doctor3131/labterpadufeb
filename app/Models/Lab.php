<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Get all schedules for this lab
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get all bookings for this lab
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all asset units in this lab
     */
    public function assetUnits()
    {
        return $this->hasMany(AssetUnit::class);
    }

    /**
     * Get all inventory balances for this lab
     */
    public function inventoryBalances()
    {
        return $this->hasMany(InventoryBalance::class);
    }

    /**
     * Get inventory transactions for this lab
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Scope: exclude the warehouse (Gudang) and external (Eksternal) rooms.
     * Gudang is used only for inventory management, not for bookings or scheduling.
     * Eksternal is used only for tracking items transferred outside.
     */
    public function scopeExcludeWarehouse($query)
    {
        return $query->whereNotIn('name', ['Gudang', 'Eksternal']);
    }

    /**
     * Scope: exclude the external (Eksternal) room.
     */
    public function scopeExcludeExternal($query)
    {
        return $query->where('name', '!=', 'Eksternal');
    }

    /**
     * Check if lab is available at specific date and time
     * Checks both:
     * 1. Recurring schedules (schedules table) - for permanent classes
     * 2. One-time bookings (bookings table) - for temporary bookings
     */
    public function isAvailable($day, $startTime, $endTime, $date = null)
    {
        // Check if lab is active
        if ($this->status !== 'available') {
            return false;
        }

        // Check recurring schedules (perkuliahan tetap)
        $hasScheduleConflict = $this->schedules()
            ->where('day', $day)
            ->where(function ($query) use ($date, $startTime, $endTime) {
                // Check if schedule is active on the requested date
                if ($date) {
                    $query->activeBetweenDates($date, $date);
                }
                
                // Check for time overlap
                $query->overlappingTime($startTime, $endTime);
            })
            ->exists();

        if ($hasScheduleConflict) {
            return false;
        }

        // Check one-time bookings (only if date is provided)
        if ($date) {
            // Check 1: Exact date match for NON-recurring pending bookings
            $hasBookingConflict = $this->bookings()
                ->where('booking_date', $date)
                ->where('status', 'pending')
                ->where('is_recurring', false)
                ->overlappingTime($startTime, $endTime)
                ->exists();

            if ($hasBookingConflict) {
                return false;
            }

            // Check 2: Day-of-week match for RECURRING pending bookings
            // A recurring booking (perkuliahan_tetap) blocks the same day every week
            // starting from booking_date
            $hasRecurringConflict = $this->bookings()
                ->where('day', $day)  // Same day of week
                ->where('status', 'pending')
                ->where('is_recurring', true)
                ->where('booking_date', '<=', $date)  // Requested date is on/after booking starts
                ->overlappingTime($startTime, $endTime)
                ->exists();

            if ($hasRecurringConflict) {
                return false;
            }
        }

        return true;
    }
}
