<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    public $timestamps = false;

    protected $table = 'feature_flags';

    protected $fillable = ['key', 'value', 'description', 'updated_at'];

    protected $casts = [
        'value'       => 'json',
        'updated_at'  => 'datetime',
    ];

    public static function enabled(string $key, bool $default = false): bool
    {
        return (bool) self::cacheValue($key, $default);
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return self::cacheValue($key, $default);
    }

    protected static function cacheValue(string $key, mixed $default): mixed
    {
        return Cache::remember("feature_flag:{$key}", 5, function () use ($key, $default) {
            $flag = static::where('key', $key)->first();
            return $flag ? $flag->value : $default;
        });
    }

    // Flags MVP
    public const KEY_PAYMENTS_ENABLED   = 'payments_enabled';
    public const KEY_COMMUNITY_SUBMISSION = 'community_profile_submission_enabled';
    public const KEY_PUBLIC_RANKING     = 'public_ranking_enabled';
    public const KEY_PROMOTIONAL_CREDITS = 'promotional_credits_enabled';
    public const KEY_SHARING_ENABLED    = 'sharing_enabled';
    public const KEY_FINANCIAL_KILL_SWITCH = 'financial_kill_switch'; // NO_GO_004
}
