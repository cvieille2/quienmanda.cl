<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->text('project_description')->nullable()->after('display_name');
            $table->string('instagram_url', 500)->nullable()->after('project_description');
            $table->string('tiktok_url', 500)->nullable()->after('instagram_url');
            $table->string('x_url', 500)->nullable()->after('tiktok_url');
            $table->string('website_url', 500)->nullable()->after('x_url');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['project_description', 'instagram_url', 'tiktok_url', 'x_url', 'website_url']);
        });
    }
};