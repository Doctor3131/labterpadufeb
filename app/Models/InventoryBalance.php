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
        'brand',
        'lab_id',
        'condition',
        'quantity',
        'university_asset_code_prefix',
        'custom_codes',
    ];

    protected $casts = [
        'condition' => ConditionEnum::class,
        'quantity' => 'integer',
        'custom_codes' => 'array',
    ];

    /**
     * Get the explicitly set or dynamically generated university asset codes.
     */
    public function getCalculatedCodesAttribute(): array
    {
        // If custom_codes array exists and has elements, use it
        if (!empty($this->custom_codes)) {
            return $this->custom_codes;
        }

        $codes = [];
        $pfx = $this->university_asset_code_prefix;
        $qty = $this->quantity;

        if (!$pfx || $qty <= 0) {
            return $codes;
        }

        // Generate logic mirroring InventoryService
        if (preg_match('/^(.+\.)([A-Za-z]*)(\d+)$/', $pfx, $matches)) {
            $base = $matches[1];
            $letters = $matches[2];
            $startNum = (int)$matches[3];
            
            for ($i = 0; $i < $qty; $i++) {
                $codes[] = $base . $letters . ($startNum + $i);
            }
        } else {
            for ($i = 1; $i <= $qty; $i++) {
                $codes[] = $pfx . '-' . $i;
            }
        }

        return $codes;
    }

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
