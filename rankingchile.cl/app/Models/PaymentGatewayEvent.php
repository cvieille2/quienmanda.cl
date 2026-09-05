<?php

namespace App\Models;

use App\Enums\GatewayProcessingResult;
use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayEvent extends Model
{
    public $timestamps = false;

    protected $table = 'payment_gateway_events';

    protected $fillable = [
        'payment_gateway', 'gateway_event_id', 'provider_transaction_id', 'external_reference',
        'event_type', 'payload_hash', 'received_at', 'processed_at', 'processing_result',
    ];

    protected $casts = [
        'payment_gateway'   => PaymentGateway::class,
        'received_at'       => 'datetime',
        'processed_at'      => 'datetime',
        'processing_result' => GatewayProcessingResult::class,
    ];

    public const RESULT_PENDING   = GatewayProcessingResult::Pending->value;
    public const RESULT_PROCESSED = GatewayProcessingResult::Processed->value;
    public const RESULT_DUPLICATE = GatewayProcessingResult::Duplicate->value;
    public const RESULT_IGNORED   = GatewayProcessingResult::Ignored->value;
    public const RESULT_ERROR     = GatewayProcessingResult::Error->value;
}
