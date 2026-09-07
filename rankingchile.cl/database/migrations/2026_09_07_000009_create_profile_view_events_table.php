<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_view_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('created_at');

            $table->index(['profile_id', 'created_at'], 'idx_profile_views_profile_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_view_events');
    }
};
