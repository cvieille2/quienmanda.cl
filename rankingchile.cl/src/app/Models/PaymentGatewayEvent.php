<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayEvent extends Model
{
    public $timestamps = false; // received_at/processed_at se manejan manualmente

    protected $table = 'payment_gateway_events';

    protected $fillable = [
        'payment_gateway', 'gateway_event_id', 'provider_transaction_id', 'external_reference',
        'event_type', 'payload_hash', 'received_at', 'processed_at', 'processing_result',
    ];

    protected $casts = [
        'received_at'  => 'datetime',
        'processed_at' => 'datetime',
    ];

    public const RESULT_PENDING   = 'pending';
    public const RESULT_PROCESSED = 'processed';
    public const RESULT_DUPLICATE = 'duplicate';
    public const RESULT_ERROR     = 'error';
}
