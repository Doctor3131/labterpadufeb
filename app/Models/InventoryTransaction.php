<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'lab_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'type' => TransactionTypeEnum::class,
    ];

    /**
     * Get the lab this transaction is for
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all lines in this transaction
     */
    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class, 'transaction_id');
    }

    /**
     * Scope: filter by type
     */
    public function scopeOfType($query, TransactionTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: filter by lab
     */
    public function scopeInLab($query, $labId)
    {
        return $query->where('lab_id', $labId);
    }
}
