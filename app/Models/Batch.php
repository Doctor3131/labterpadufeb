<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'proc_source_code',
        'arrival_mmyy',
        'procurement_date',
        'source_description',
        'unit_price',
    ];

    protected $casts = [
        'procurement_date' => 'date',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the item this batch belongs to
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get all asset units in this batch
     */
    public function assetUnits(): HasMany
    {
        return $this->hasMany(AssetUnit::class);
    }

    /**
     * Get all inventory balances for this batch
     */
    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    /**
     * Get formatted arrival date (month year)
     */
    public function getArrivalFormattedAttribute(): string
    {
        if (strlen($this->arrival_mmyy) === 4) {
            $month = substr($this->arrival_mmyy, 0, 2);
            $year = '20' . substr($this->arrival_mmyy, 2, 2);
            $months = [
                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
            ];
            return ($months[$month] ?? $month) . ' ' . $year;
        }
        return $this->arrival_mmyy;
    }

    /**
     * Get total units in this batch
     */
    public function getTotalUnitsAttribute(): int
    {
        if ($this->item && $this->item->hasIndividualUnits()) {
            return $this->assetUnits()->count();
        }
        return $this->inventoryBalances()->sum('quantity');
    }
}
