<?php

namespace App\Models;

use App\Enums\ConditionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'asset_unit_id',
        'inventory_balance_id',
        'from_condition',
        'to_condition',
        'quantity',
    ];

    protected $casts = [
        'from_condition' => ConditionEnum::class,
        'to_condition' => ConditionEnum::class,
        'quantity' => 'integer',
    ];

    /**
     * Get the transaction this line belongs to
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'transaction_id');
    }

    /**
     * Get the asset unit (if applicable)
     */
    public function assetUnit(): BelongsTo
    {
        return $this->belongsTo(AssetUnit::class);
    }

    /**
     * Get the inventory balance (if applicable)
     */
    public function inventoryBalance(): BelongsTo
    {
        return $this->belongsTo(InventoryBalance::class);
    }
}
