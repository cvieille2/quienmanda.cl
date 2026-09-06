<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->foreignId('profile_submission_id')->nullable()->constrained('profile_submissions')->nullOnDelete();
            $table->string('link_type', 20)->default('reference');
            $table->string('label', 120)->nullable();
            $table->string('original_url', 500);
            $table->string('normalized_url', 500);
            $table->string('host', 191)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('normalized_url', 'idx_profile_links_normalized_url');
            $table->index('profile_id', 'idx_profile_links_profile_id');
            $table->index('profile_submission_id', 'idx_profile_links_submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_links');
    }
};
