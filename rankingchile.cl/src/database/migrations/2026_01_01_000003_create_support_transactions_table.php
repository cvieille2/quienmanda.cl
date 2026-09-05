<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_transactions', function (Blueprint $table) {
            $table->id();
            // Fuente de verdad financiera del ranking.
            // FKs RESTRICTIVAS (sin cascadeOnDelete) a propósito (D-045): jamás se pierde el
            // historial de pagos reales si se borra un periodo/perfil (retención DTE + NO-GO).
            // profiles usa softDeletes (nunca borrado físico), por lo que RESTRICT nunca dispara.
            $table->foreignId('ranking_period_id')->constrained('ranking_periods')->restrictOnDelete();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete();
            $table->unsignedInteger('amount_clp');              // integer CLP no negativo, >= 1000 (mínimo)
            $table->char('currency', 3)->default('CLP');
            $table->enum('type', ['real', 'promotional'])->default('real');
            $table->string('status', 16)->default('pending');    // pending|approved|failed|disputed|refunded|reversed
            $table->string('supporter_name', 64)->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->string('gateway_payer_id', 128)->nullable();
            $table->string('payer_reference_hash', 128)->nullable(); // hash supporter_count / riesgo
            $table->string('payer_email_hash', 128)->nullable();     // hash email normalizado (NUNCA crudo)
            $table->string('signed_random_cookie', 160)->nullable();
            $table->string('payment_provider_account', 128)->nullable();
            $table->string('fan_email', 191)->nullable();        // D-33: email REAL solo DTE/boleta + retención
            $table->string('device_risk_signals', 255)->nullable();
            $table->boolean('is_payer_identity_resolved')->default(false);
            $table->boolean('payer_age_declared_18')->default(false); // edad mínima 18
            $table->string('payment_gateway', 16)->default('mercadopago'); // D-30 única pasarela MVP
            $table->string('provider_transaction_id', 128)->nullable()->unique('uq_tx_provider_transaction_id'); // idempotencia
            $table->char('external_reference', 26)->nullable()->unique('uq_tx_external_reference');             // ULID público (idempotencia)
            $table->string('gateway_status', 32)->nullable();
            $table->dateTime('checkout_created_at')->nullable();
            $table->dateTime('provider_approved_at')->nullable();
            $table->dateTime('webhook_received_at')->nullable();
            $table->dateTime('ranking_qualified_at')->nullable(); // determina el periodo competitivo (half-open D-041)
            $table->dateTime('disputed_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->dateTime('reversed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['ranking_period_id', 'type', 'status', 'ranking_qualified_at'],
                'idx_rank_period_type_status_qat'
            );
            $table->index(['profile_id', 'ranking_period_id'], 'idx_tx_profile_period');
            $table->index('payer_reference_hash', 'idx_tx_payer_reference_hash');
            $table->index('fan_email', 'idx_tx_fan_email');

            // Dinero CLP integer: nunca puede ser 0 ni negativo. Los mínimos/máximos por periodo
            // (configurables) NO se hardcodean aquí: se validan vs la configuration CONGELADA.
            $table->check(fn (Blueprint $table) => $table->where('amount_clp', '>', 0));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_transactions');
    }
};