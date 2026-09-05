<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('display_name', 120);
            $table->string('category', 60);
            $table->string('profile_image_url', 500)->nullable();
            $table->string('featured_media_url', 500)->nullable();
            $table->string('submitted_by_session_id', 160)->nullable();
            $table->string('submitted_email_hash', 128)->nullable();
            $table->string('status', 16)->default('pending'); // pending|approved|rejected
            $table->unsignedBigInteger('reviewed_by')->nullable(); // FK lógica -> users.id (000012)
            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('submitted_by_session_id', 'idx_sub_session');
            $table->index(['status', 'reviewed_at'], 'idx_sub_status_reviewed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_submissions');
    }
};