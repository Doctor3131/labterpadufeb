<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            // A schedule whose occurrence is cancelled on this date does not block the slot
            ->when($date, function ($query) use ($date) {
                $query->whereDoesntHave('occurrences', function ($q) use ($date) {
                    $q->where('occurrence_date', $date)
                        ->where('type', ScheduleOccurrence::TYPE_CANCELLED);
                });
            })
            ->exists();

        if ($hasScheduleConflict) {
            return false;
        }

        // Check moved occurrences that occupy this lab/time on the requested date
        // A single instance moved into this lab blocks this slot.
        if ($date) {
            $movedConflict = ScheduleOccurrence::where('lab_id', $this->id)
                ->where('occurrence_date', $date)
                ->where('type', ScheduleOccurrence::TYPE_MOVED)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereTime('start_time', '<', $endTime)
                        ->whereTime('end_time', '>', $startTime);
                })
                ->exists();

            if ($movedConflict) {
                return false;
            }
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
