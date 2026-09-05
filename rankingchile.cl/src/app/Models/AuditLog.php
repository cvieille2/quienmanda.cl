<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false; // se usa created_at manual

    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_type', 'actor_id', 'event_type', 'entity_type', 'entity_id', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Estándar PSR (nombres no reservados, prefijo ACTOR_)
    public const ACTOR_SYSTEM = 'system';
    public const ACTOR_ADMIN  = 'admin';
    public const ACTOR_GATEWAY = 'gateway';
    public const ACTOR_USER   = 'user';
    public const ACTOR_SCHEDULED_TASK = 'scheduled_task';

    // event_type oficiales (rebaseline v3.1.0: period_* reemplaza cycle_*)
    public const EVT_PAYMENT_CREATED  = 'payment_created';
    public const EVT_PAYMENT_APPROVED = 'payment_approved';
    public const EVT_PAYMENT_FAILED   = 'payment_failed';
    public const EVT_PAYMENT_DISPUTED = 'payment_disputed';
    public const EVT_PAYMENT_REFUNDED = 'payment_refunded';
    public const EVT_PAYMENT_REVERSED = 'payment_reversed';
    public const EVT_PROFILE_CREATED  = 'profile_created';
    public const EVT_PROFILE_UPDATED  = 'profile_updated';
    public const EVT_PROFILE_SUSPENDED = 'profile_suspended';
    public const EVT_PROFILE_RESTORED = 'profile_restored';
    public const EVT_PROFILE_VERIFIED = 'profile_verified';
    public const EVT_PROFILE_REJECTED = 'profile_rejected';
    public const EVT_PROFILE_REPORT_RESOLVED = 'profile_report_resolved';
    public const EVT_PROMOTIONAL_CREDIT_CREATED = 'promotional_credit_created';
    public const EVT_PAYMENT_DELTA_QUEUED = 'payment_delta_queued';
    public const EVT_RANKING_DELTA_RECALCULATED = 'ranking_delta_recalculated';

    // Dominio de periodos (D-040): identifica el periodo competitivo, ya no solo semanal.
    public const EVT_PERIOD_CREATED    = 'period_created';
    public const EVT_PERIOD_ACTIVATED  = 'period_activated';
    public const EVT_PERIOD_CLOSED     = 'period_closed';
    public const EVT_PERIOD_SETTLED    = 'period_settled';
    public const EVT_PERIOD_SNAPSHOTTED = 'period_snapshotted';
    public const EVT_PERIOD_CANCELLED  = 'period_cancelled';
    public const EVT_RANKING_POSITION_CHANGED = 'ranking_position_changed';
}
