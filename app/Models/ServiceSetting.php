<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSetting extends Model
{
    protected $fillable = [
        'service_type',
        'key',
        'value',
        'updated_by',
    ];

    /**
     * Get a setting value for a service.
     */
    public static function getValue(string $serviceType, string $key, string $default = null): ?string
    {
        $setting = static::where('service_type', $serviceType)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value for a service.
     */
    public static function setValue(string $serviceType, string $key, string $value, ?int $updatedBy = null): void
    {
        static::updateOrCreate(
            ['service_type' => $serviceType, 'key' => $key],
            ['value' => $value, 'updated_by' => $updatedBy]
        );
    }

    /**
     * Check if a boolean setting is enabled.
     */
    public static function isEnabled(string $serviceType, string $key): bool
    {
        return static::getValue($serviceType, $key, '0') === '1';
    }

    /**
     * Relationship: updater.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
