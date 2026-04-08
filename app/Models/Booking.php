<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    /**
     * Valid booking types
     */
    public const BOOKING_TYPES = [
        'perkuliahan_tetap',
        'perkuliahan_tidak_tetap',
        'non_perkuliahan',
        'pribadi',
    ];

    /**
     * Valid activity types for non-perkuliahan bookings
     * Centralized to avoid duplication between migration ENUM and validation rules
     */
    public const ACTIVITY_TYPES = [
        'Seminar',
        'Workshop',
        'Pelatihan',
        'Rapat',
        'Ujian',
        'Lainnya',
    ];

    /**
     * Valid applicant statuses for pribadi bookings
     */
    public const APPLICANT_STATUSES = [
        'Mahasiswa',
        'Dosen',
        'Pegawai',
        'Lainnya',
    ];

    protected $fillable = [
        'lab_id',
        'booking_type',
        'pribadi_sub_type',
        'unit_type',
        'pic_name',
        'study_program',
        'nim',
        'nip',
        'phone_number',
        'applicant_status',
        'custom_status',
        'class_year',
        'purpose',
        'activity_type',
        'position',
        'equipment_needs',
        'activity_name',
        'is_bimbingan_dosen',
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
        'tracking_token',
        'admin_notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'handled_at' => 'datetime',
        'participant_count' => 'integer',
        'is_recurring' => 'boolean',
        'is_bimbingan_dosen' => 'boolean',
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

    /**
     * Check if this is a non-perkuliahan booking with lecturer guidance (bimbingan dosen)
     */
    public function isBimbinganDosen()
    {
        return $this->booking_type === 'non_perkuliahan' && $this->is_bimbingan_dosen;
    }

    /**
     * Scope to filter bookings that overlap with a specific time range.
     * Centralized time overlap logic - same as Schedule::scopeOverlappingTime
     */
    public function scopeOverlappingTime($query, $startTime, $endTime)
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            // New booking starts during existing booking
            $q->where(function ($sub) use ($startTime, $endTime) {
                $sub->where('start_time', '<=', $startTime)
                    ->where('end_time', '>', $startTime);
            })
            // New booking ends during existing booking
            ->orWhere(function ($sub) use ($startTime, $endTime) {
                $sub->where('start_time', '<', $endTime)
                    ->where('end_time', '>=', $endTime);
            })
            // New booking completely covers existing booking
            ->orWhere(function ($sub) use ($startTime, $endTime) {
                $sub->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime);
            });
        });
    }
}
