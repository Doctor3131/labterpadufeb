<?php

namespace App\Models;

use App\Enums\TrackingModeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetTypeCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'default_tracking_mode',
        'is_borrowable',
    ];

    protected $casts = [
        'default_tracking_mode' => TrackingModeEnum::class,
        'is_borrowable' => 'boolean',
    ];

    /**
     * Get all items with this type code
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
