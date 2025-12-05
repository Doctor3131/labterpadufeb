<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lab_id',
        'day',
        'start_time',
        'end_time',
        'course',
        'description',
        'student_count',
        'status',
        'admin_notes',
        'approved_at',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'student_count' => 'integer',
    ];

    /**
     * Get the user who made the booking
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lab being booked
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the schedule created from this booking
     */
    public function schedule()
    {
        return $this->hasOne(Schedule::class);
    }

    /**
     * Check if booking is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if booking is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
}
