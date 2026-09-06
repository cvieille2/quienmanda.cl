<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('profile_category_id')->nullable()->after('slug')->constrained('profile_categories')->nullOnDelete();
            $table->foreignId('profile_submission_id')->nullable()->after('profile_category_id')->constrained('profile_submissions')->nullOnDelete();
            $table->string('source_type', 32)->nullable()->after('profile_submission_id');
            $table->string('source_url', 500)->nullable()->after('source_type');
            $table->string('onboarding_normalized_url', 500)->nullable()->after('source_url');
            $table->string('onboarding_detected_title', 160)->nullable()->after('onboarding_normalized_url');
            $table->index(['profile_category_id', 'status'], 'idx_profiles_category_status');
            $table->index(['profile_submission_id'], 'idx_profiles_submission_id');
        });

        Schema::table('profile_submissions', function (Blueprint $table) {
            $table->foreignId('profile_category_id')->nullable()->after('category')->constrained('profile_categories')->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->after('profile_category_id')->constrained('profiles')->nullOnDelete();
            $table->string('source_type', 32)->nullable()->after('profile_id');
            $table->string('source_url', 500)->nullable()->after('source_type');
            $table->string('normalized_url', 500)->nullable()->after('source_url');
            $table->string('detected_title', 160)->nullable()->after('normalized_url');
            $table->foreignId('duplicate_profile_id')->nullable()->after('detected_title')->constrained('profiles')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->change();
            $table->index('profile_id', 'idx_submissions_profile_id');
            $table->index('profile_category_id', 'idx_submissions_category_id');
            $table->index('duplicate_profile_id', 'idx_submissions_duplicate_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('profile_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicate_profile_id');
            $table->dropConstrainedForeignId('profile_id');
            $table->dropConstrainedForeignId('profile_category_id');
            $table->dropIndex('idx_submissions_profile_id');
            $table->dropIndex('idx_submissions_category_id');
            $table->dropIndex('idx_submissions_duplicate_profile_id');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_submission_id');
            $table->dropConstrainedForeignId('profile_category_id');
            $table->dropIndex('idx_profiles_category_status');
            $table->dropIndex('idx_profiles_submission_id');
        });
    }
};
