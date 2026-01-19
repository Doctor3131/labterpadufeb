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
     * NOTE: Removed $with = ['booking'] for performance optimization.
     * Use ->with('booking') explicitly where needed.
     */

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        // Note: start_time and end_time are stored as TIME, not DATETIME
        // No cast needed - use Carbon::parse() when formatting
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
     * Scope to filter schedules that are active within a specific date range.
     * Takes into account start_date and end_date of the schedule.
     */
    public function scopeActiveBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            // Case 1: Schedule has no start_date (always active from beginning)
            $q->whereNull('start_date')
              ->orWhere(function ($q2) use ($endDate, $startDate) {
                  // Case 2: Schedule starts before or on the end date of the requested range
                  $q2->where('start_date', '<=', $endDate)
                     ->where(function ($q3) use ($startDate) {
                         // AND it has no end_date or ends after or on the start date of the requested range
                         $q3->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startDate);
                     });
              });
        });
    }

    /**
     * Scope to filter schedules that overlap with a specific time range.
     */
    public function scopeOverlappingTime($query, $startTime, $endTime)
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            // New booking starts during existing schedule
            $q->where(function ($sub) use ($startTime, $endTime) {
                $sub->where('start_time', '<=', $startTime)
                    ->where('end_time', '>', $startTime);
            })
            // New booking ends during existing schedule
            ->orWhere(function ($sub) use ($startTime, $endTime) {
                $sub->where('start_time', '<', $endTime)
                    ->where('end_time', '>=', $endTime);
            })
            // New booking completely covers existing schedule
            ->orWhere(function ($sub) use ($startTime, $endTime) {
                $sub->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime);
            });
        });
    }

    public function getTimeRangeAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($this->end_time)->format('H:i');
    }
}
