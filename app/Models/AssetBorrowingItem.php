<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetBorrowingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_borrowing_id',
        'item_id',
        'asset_unit_id',
        'inventory_balance_id',
        'quantity',
        'brand_type',
        'condition_good',
        'condition_adequate',
        'condition_complete',
        'remarks',
        'notes',
    ];

    /**
     * Get the borrowing record
     */
    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(AssetBorrowing::class, 'asset_borrowing_id');
    }

    /**
     * Get the item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the specific asset unit (for STRUCTURED_TAG & SEAT_NUMBER)
     */
    public function assetUnit(): BelongsTo
    {
        return $this->belongsTo(AssetUnit::class);
    }

    /**
     * Get the inventory balance (for AGGREGATE)
     */
    public function inventoryBalance(): BelongsTo
    {
        return $this->belongsTo(InventoryBalance::class);
    }
}
