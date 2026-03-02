<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetBorrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'borrower_name',
        'borrower_type',
        'borrower_id_number',
        'study_program',
        'class_year',
        'position',
        'phone_number',
        'email',
        'borrower_address',
        'first_party_name',
        'first_party_position',
        'first_party_address',
        'first_party_phone',
        'document_date',
        'lab_id',
        'purpose',
        'borrow_date',
        'return_date',
        'borrow_time',
        'return_time',
        'status',
        'rejection_reason',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'handed_out_by',
        'handed_out_at',
        'received_back_by',
        'received_back_at',
        'borrow_condition_notes',
        'return_condition_notes',
        'is_damaged_on_return',
        'damage_description',
        'replacement_deadline',
        'is_replaced',
        'replaced_at',
        'replaced_by',
        'replacement_notes',
        'document_path',
        'generated_document_path',
        'items_override',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'document_date' => 'date',
        'replacement_deadline' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'handed_out_at' => 'datetime',
        'received_back_at' => 'datetime',
        'replaced_at' => 'datetime',
        'is_damaged_on_return' => 'boolean',
        'is_replaced' => 'boolean',
        'items_override' => 'array',
    ];

    /**
     * Get the lab where the asset is from
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the admin who approved
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the admin who rejected
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the admin who handed out
     */
    public function handedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_out_by');
    }

    /**
     * Get the admin who received back
     */
    public function receivedBackBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_back_by');
    }

    /**
     * Get the admin who confirmed replacement
     */
    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }

    /**
     * Get all borrowed items
     */
    public function borrowedItems(): HasMany
    {
        return $this->hasMany(AssetBorrowingItem::class);
    }

    /**
     * Check if borrowing is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'borrowed') {
            return $this->return_date->isPast();
        }
        return false;
    }

    /**
     * Check if replacement is overdue
     */
    public function isReplacementOverdue(): bool
    {
        if ($this->is_damaged_on_return && !$this->is_replaced && $this->replacement_deadline) {
            return $this->replacement_deadline->isPast();
        }
        return false;
    }

    /**
     * Check if replacement is pending
     */
    public function isReplacementPending(): bool
    {
        return $this->is_damaged_on_return && !$this->is_replaced && $this->replacement_deadline !== null;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'borrowed' => 'bg-purple-100 text-purple-800',
            'returned' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'borrowed' => 'Sedang Dipinjam',
            'returned' => 'Sudah Dikembalikan',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }
}
