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
        'has_sub_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_sub_data' => 'boolean',
    ];

    /**
     * Get the sub data for this master
     */
    public function subData()
    {
        return $this->hasMany(BpsSubData::class, 'master_id')->orderBy('name');
    }

    /**
     * Get active sub data only
     */
    public function activeSubData()
    {
        return $this->hasMany(BpsSubData::class, 'master_id')
            ->where('is_active', true)
            ->orderBy('name');
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
        return $query->orderBy('name');
    }
}
