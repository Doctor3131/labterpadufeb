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
     * Check if a specific date is blocked for a service.
     */
    public static function isBlocked(string $serviceType, string $date): bool
    {
        return static::where('service_type', $serviceType)
            ->where('blocked_date', $date)
            ->exists();
    }

    /**
     * Get all blocked dates for a service as a simple array of date strings.
     */
    public static function getBlockedDatesArray(string $serviceType): array
    {
        return static::forService($serviceType)
            ->upcoming()
            ->orderBy('blocked_date')
            ->pluck('blocked_date')
            ->map(fn ($date) => $date->format('Y-m-d'))
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
