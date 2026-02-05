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

    protected $fillable = [
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
        'tracking_token',
        'admin_notes',
    ];

    protected $casts = [
        'has_lecturer_collaboration' => 'boolean',
        'agreement_accepted' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->tracking_token)) {
                $model->tracking_token = static::generateTrackingToken();
            }
        });
    }

    /**
     * Generate unique tracking token
     */
    public static function generateTrackingToken(): string
    {
        do {
            $token = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
        } while (static::where('tracking_token', $token)->exists());

        return $token;
    }

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
}
