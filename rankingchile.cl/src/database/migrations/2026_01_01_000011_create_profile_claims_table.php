<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete(); // D-045: softDeletes en profiles, RESTRICT nunca dispara
            $table->string('claimant_name', 120);
            $table->string('claimant_email', 191);
            $table->string('claimant_role', 60)->nullable();
            $table->string('evidence_reference', 500)->nullable();
            $table->string('status', 16)->default('pending'); // pending|verified|rejected
            $table->unsignedBigInteger('reviewed_by')->nullable(); // FK lógica -> users.id (000012)
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['profile_id', 'status'], 'idx_claim_profile_status');
            $table->index(['status', 'reviewed_at'], 'idx_claim_status_reviewed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_claims');
    }
};