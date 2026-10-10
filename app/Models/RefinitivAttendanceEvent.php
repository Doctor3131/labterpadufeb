<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefinitivAttendanceEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'refinitiv_request_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RefinitivRequest::class, 'refinitiv_request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
