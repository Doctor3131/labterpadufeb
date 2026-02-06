<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BpsRequestVariable extends Model
{
    use HasFactory;

    protected $table = 'bps_request_variables';

    protected $fillable = [
        'request_id',
        'sub_data_id',
        'master_id',
        'variables',
    ];

    /**
     * Get the request this belongs to
     */
    public function request()
    {
        return $this->belongsTo(BpsRequest::class, 'request_id');
    }

    /**
     * Get the sub data this belongs to
     */
    public function subData()
    {
        return $this->belongsTo(BpsSubData::class, 'sub_data_id');
    }

    /**
     * Get the master data this belongs to (for single-level data)
     */
    public function masterData()
    {
        return $this->belongsTo(BpsMasterData::class, 'master_id');
    }
}
