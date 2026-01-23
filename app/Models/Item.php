<?php

namespace App\Models;

use App\Enums\TrackingModeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'asset_type_code_id',
        'tracking_mode',
        'description',
    ];

    protected $casts = [
        'tracking_mode' => TrackingModeEnum::class,
    ];

    /**
     * Get the type code for this item
     */
    public function assetTypeCode(): BelongsTo
    {
        return $this->belongsTo(AssetTypeCode::class);
    }

    /**
     * Get all batches for this item
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Check if this item tracks individual units
     */
    public function hasIndividualUnits(): bool
    {
        return $this->tracking_mode->hasIndividualUnits();
    }

    /**
     * Get total unit count across all batches and labs
     */
    public function getTotalUnitsAttribute(): int
    {
        if ($this->hasIndividualUnits()) {
            return $this->batches->sum(fn($batch) => $batch->assetUnits->count());
        }
        return $this->batches->sum(fn($batch) => $batch->inventoryBalances->sum('quantity'));
    }
}
