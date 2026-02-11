<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'study_program',
        'nim',
        'nip',
        'lecturer_nip',
        'phone_number',
        'software_needs',
        'ktm_path',
    ];

    /**
     * Get the schedule this document belongs to
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
