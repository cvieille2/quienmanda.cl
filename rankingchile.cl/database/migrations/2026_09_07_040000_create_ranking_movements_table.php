<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete();
            $table->foreignId('support_transaction_id')->constrained('support_transactions')->restrictOnDelete();
            $table->unsignedInteger('amount_clp');
            $table->unsignedInteger('position_before')->nullable();
            $table->unsignedInteger('position_after');
            $table->unsignedBigInteger('total_before_clp')->default(0);
            $table->unsignedBigInteger('total_after_clp');
            $table->foreignId('leader_before_profile_id')->nullable()->constrained('profiles')->restrictOnDelete();
            $table->foreignId('leader_after_profile_id')->nullable()->constrained('profiles')->restrictOnDelete();
            $table->unsignedBigInteger('ranking_version');
            $table->dateTime('approved_at');
            $table->timestamps();

            $table->unique('support_transaction_id', 'uq_ranking_movements_transaction');
            $table->index(['profile_id', 'approved_at'], 'idx_ranking_movements_profile_date');
            $table->index(['approved_at', 'ranking_version'], 'idx_ranking_movements_date_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_movements');
    }
};
