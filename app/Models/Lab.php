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
     * Check if lab is available at specific time on specific day
     */
    public function isAvailable($day, $startTime, $endTime)
    {
        return !$this->schedules()
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();
    }
}
