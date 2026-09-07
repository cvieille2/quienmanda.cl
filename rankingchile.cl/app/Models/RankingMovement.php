<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingMovement extends Model
{
    protected $fillable = [
        'profile_id', 'support_transaction_id', 'amount_clp',
        'position_before', 'position_after', 'total_before_clp', 'total_after_clp',
        'leader_before_profile_id', 'leader_after_profile_id',
        'ranking_version', 'approved_at',
    ];

    protected $casts = [
        'amount_clp' => 'int',
        'position_before' => 'int',
        'position_after' => 'int',
        'total_before_clp' => 'int',
        'total_after_clp' => 'int',
        'ranking_version' => 'int',
        'approved_at' => 'datetime',
    ];

    public function profile(): BelongsTo { return $this->belongsTo(Profile::class); }
    public function supportTransaction(): BelongsTo { return $this->belongsTo(SupportTransaction::class); }
    public function leaderBefore(): BelongsTo { return $this->belongsTo(Profile::class, 'leader_before_profile_id'); }
    public function leaderAfter(): BelongsTo { return $this->belongsTo(Profile::class, 'leader_after_profile_id'); }
}
