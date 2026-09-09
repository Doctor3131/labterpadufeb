<?php

namespace App\Models;

use App\Services\ScheduleCalendarService;
use Carbon\Carbon;
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

        if ($date) {
            return app(ScheduleCalendarService::class)->findConflict(
                $this->id,
                Carbon::parse($date),
                $startTime,
                $endTime
            ) === null;
        }

        // Date-less checks are retained only for legacy callers.
        return ! $this->schedules()
            ->where('day', $day)
            ->overlappingTime($startTime, $endTime)
            ->exists();
    }
}
