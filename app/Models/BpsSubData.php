<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BpsSubData extends Model
{
    use HasFactory;

    protected $table = 'bps_sub_data';

    protected $fillable = [
        'master_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the master data this belongs to
     */
    public function master()
    {
        return $this->belongsTo(BpsMasterData::class, 'master_id');
    }

    /**
     * Get the requests that include this sub data
     */
    public function requests()
    {
        return $this->belongsToMany(BpsRequest::class, 'bps_request_data', 'sub_data_id', 'request_id');
    }

    /**
     * Scope for active sub data
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
