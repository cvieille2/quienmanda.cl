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
            $table->char('public_id', 26)->unique('uq_periods_public_id');
            $table->string('code', 32)->unique('uq_periods_code');
            $table->string('period_type', 16)->default('weekly');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('settlement_delay_minutes')->default(5);
            $table->string('status', 24)->default('draft');
            $table->json('configuration');
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('settled_at')->nullable();
            $table->dateTime('snapshotted_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at'], 'idx_periods_status_starts');
            $table->index(['status', 'ends_at'], 'idx_periods_status_ends');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_periods');
    }
};
