<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_categories', function (Blueprint $table) {
            $table->string('short_name', 60)->nullable()->after('name');
            $table->string('hero_title', 160)->nullable()->after('description');
            $table->text('hero_description')->nullable()->after('hero_title');
            $table->string('seo_title', 200)->nullable()->after('hero_description');
            $table->string('seo_description', 320)->nullable()->after('seo_title');
            $table->longText('seo_content')->nullable()->after('seo_description');
            $table->string('icon', 60)->nullable()->after('seo_content');
            $table->boolean('show_in_navigation')->default(true)->after('icon');
            $table->boolean('is_indexable')->default(true)->after('show_in_navigation');
            $table->integer('sort_order')->default(0)->after('is_indexable');

            $table->index(['is_active', 'show_in_navigation', 'sort_order'], 'idx_categories_nav');
        });
    }

    public function down(): void
    {
        Schema::table('profile_categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_nav');
            $table->dropColumn([
                'short_name',
                'hero_title',
                'hero_description',
                'seo_title',
                'seo_description',
                'seo_content',
                'icon',
                'show_in_navigation',
                'is_indexable',
                'sort_order',
            ]);
        });
    }
};
