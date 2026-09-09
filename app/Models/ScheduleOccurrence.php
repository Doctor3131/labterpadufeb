<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleOccurrence extends Model
{
    /**
     * Occurrence types.
     */
    public const TYPE_CANCELLED = 'cancelled';

    public const TYPE_MOVED = 'moved';

    protected $fillable = [
        'schedule_id',
        'occurrence_date',
        'override_date',
        'type',
        'lab_id',
        'start_time',
        'end_time',
        'change_reason',
        'changed_by',
    ];

    protected $casts = [
        'occurrence_date' => 'date',
        'override_date' => 'date',
    ];

    /**
     * The recurring schedule this occurrence belongs to.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * The lab the occurrence is moved to (only when type = moved).
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function changeLogs()
    {
        return $this->hasMany(ScheduleChangeLog::class);
    }

    /**
     * Is this occurrence cancelled?
     */
    public function isCancelled(): bool
    {
        return $this->type === self::TYPE_CANCELLED;
    }
}
