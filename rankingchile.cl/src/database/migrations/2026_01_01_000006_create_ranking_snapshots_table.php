<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resultado final INMUTABLE del periodo + settlement window + delta recalc (D-042).
        Schema::create('ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranking_period_id')->constrained('ranking_periods')->restrictOnDelete(); // D-045
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete();               // D-045
            $table->unsignedInteger('final_position');              // posicion en el periodo, >= 1
            $table->unsignedBigInteger('total_real_clp')->default(0);       // acumulado CLP (alto volumen, >= 0)
            $table->unsignedBigInteger('total_promotional_clp')->default(0); // separado, no rankea
            $table->unsignedInteger('total_supporters')->default(0);        // count, >= 0
            $table->dateTime('total_reached_at')->nullable(); // desempate inicial (ganador temprano)
            $table->unsignedInteger('revision')->default(0);  // 0 = snapshot original; >0 = delta recalc
            // FK auto-referenciada al snapshot original (evidencia). Como los snapshots son
            // inmutables (guard en el Model bloquea update/delete), el nullOnDelete es defensivo
            // y solo aplica si se forzara un borrado manual a nivel DB.
            $table->foreignId('is_revision_of')->nullable()->constrained('ranking_snapshots')->nullOnDelete();
            $table->boolean('immutable')->default(true);
            $table->timestamps();

            $table->unique(
                ['ranking_period_id', 'profile_id', 'revision'],
                'uq_snapshot_period_profile_rev'
            );
            $table->index(['ranking_period_id', 'final_position'], 'idx_snapshot_period_pos');

            // final_position es position de ranking: >= 1 (nunca 0).
            $table->check(fn (Blueprint $table) => $table->where('final_position', '>=', 1));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_snapshots');
    }
};