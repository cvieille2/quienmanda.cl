<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only: log de eventos crudos de la pasarela (auditoría + dedupe de webhook).
        Schema::create('payment_gateway_events', function (Blueprint $table) {
            $table->id();
            $table->string('payment_gateway', 16)->default('mercadopago');
            $table->string('gateway_event_id', 128);
            $table->string('provider_transaction_id', 128)->nullable();
            $table->char('external_reference', 26)->nullable();
            $table->string('event_type', 64)->nullable(); // notification|confirmation|refund|chargeback|...
            $table->char('payload_hash', 64);            // sha256 del payload
            $table->dateTime('received_at');
            $table->dateTime('processed_at')->nullable();
            $table->string('processing_result', 16)->default('pending'); // pending|processed|duplicate|error
            $table->timestamps();

            $table->unique(['payment_gateway', 'gateway_event_id'], 'uq_gw_event'); // dedupe webhook
            $table->index(['processing_result', 'received_at'], 'idx_gw_processing');
            $table->index('external_reference', 'idx_gw_external_reference');
            $table->index('provider_transaction_id', 'idx_gw_provider_transaction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_events');
    }
};
