<?php

namespace App\Models;

use App\Enums\ConditionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'lab_id',
        'proc_source_code',
        'arrival_mmyy',
        'type_code',
        'lab_code_snapshot',
        'seq_number',
        'asset_tag',
        'subtype',
        'condition',
        'is_available',
    ];

    protected $casts = [
        'condition' => ConditionEnum::class,
        'seq_number' => 'integer',
        'is_available' => 'boolean',
    ];

    /**
     * Get the batch this unit belongs to
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the lab this unit is located in
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * Get transaction lines for this unit
     */
    public function transactionLines(): HasMany
    {
        return $this->hasMany(TransactionLine::class);
    }

    /**
     * Get borrowing items for this unit
     */
    public function borrowingItems(): HasMany
    {
        return $this->hasMany(AssetBorrowingItem::class);
    }

    /**
     * Check if unit is usable (good condition and available)
     */
    public function isUsable(): bool
    {
        return $this->condition->isUsable() && $this->is_available;
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
     * Scope: search by asset tag
     */
    public function scopeSearchTag($query, string $search)
    {
        return $query->where('asset_tag', 'like', "%{$search}%");
    }

    /**
     * Get the latest condition change notes for this unit
     */
    public function getLatestConditionNotes(): ?string
    {
        $latestTransaction = $this->transactionLines()
            ->with('transaction')
            ->whereHas('transaction', function ($query) {
                $query->where('type', \App\Enums\TransactionTypeEnum::CONDITION_CHANGE);
            })
            ->latest()
            ->first();

        return $latestTransaction?->transaction?->notes;
    }
}
