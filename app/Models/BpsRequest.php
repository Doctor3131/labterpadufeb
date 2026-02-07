<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BpsRequest extends Model
{
    use HasFactory;

    protected $table = 'bps_requests';

    /**
     * Valid purposes for data request
     */
    public const PURPOSES = [
        'Skripsi',
        'Thesis',
        'Disertasi',
        'Lomba',
        'Tugas Mata Kuliah',
        'Penelitian/Project Dengan Dosen',
        'Riset',
        'Lainnya',
    ];

    /**
     * Valid applicant types
     */
    public const APPLICANT_TYPES = [
        'mahasiswa',
        'dosen',
    ];

    /**
     * Valid statuses
     */
    public const STATUSES = [
        'pending',
        'completed',
    ];

    /**
     * Valid study programs
     */
    public const STUDY_PROGRAMS = [
        'S1- Ekonomi',
        'S1- Manajemen',
        'S1- Akuntansi',
        'S1- Ekonomi Islam',
        'S1- Bisnis Digital',
        'S2- Ekonomi',
        'S2- Manajemen',
        'S2- Akuntansi',
        'Sekolah Vokasi',
        'S3- PDIE Ilmu Ekonomi',
        'S3- PDIE Akuntansi',
        'S3- PDIE Manajemen',
        'Lainnya',
    ];

    protected $fillable = [
        'token',
        'applicant_type',
        'name',
        'email',
        'nim',
        'nip',
        'phone',
        'study_program',
        'purpose',
        'purpose_other',
        'has_lecturer_collaboration',
        'collaborating_lecturer_name',
        'ktm_path',
        'statement_letter_path',
        'agreement_accepted',
        'status',
        'completed_at',
        'handled_by',
    ];

    protected $casts = [
        'has_lecturer_collaboration' => 'boolean',
        'agreement_accepted' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who handled this request
     */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Get the selected sub data (datasets)
     */
    public function subData()
    {
        return $this->belongsToMany(BpsSubData::class, 'bps_request_data', 'request_id', 'sub_data_id')
            ->withTimestamps();
    }

    /**
     * Get the variables for this request
     */
    public function variables()
    {
        return $this->hasMany(BpsRequestVariable::class, 'request_id');
    }

    /**
     * Get identifier (NIM or NIP based on applicant type)
     */
    public function getIdentifierAttribute(): string
    {
        return $this->applicant_type === 'mahasiswa' ? ($this->nim ?? '-') : ($this->nip ?? '-');
    }

    /**
     * Get display purpose (including 'other' if applicable)
     */
    public function getDisplayPurposeAttribute(): string
    {
        if ($this->purpose === 'Lainnya' && $this->purpose_other) {
            return $this->purpose_other;
        }
        return $this->purpose;
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Generate unique token for secure URL access
     */
    public static function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
