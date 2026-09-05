<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete(); // D-045: softDeletes en profiles, RESTRICT nunca dispara
            $table->string('reason', 32); // impersonation|private_person|minor|harassment|incorrect_information|copyright|image_rights|malicious_link|other
            $table->text('description')->nullable();
            $table->string('reporter_email_hash', 128)->nullable();
            $table->string('status', 16)->default('open'); // open|under_review|resolved|rejected
            $table->unsignedBigInteger('reviewed_by')->nullable(); // FK lógica -> users.id (000012)
            $table->dateTime('reviewed_at')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();

            $table->index(['profile_id', 'status'], 'idx_report_profile_status');
            $table->index(['status', 'reviewed_at'], 'idx_report_status_reviewed');
            $table->index('reporter_email_hash', 'idx_report_reporter_email_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_reports');
    }
};