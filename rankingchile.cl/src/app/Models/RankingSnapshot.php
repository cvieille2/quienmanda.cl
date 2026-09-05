<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingSnapshot extends Model
{
    protected $table = 'ranking_snapshots';

    protected $fillable = [
        'ranking_period_id', 'profile_id', 'final_position', 'total_real_clp',
        'total_promotional_clp', 'total_supporters', 'total_reached_at',
        'revision', 'is_revision_of', 'immutable',
    ];

    protected $casts = [
        'final_position'        => 'int',
        'total_real_clp'        => 'int',
        'total_promotional_clp' => 'int',
        'total_supporters'      => 'int',
        'total_reached_at'      => 'datetime',
        'revision'              => 'int',
        'immutable'             => 'boolean',
    ];

    public function rankingPeriod(): BelongsTo
    {
        return $this->belongsTo(RankingPeriod::class, 'ranking_period_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    /** Snapshot oficial del periodo para un perfil dado = el de MAYOR revision (delta recalc D-042). */
    public static function officialFor(int $periodId, int $profileId): ?self
    {
        return static::where('ranking_period_id', $periodId)
            ->where('profile_id', $profileId)
            ->orderByDesc('revision')
            ->first();
    }

    protected static function booted(): void
    {
        // Guard de inmutabilidad: NINGÚN registro de snapshot (revision 0 o >0) se actualiza ni elimina.
        static::updating(function (RankingSnapshot $snap) {
            throw new \LogicException('Ranking snapshot is immutable and cannot be modified.');
        });
        static::deleting(function (RankingSnapshot $snap) {
            throw new \LogicException('Ranking snapshot is immutable and cannot be deleted.');
        });
    }
}