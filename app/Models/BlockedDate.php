<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedDate extends Model
{
    protected $fillable = [
        'service_type',
        'blocked_date',
        'reason',
        'blocked_session', // null = all sessions, 'sesi_1' or 'sesi_2' = specific session
        'created_by',
    ];

    protected $casts = [
        'blocked_date' => 'date',
    ];

    /**
     * Scope: filter by service type.
     */
    public function scopeForService($query, string $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    /**
     * Scope: only future or today dates.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('blocked_date', '>=', now()->toDateString());
    }

    /**
     * Check if a specific date (and optionally session) is blocked for a service.
     * 
     * A date+session is blocked if:
     * - There's a record with blocked_session = null (blocks ALL sessions), OR
     * - There's a record with blocked_session = $session (blocks that specific session)
     */
    public static function isBlocked(string $serviceType, string $date, ?string $session = null): bool
    {
        $query = static::where('service_type', $serviceType)
            ->where('blocked_date', $date);

        if ($session) {
            // Check if entire day is blocked OR specific session is blocked
            $query->where(function ($q) use ($session) {
                $q->whereNull('blocked_session')
                  ->orWhere('blocked_session', $session);
            });
        }

        return $query->exists();
    }

    /**
     * Get all blocked dates for a service as array of objects with date, reason, and session info.
     * Returns: [{ date: 'Y-m-d', reason: '...', blocked_session: null|'sesi_1'|'sesi_2' }, ...]
     */
    public static function getBlockedDatesArray(string $serviceType): array
    {
        return static::forService($serviceType)
            ->upcoming()
            ->orderBy('blocked_date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->blocked_date->format('Y-m-d'),
                'reason' => $item->reason,
                'blocked_session' => $item->blocked_session,
            ])
            ->toArray();
    }

    /**
     * Relationship: creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
