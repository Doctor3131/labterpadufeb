<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'start_time',
        'end_time',
        'room',
        'course',
        'lecturer',
        'komting',
        'phone',
        'student_count',
        'type',
        'booking_id',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'student_count' => 'integer',
    ];

    /**
     * Get the booking that created this schedule
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
