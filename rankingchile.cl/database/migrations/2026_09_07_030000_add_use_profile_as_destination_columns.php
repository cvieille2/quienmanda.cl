<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('profiles', 'use_profile_as_destination')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->boolean('use_profile_as_destination')->default(false)->after('onboarding_detected_title');
            });
        }

        if (! Schema::hasColumn('profile_submissions', 'use_profile_as_destination')) {
            Schema::table('profile_submissions', function (Blueprint $table) {
                $table->boolean('use_profile_as_destination')->default(false)->after('detected_title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('profile_submissions', 'use_profile_as_destination')) {
            Schema::table('profile_submissions', function (Blueprint $table) {
                $table->dropColumn('use_profile_as_destination');
            });
        }

        if (Schema::hasColumn('profiles', 'use_profile_as_destination')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('use_profile_as_destination');
            });
        }
    }
};
