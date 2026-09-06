<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_click_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('ranking_period_id')->nullable()->constrained('ranking_periods')->nullOnDelete();
            $table->string('source', 50)->nullable();
            $table->text('destination_url');
            $table->string('session_id', 100)->nullable();
            $table->text('referrer')->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 150)->nullable();
            $table->timestamp('created_at');

            $table->index('profile_id', 'idx_outbound_clicks_profile_id');
            $table->index('ranking_period_id', 'idx_outbound_clicks_period_id');
            $table->index('created_at', 'idx_outbound_clicks_created_at');
            $table->index(['profile_id', 'created_at'], 'idx_outbound_clicks_profile_created');
            $table->index(['ranking_period_id', 'created_at'], 'idx_outbound_clicks_period_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_click_events');
    }
};
