<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking_periods', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique('uq_periods_public_id'); // ULID público único (D-044)
            $table->string('code', 32)->unique('uq_periods_code');         // 'W2026-35' | 'M2026-08' | 'D2026-08-30'
            $table->string('period_type', 16)->default('weekly');          // daily|weekly|monthly|quarterly|semester|yearly|custom
            $table->dateTime('starts_at');                                 // inicio (inclusivo), UTC
            $table->dateTime('ends_at');                                   // LIMITE EXCLUSIVO (half-open D-041)
            $table->unsignedInteger('settlement_delay_minutes')->default(5); // D-042: ventana de settlement tras ends_at
            $table->string('status', 24)->default('draft');                // draft|scheduled|active|closed_pending_settlement|closed|snapshotted|cancelled
            $table->json('configuration');                                 // copia CONGELADA (D-005/D-048)
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('settled_at')->nullable();
            $table->dateTime('snapshotted_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at'], 'idx_periods_status_starts');
            $table->index(['status', 'ends_at'], 'idx_periods_status_ends');

            // Integridad half-open (D-041): un periodo con length <= 0 es inválido (nunca debe existir).
            $table->check(fn (Blueprint $table) => $table->whereColumn('starts_at', '<', 'ends_at'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_periods');
    }
};