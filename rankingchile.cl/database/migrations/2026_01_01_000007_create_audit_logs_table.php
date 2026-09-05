<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Auditoría append-only. actor_type/actor_id son FK lógicas hacia users (no física por orden).
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 32);   // system|admin|gateway|user|scheduled_task
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event_type', 64);
            $table->string('entity_type', 32);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entity_type', 'entity_id'], 'idx_audit_entity');
            $table->index(['event_type', 'created_at'], 'idx_audit_event_created');
            $table->index(['actor_type', 'actor_id'], 'idx_audit_actor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
