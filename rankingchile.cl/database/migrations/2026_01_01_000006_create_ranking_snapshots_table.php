<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranking_period_id')->constrained('ranking_periods')->restrictOnDelete();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete();
            $table->unsignedInteger('final_position');
            $table->unsignedBigInteger('total_real_clp')->default(0);
            $table->unsignedBigInteger('total_promotional_clp')->default(0);
            $table->unsignedInteger('total_supporters')->default(0);
            $table->dateTime('total_reached_at')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('is_revision_of')->nullable()->constrained('ranking_snapshots')->nullOnDelete();
            $table->boolean('immutable')->default(true);
            $table->timestamps();

            $table->unique(['ranking_period_id', 'profile_id', 'revision'], 'uq_snapshot_period_profile_rev');
            $table->index(['ranking_period_id', 'final_position'], 'idx_snapshot_period_pos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_snapshots');
    }
};
