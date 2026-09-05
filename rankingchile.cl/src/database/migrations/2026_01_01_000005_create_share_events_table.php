<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles');
            // Evento de atribución viral de soporte: si la transacción se elimina, el share
            // event se conserva con soporte null (no se borra el registro de difusión).
            $table->foreignId('support_transaction_id')->nullable()->constrained('support_transactions')->nullOnDelete();
            $table->string('source', 40)->nullable();   // share_trophy|...
            $table->string('period_code', 32)->nullable(); // renombrado de cycle_code (rebaseline)
            $table->string('referrer_id', 160)->nullable();
            $table->string('utm_source', 80)->nullable();
            $table->string('utm_medium', 80)->nullable();
            $table->string('utm_campaign', 80)->nullable();
            $table->timestamps();

            $table->index('profile_id', 'idx_share_profile');
            $table->index('support_transaction_id', 'idx_share_tx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_events');
    }
};