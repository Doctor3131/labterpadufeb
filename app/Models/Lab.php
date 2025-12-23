<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'capacity',
        'status',
        'image',
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
        // Check recurring schedules (perkuliahan tetap)
        $hasScheduleConflict = $this->schedules()
            ->where('day', $day)
            ->where(function ($query) use ($date) {
                // Check if schedule is active on the requested date
                if ($date) {
                    $query->where(function ($q) use ($date) {
                        $q->whereNull('start_date')
                          ->orWhere(function ($q2) use ($date) {
                              $q2->where('start_date', '<=', $date)
                                 ->where(function ($q3) use ($date) {
                                     $q3->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $date);
                                 });
                          });
                    });
                }
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // New booking starts during existing schedule
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })
                ->orWhere(function ($q) use ($startTime, $endTime) {
                    // New booking ends during existing schedule
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                })
                ->orWhere(function ($q) use ($startTime, $endTime) {
                    // New booking completely covers existing schedule
                    $q->where('start_time', '>=', $startTime)
                      ->where('end_time', '<=', $endTime);
                });
            })
            ->exists();

        if ($hasScheduleConflict) {
            return false;
        }

        // Check one-time bookings (only if date is provided)
        if ($date) {
            $hasBookingConflict = $this->bookings()
                ->where('booking_date', $date)
                ->whereIn('status', ['pending', 'approved']) // Only check non-rejected bookings
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        // New booking starts during existing booking
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>', $startTime);
                    })
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        // New booking ends during existing booking
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>=', $endTime);
                    })
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        // New booking completely covers existing booking
                        $q->where('start_time', '>=', $startTime)
                          ->where('end_time', '<=', $endTime);
                    });
                })
                ->exists();

            if ($hasBookingConflict) {
                return false;
            }
        }

        return true;
    }
}
