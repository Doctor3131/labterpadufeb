<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'booking_type',
        'unit_type',
        'pic_name',
        'study_program',
        'nim',
        'phone_number',
        'address',
        'applicant_status',
        'custom_status',
        'class_year',
        'purpose',
        'tracking_token',
        'activity_type',
        'position',
        'equipment_needs',
        'activity_name',
        'course_name',
        'lecturer_name',
        'lecturer_nip',
        'software_needs',
        'is_recurring',
        'day',
        'booking_date',
        'start_time',
        'end_time',
        'participant_count',
        'document_path',
        'status',
        'rejection_reason',
        'admin_notes',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'handled_at' => 'datetime',
        'participant_count' => 'integer',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the lab being booked
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the user who is handling this booking
     */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
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



    /**
     * Check if this is a perkuliahan booking
     */
    public function isPerkuliahan()
    {
        return in_array($this->booking_type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']);
    }

    /**
     * Check if this is a non-perkuliahan booking
     */
    public function isNonPerkuliahan()
    {
        return $this->booking_type === 'non_perkuliahan';
    }

    /**
     * Check if this is a pribadi (personal) booking
     */
    public function isPribadi()
    {
        return $this->booking_type === 'pribadi';
    }
}
