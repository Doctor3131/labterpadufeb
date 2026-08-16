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
        'brand',
        'category',
        'asset_type_code_id',
        'tracking_mode',
        'description',
        'image_path',
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
     * Get all borrowing items
     */
    public function borrowingItems(): HasMany
    {
        return $this->hasMany(AssetBorrowingItem::class);
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
            return $this->batches->sum(fn ($batch) => $batch->assetUnits->count());
        }

        return $this->batches->sum(fn ($batch) => $batch->inventoryBalances->sum('quantity'));
    }

    /**
     * Get total available units that can be borrowed (excluding borrowed, damaged, and currently borrowed units)
     * Works with eager loaded data for better performance, falls back to direct query if not loaded
     */
    public function getAvailableUnitsAttribute(): int
    {
        // Calculate total stock available in inventory
        $totalStock = 0;

        // Check if batches are loaded
        if ($this->relationLoaded('batches')) {
            // Use eager loaded data for better performance
            if ($this->hasIndividualUnits()) {
                $totalStock = $this->batches->sum(function ($batch) {
                    if ($batch->relationLoaded('assetUnits')) {
                        // assetUnits already filtered in eager loading
                        return $batch->assetUnits->count();
                    } else {
                        // Fall back to query
                        return $batch->assetUnits()
                            ->where('is_available', true)
                            ->where('condition', 'BAIK')
                            ->count();
                    }
                });
            } else {
                $totalStock = $this->batches->sum(function ($batch) {
                    if ($batch->relationLoaded('inventoryBalances')) {
                        // inventoryBalances already filtered in eager loading
                        return $batch->inventoryBalances->sum('quantity');
                    } else {
                        // Fall back to query
                        return $batch->inventoryBalances()
                            ->where('condition', 'BAIK')
                            ->where('quantity', '>', 0)
                            ->sum('quantity');
                    }
                });
            }
        } else {
            // Batches not loaded, query directly
            if ($this->hasIndividualUnits()) {
                $totalStock = AssetUnit::whereHas('batch', function ($query) {
                    $query->where('item_id', $this->id);
                })
                    ->where('is_available', true)
                    ->where('condition', 'BAIK')
                    ->count();
            } else {
                $totalStock = InventoryBalance::whereHas('batch', function ($query) {
                    $query->where('item_id', $this->id);
                })
                    ->where('condition', 'BAIK')
                    ->where('quantity', '>', 0)
                    ->sum('quantity');
            }
        }

        // Subtract reserved items (status = approved) for aggregate items.
        // NOTE: Items with status 'borrowed' are already deducted from inventory_balances
        //       during the handout process, so we MUST NOT subtract them again here.
        // Items with status 'pending' are not yet reserved, so not subtracted.
        if (! $this->hasIndividualUnits()) {
            $reservedQuantity = AssetBorrowingItem::where('item_id', $this->id)
                ->whereHas('borrowing', function ($query) {
                    // Only 'approved' (reserved but not yet handed out) - inventory not yet reduced
                    $query->where('status', 'approved');
                })
                ->sum('quantity');

            $totalStock -= $reservedQuantity;
        }

        return max(0, $totalStock); // Ensure we never return negative
    }
}
