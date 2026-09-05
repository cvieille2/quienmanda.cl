<?php

namespace App\Models;

use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SupportTransaction extends Model
{
    protected $table = 'support_transactions';

    protected $fillable = [
        'ranking_period_id', 'profile_id', 'amount_clp', 'currency', 'type', 'status',
        'supporter_name', 'is_anonymous', 'gateway_payer_id', 'payer_reference_hash',
        'payer_email_hash', 'signed_random_cookie', 'payment_provider_account', 'fan_email',
        'device_risk_signals', 'is_payer_identity_resolved', 'payer_age_declared_18',
        'payment_gateway', 'provider_transaction_id', 'external_reference', 'gateway_status',
        'checkout_created_at', 'provider_approved_at', 'webhook_received_at', 'ranking_qualified_at',
        'disputed_at', 'refunded_at', 'reversed_at',
    ];

    protected $casts = [
        'amount_clp'                 => 'int',
        'type'                       => SupportTransactionType::class,
        'status'                     => SupportTransactionStatus::class,
        'is_anonymous'               => 'boolean',
        'is_payer_identity_resolved' => 'boolean',
        'payer_age_declared_18'      => 'boolean',
        'checkout_created_at'        => 'datetime',
        'provider_approved_at'       => 'datetime',
        'webhook_received_at'        => 'datetime',
        'ranking_qualified_at'       => 'datetime',
        'disputed_at'                => 'datetime',
        'refunded_at'                => 'datetime',
        'reversed_at'                => 'datetime',
    ];

    public const TYPE_REAL        = SupportTransactionType::Real->value;
    public const TYPE_PROMOTIONAL = SupportTransactionType::Promotional->value;

    public const STATUS_PENDING  = SupportTransactionStatus::Pending->value;
    public const STATUS_APPROVED = SupportTransactionStatus::Approved->value;
    public const STATUS_FAILED   = SupportTransactionStatus::Failed->value;
    public const STATUS_DISPUTED = SupportTransactionStatus::Disputed->value;
    public const STATUS_REFUNDED = SupportTransactionStatus::Refunded->value;
    public const STATUS_REVERSED = SupportTransactionStatus::Reversed->value;

    public const GATEWAY_MERCADOPAGO = 'mercadopago';

    public const MIN_AMOUNT_CLP = 1000;

    public function rankingPeriod(): BelongsTo
    {
        return $this->belongsTo(RankingPeriod::class, 'ranking_period_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function shareEvents()
    {
        return $this->hasMany(ShareEvent::class, 'support_transaction_id');
    }

    /** Identificador público ULID (nunca exponer PK interna), generado si no existe. */
    public function resolveExternalReference(): string
    {
        if (! $this->external_reference) {
            $this->external_reference = (string) Str::ulid();
        }
        return $this->external_reference;
    }
}
