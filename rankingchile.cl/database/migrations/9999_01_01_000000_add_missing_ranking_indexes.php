<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add index for support_transactions table
        Schema::table('support_transactions', function (Blueprint $table) {
            // Index for filtering by profile and the new dynamic date range
            $table->index(['profile_id', 'ranking_qualified_at'], 'idx_tx_profile_qualified_at');
        });

        // Add index for profiles table
        Schema::table('profiles', function (Blueprint $table) {
            // Index for category, status, and type as recommended
            // Note: Using 'category' column as per migration file, not 'category_id'
            $table->index(['category', 'status', 'type'], 'idx_profiles_category_status_type');
        });
    }

    public function down(): void
    {
        // Drop the added indexes if migration is rolled back
        Schema::table('support_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_tx_profile_qualified_at');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('idx_profiles_category_status_type');
        });
    }
};