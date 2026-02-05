<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BpsMasterData extends Model
{
    use HasFactory;

    protected $table = 'bps_master_data';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the sub data for this master
     */
    public function subData()
    {
        return $this->hasMany(BpsSubData::class, 'master_id')->orderBy('sort_order');
    }

    /**
     * Get active sub data only
     */
    public function activeSubData()
    {
        return $this->hasMany(BpsSubData::class, 'master_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Scope for active master data
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered results
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
