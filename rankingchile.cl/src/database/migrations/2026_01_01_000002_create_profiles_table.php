<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique('uq_profiles_public_id');  // ULID público único (D-044)
            $table->string('display_name', 120);
            $table->string('slug', 160)->unique('uq_profiles_slug');         // slug humano legible y único (D-044)
            $table->string('category', 60);
            $table->string('type', 20)->default('public_figure'); // public_figure|community
            $table->string('status', 16)->default('pending_review'); // pending_review|active|suspended|archived|rejected
            $table->string('verification_status', 16)->default('unverified'); // unverified|pending|verified
            $table->boolean('is_community_created')->default(false);
            $table->string('profile_image_url', 500)->nullable();
            $table->string('featured_media_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category'], 'idx_profiles_status_category');
            $table->index(['type', 'status'], 'idx_profiles_type_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};