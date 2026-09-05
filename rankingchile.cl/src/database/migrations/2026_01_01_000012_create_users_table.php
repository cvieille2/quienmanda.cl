<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->string('role', 24)->default('admin'); // super_admin|moderator|finance_reviewer
            $table->string('status', 16)->default('active');
            $table->dateTime('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['role', 'status'], 'idx_users_role_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};