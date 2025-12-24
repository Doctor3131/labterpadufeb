<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'day',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'course',
        'lecturer',
        'type',
        'booking_id',
        'komting',
        'student_count',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['booking'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the lab associated with this schedule
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the booking that created this schedule
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get effective course/activity name
     * Fallback to booking if not locally set
     */
    public function getCourseAttribute($value)
    {
        if (!empty($value)) return $value;
        
        if ($this->booking) {
            return $this->booking->course_name ?? $this->booking->activity_name;
        }
        
        return null;
    }

    /**
     * Get effective lecturer/PIC name
     * Fallback to booking if not locally set
     */
    public function getLecturerAttribute($value)
    {
        if (!empty($value)) return $value;
        
        if ($this->booking) {
            return $this->booking->lecturer_name ?? $this->booking->pic_name;
        }
        
        return null;
    }

    /**
     * Get komting/PIC - from column if set, otherwise from booking if exists
     */
    public function getKomtingAttribute()
    {
        // Priority 1: Check if komting column has value (for regular schedules)
        if (isset($this->attributes['komting']) && !empty($this->attributes['komting'])) {
            return $this->attributes['komting'];
        }
        
        // Priority 2: Get from booking relationship (for booking schedules)
        if ($this->booking) {
            return $this->booking->pic_name;
        }
        
        return null;
    }

    /**
     * Get phone - from booking if exists, otherwise null
     */
    public function getPhoneAttribute()
    {
        if ($this->booking) {
            return $this->booking->phone_number;
        }
        return null;
    }

    /**
     * Get student count - from column if set, otherwise from booking if exists
     */
    public function getStudentCountAttribute()
    {
        // Priority 1: Check if student_count column has value (for regular schedules)
        if (isset($this->attributes['student_count']) && !empty($this->attributes['student_count'])) {
            return $this->attributes['student_count'];
        }
        
        // Priority 2: Get from booking relationship (for booking schedules)
        if ($this->booking) {
            return $this->booking->participant_count;
        }
        
        return null;
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
