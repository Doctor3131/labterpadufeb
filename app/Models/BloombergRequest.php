<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloombergRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'type',
        'name',
        'nim_nip',
        'phone',
        'applicant_type',
        'study_program',
        'usage_date',
        'session',
        'purpose',
        'research_title',
        'subject_name',
        'lecturer_name',
        'statement_file',
        'admin_notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    // Request types
    const TYPE_RESERVASI = 'reservasi';
    const TYPE_WALK_IN = 'walk_in';

    const TYPES = [
        'reservasi' => 'Reservasi',
        'walk_in' => 'Kunjungan Langsung',
    ];

    // Constants for applicant types
    const APPLICANT_TYPES = [
        'mahasiswa' => 'Mahasiswa',
        'dosen_undip' => 'Dosen Undip',
        'dosen_non_undip' => 'Dosen Non Undip',
    ];

    // Constants for study programs (only for mahasiswa)
    const STUDY_PROGRAMS = [
        'S1 Ekonomi',
        'S1 Manajemen',
        'S1 Akuntansi',
        'S1 Ekonomi Islam',
        'S1 Bisnis Digital',
        'S2 Ekonomi',
        'S2 Manajemen',
        'S2 Akuntansi',
        'PDIE Ekonomi',
        'PDIE Manajemen',
        'PDIE Akuntansi',
        'D4 Akuntansi Perpajakan',
        'Lainnya',
    ];

    // Constants for purposes
    const PURPOSES = [
        'skripsi' => 'Skripsi',
        'thesis' => 'Thesis',
        'disertasi' => 'Disertasi',
        'sertifikasi_bloomberg' => 'Sertifikasi Bloomberg',
        'lomba' => 'Lomba',
        'tugas_mk' => 'Tugas Mata Kuliah',
        'penelitian_dosen' => 'Penelitian/Project dengan Dosen',
        'explore' => 'Explore (Ingin mengetahui tentang Bloomberg)',
        'lainnya' => 'Lainnya',
    ];

    // Constants for sessions
    const SESSIONS = [
        'sesi_1' => 'Sesi 1: 09.00 - 12.00 WIB',
        'sesi_2' => 'Sesi 2: 13.00 - 15.00 WIB',
    ];

    // Session label for Friday
    const SESSIONS_FRIDAY = [
        'sesi_1' => 'Sesi 1: 09.00 - 12.00 WIB',
        'sesi_2' => 'Sesi 2: 13.30 - 15.00 WIB',
    ];

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
        return in_array($this->applicant_type, ['dosen_undip', 'dosen_non_undip']);
    }

    /**
     * Check if this is a walk-in request
     */
    public function isWalkIn(): bool
    {
        return $this->type === self::TYPE_WALK_IN;
    }

    /**
     * Get formatted type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get formatted applicant type
     */
    public function getApplicantTypeLabelAttribute(): string
    {
        return self::APPLICANT_TYPES[$this->applicant_type] ?? $this->applicant_type;
    }

    /**
     * Get formatted purpose
     */
    public function getPurposeLabelAttribute(): string
    {
        return self::PURPOSES[$this->purpose] ?? $this->purpose;
    }

    /**
     * Get formatted session (considers Friday)
     */
    public function getSessionLabelAttribute(): string
    {
        if ($this->usage_date && $this->usage_date->isFriday()) {
            return self::SESSIONS_FRIDAY[$this->session] ?? $this->session;
        }
        return self::SESSIONS[$this->session] ?? $this->session;
    }

    /**
     * Get session time range
     */
    public function getSessionTimeAttribute(): array
    {
        $isFriday = $this->usage_date && $this->usage_date->isFriday();

        $times = [
            'sesi_1' => ['start' => '09:00', 'end' => '12:00'],
            'sesi_2' => ['start' => $isFriday ? '13:30' : '13:00', 'end' => '15:00'],
        ];

        return $times[$this->session] ?? ['start' => '09:00', 'end' => '12:00'];
    }

    /**
     * Get remaining capacity for a specific date and session.
     */
    public static function getRemainingCapacity(string $date, string $session): int
    {
        $capacity = (int) ServiceSetting::getValue('bloomberg', 'capacity_per_session', '12');
        $booked = static::where('usage_date', $date)
            ->where('session', $session)
            ->where('type', self::TYPE_RESERVASI)
            ->count();

        return max(0, $capacity - $booked);
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
