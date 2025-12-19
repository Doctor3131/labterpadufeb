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
        'nama_peminjam',
        'program_studi',
        'nim',
        'no_telpon',
        'alamat',
        'jenis_kegiatan',
        'jabatan',
        'kebutuhan_peralatan',
        'nama_kegiatan',
        'mata_kuliah',
        'dosen_pengampu',
        'nip_dosen',
        'software_digunakan',
        'is_recurring',
        'tanggal',
        'start_time',
        'end_time',
        'jumlah_peserta',
        'document_path',
        'status',
        'rejection_reason',
        'approved_by',
        'admin_notes',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'jumlah_peserta' => 'integer',
        'is_recurring' => 'boolean',
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
     * Get the admin who approved this booking
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
     * Get the day name from date
     */
    public function getDayAttribute()
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        
        return $days[$this->tanggal->format('l')];
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
}
