<?php

namespace App\Models;

use App\Enums\ConditionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'lab_id',
        'condition',
        'quantity',
    ];

    protected $casts = [
        'condition' => ConditionEnum::class,
        'quantity' => 'integer',
    ];

    /**
     * Get the batch this balance belongs to
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the lab this balance is in
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get transaction lines for this balance
     */
    public function transactionLines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }

    /**
     * Scope: filter by lab
     */
    public function scopeInLab($query, $labId)
    {
        return $query->where('lab_id', $labId);
    }

    /**
     * Scope: filter by condition
     */
    public function scopeWithCondition($query, ConditionEnum $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope: only non-zero quantities
     */
    public function scopeNonZero($query)
    {
        return $query->where('quantity', '>', 0);
    }
}
