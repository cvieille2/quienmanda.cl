<?php

namespace App\Models;

use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RankingPeriod extends Model
{
    protected $table = 'ranking_periods';

    protected $fillable = [
        'public_id', 'code', 'period_type', 'starts_at', 'ends_at',
        'settlement_delay_minutes', 'status', 'configuration',
        'closed_at', 'settled_at', 'snapshotted_at', 'cancelled_at',
    ];

    protected $casts = [
        'period_type'              => RankingPeriodType::class,
        'starts_at'                => 'datetime',
        'ends_at'                  => 'datetime',
        'settlement_delay_minutes' => 'int',
        'configuration'            => 'array',
        'status'                   => RankingPeriodStatus::class,
        'closed_at'                => 'datetime',
        'settled_at'               => 'datetime',
        'snapshotted_at'           => 'datetime',
        'cancelled_at'             => 'datetime',
    ];

    // D-042: flujo de estados activo -> closed_pending_settlement -> closed -> snapshotted.
    public const STATUS_DRAFT_BLOQUE  = RankingPeriodStatus::Draft->value;
    public const STATUS_SCHEDULED     = RankingPeriodStatus::Scheduled->value;
    public const STATUS_ACTIVE        = RankingPeriodStatus::Active->value;
    public const STATUS_CLOSED_PENDING_SETTLEMENT = RankingPeriodStatus::ClosedPendingSettlement->value;
    public const STATUS_CLOSED        = RankingPeriodStatus::Closed->value;
    public const STATUS_SNAPSHOTTED   = RankingPeriodStatus::Snapshotted->value;
    public const STATUS_CANCELLED     = RankingPeriodStatus::Cancelled->value; // D-043: nunca publica ni rankea
    public const TYPE_DEFAULT         = RankingPeriodType::Weekly->value;

    /** Asignación half-open (D-041): un instante pertenece si starts_at <= t < ends_at. */
    public function includes(SupportTransaction|\Carbon\CarbonInterface $at): bool
    {
        $instant = $at instanceof SupportTransaction ? $at->ranking_qualified_at : $at;

        return $instant !== null
            && $this->starts_at->lessThanOrEqualTo($instant)
            && $this->ends_at->greaterThan($instant); // ends_at es límite EXCLUSIVO
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SupportTransaction::class, 'ranking_period_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(RankingSnapshot::class, 'ranking_period_id');
    }

    protected static function booted(): void
    {
        static::creating(function (RankingPeriod $period) {
            if (! $period->public_id) {
                $period->public_id = (string) Str::ulid();
            }
        });
    }
}