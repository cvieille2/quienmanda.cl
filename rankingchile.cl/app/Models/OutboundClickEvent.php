<?php

namespace App\Models;

use App\Enums\OutboundClickSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundClickEvent extends Model
{
    protected $table = 'outbound_click_events';

    public const UPDATED_AT = null;

    protected $fillable = [
        'profile_id',
        'ranking_period_id',
        'source',
        'destination_url',
        'session_id',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'created_at',
    ];

    protected $casts = [
        'source' => OutboundClickSource::class,
        'created_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function rankingPeriod(): BelongsTo
    {
        return $this->belongsTo(RankingPeriod::class, 'ranking_period_id');
    }
}
