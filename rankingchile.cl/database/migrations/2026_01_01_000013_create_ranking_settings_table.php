<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // D-048: fila única con los defaults usados al CREAR ranking_periods.
        // Los cambios aquí NO afectan al periodo activo (su configuration JSON está congelada).
        Schema::create('ranking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 16)->default('default');               // singleton: UNIQUE(scope)
            $table->string('period_type', 16)->default('weekly');
            $table->unsignedInteger('settlement_delay_minutes')->default(5);
            $table->unsignedInteger('minimum_support_clp')->default(1000);
            $table->unsignedInteger('maximum_support_clp')->default(500000);
            $table->boolean('show_real_amounts')->default(true);
            $table->boolean('show_supporter_count')->default(true);
            $table->unsignedInteger('max_public_positions')->default(10);
            $table->string('category_scope', 16)->default('all');  // all|single (D-006, futuro)
            $table->string('category', 60)->nullable();
            $table->boolean('sharing_enabled')->default(true);
            $table->boolean('community_profiles_enabled')->default(false);
            $table->boolean('promotional_credits_enabled')->default(false);
            $table->timestamps();

            // D-048: fila única de defaults. UNIQUE(scope) impone singleton a nivel BD.
            $table->unique('scope', 'uq_settings_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_settings');
    }
};