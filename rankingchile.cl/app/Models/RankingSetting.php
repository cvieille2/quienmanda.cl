<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingSetting extends Model
{
    protected $table = 'ranking_settings';

    protected $fillable = [
        'scope', 'period_type', 'settlement_delay_minutes', 'minimum_support_clp',
        'maximum_support_clp', 'show_real_amounts', 'show_supporter_count',
        'max_public_positions', 'category_scope', 'category',
        'sharing_enabled', 'community_profiles_enabled', 'promotional_credits_enabled',
    ];

    protected $casts = [
        'settlement_delay_minutes'    => 'int',
        'minimum_support_clp'         => 'int',
        'maximum_support_clp'         => 'int',
        'show_real_amounts'           => 'boolean',
        'show_supporter_count'        => 'boolean',
        'max_public_positions'        => 'int',
        'sharing_enabled'             => 'boolean',
        'community_profiles_enabled'  => 'boolean',
        'promotional_credits_enabled' => 'boolean',
    ];
}