<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleChangeLog extends Model
{
    protected $fillable = [
        'schedule_id',
        'schedule_occurrence_id',
        'series_uuid',
        'action',
        'scope',
        'effective_date',
        'before_values',
        'after_values',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'before_values' => 'array',
        'after_values' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(ScheduleOccurrence::class, 'schedule_occurrence_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
