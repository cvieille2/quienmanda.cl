<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('profile_category_id')->constrained('regions')->nullOnDelete();
            $table->index('region_id', 'idx_profiles_region_id');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
            $table->dropIndex('idx_profiles_region_id');
        });
    }
};
