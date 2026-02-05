<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefinitivRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'name',
        'nim_nip',
        'whatsapp',
        'affiliation',
        'applicant_type',
        'study_program',
        'purpose',
        'purpose_other',
        'lecturer_name',
        'usage_date',
        'session',
        'variables',
        'ktm_file',
        'statement_file',
        'attendance_status',
        'attendance_marked_at',
        'handled_by',
        'admin_notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'attendance_marked_at' => 'datetime',
    ];

    // Constants for affiliations
    const AFFILIATIONS = [
        'internal_feb' => 'Internal FEB Undip',
        'internal_undip' => 'Internal Undip (Di Luar FEB)',
        'eksternal' => 'Eksternal Undip (Univ Lain)',
    ];

    // Constants for applicant types
    const APPLICANT_TYPES = [
        'dosen' => 'Dosen',
        'mahasiswa' => 'Mahasiswa',
    ];

    // Constants for study programs
    const STUDY_PROGRAMS = [
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

    // Constants for purposes
    const PURPOSES = [
        'skripsi' => 'Skripsi',
        'thesis' => 'Thesis',
        'disertasi' => 'Disertasi',
        'lomba' => 'Lomba',
        'tugas_mk' => 'Tugas Mata Kuliah',
        'penelitian_dosen' => 'Penelitian/Project Dengan Dosen',
        'lainnya' => 'Lainnya',
    ];

    // Constants for sessions
    const SESSIONS = [
        'sesi_1' => 'Sesi 1: 08.00 - 10.00 WIB',
        'sesi_2' => 'Sesi 2: 10.00 - 12.00 WIB',
        'sesi_3' => 'Sesi 3: 13.00 - 15.00 WIB / 13.30 - 15.30 (Jumat)',
    ];

    // Constants for attendance status
    const ATTENDANCE_STATUSES = [
        'pending' => 'Menunggu',
        'hadir' => 'Hadir',
        'tidak_hadir' => 'Tidak Hadir',
    ];

    /**
     * Get the admin who handled this request
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Check if applicant is a student
     */
    public function isStudent(): bool
    {
        return $this->applicant_type === 'mahasiswa';
    }

    /**
     * Check if applicant is a lecturer
     */
    public function isLecturer(): bool
    {
        return $this->applicant_type === 'dosen';
    }

    /**
     * Get formatted affiliation
     */
    public function getAffiliationLabelAttribute(): string
    {
        return self::AFFILIATIONS[$this->affiliation] ?? $this->affiliation;
    }

    /**
     * Get formatted purpose
     */
    public function getPurposeLabelAttribute(): string
    {
        if ($this->purpose === 'lainnya' && $this->purpose_other) {
            return $this->purpose_other;
        }
        return self::PURPOSES[$this->purpose] ?? $this->purpose;
    }

    /**
     * Get formatted session
     */
    public function getSessionLabelAttribute(): string
    {
        return self::SESSIONS[$this->session] ?? $this->session;
    }

    /**
     * Get formatted attendance status
     */
    public function getAttendanceStatusLabelAttribute(): string
    {
        return self::ATTENDANCE_STATUSES[$this->attendance_status] ?? $this->attendance_status;
    }

    /**
     * Get session time range
     */
    public function getSessionTimeAttribute(): array
    {
        $times = [
            'sesi_1' => ['start' => '08:00', 'end' => '10:00'],
            'sesi_2' => ['start' => '10:00', 'end' => '12:00'],
            'sesi_3' => ['start' => '13:00', 'end' => '15:00'], // Default, Friday is 13:30-15:30
        ];
        
        return $times[$this->session] ?? ['start' => '08:00', 'end' => '10:00'];
    }

    /**
     * Generate unique token
     */
    public static function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
