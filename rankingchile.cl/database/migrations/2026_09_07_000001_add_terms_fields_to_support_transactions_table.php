<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_transactions', function (Blueprint $table) {
            $table->string('terms_version', 16)->nullable()->after('payer_age_declared_18');
            $table->dateTime('terms_accepted_at')->nullable()->after('terms_version');
        });
    }

    public function down(): void
    {
        Schema::table('support_transactions', function (Blueprint $table) {
            $table->dropColumn(['terms_version', 'terms_accepted_at']);
        });
    }
};
