<?php

namespace App\Services;

use App\Models\FeatureFlag;

/**
 * Feature flags consultables en runtime con cache corto (5 s).
 * payments_enabled=false es el FINANCIAL KILL SWITCH (NO_GO_004): debe poder desactivarse SIN redeploy.
 */
class FeatureFlagsService
{
    // Cache corto (5 s): permite apagar sin redeploy manteniendo consistencia razonable.
    public function isEnabled(string $name, bool $default = false): bool
    {
        return FeatureFlag::enabled($name, $default);
    }

    public function value(string $name, mixed $default = null): mixed
    {
        return FeatureFlag::value($name, $default);
    }

    public function set(string $name, mixed $value, ?int $adminId = null): void
    {
        FeatureFlag::updateOrCreate(
            ['key' => $name],
            ['value' => $value, 'updated_at' => now()]
        );
        cache()->forget("feature_flag:{$name}");
        audit(
            'feature_flag_changed',
            'feature_flag',
            null,
            ['name' => $name, 'value' => $value],
            'admin',
            $adminId
        );
    }
}
