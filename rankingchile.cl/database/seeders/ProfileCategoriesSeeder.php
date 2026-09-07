<?php

namespace Database\Seeders;

use App\Models\ProfileCategory;
use Illuminate\Database\Seeder;

class ProfileCategoriesSeeder extends Seeder
{
    /**
     * Categorias base para el formulario de onboarding.
     */
    public function run(): void
    {
        $categories = [
            ['slug' => 'tech', 'name' => 'Tecnología', 'short_name' => 'Tech', 'icon' => '💻', 'sort_order' => 1],
            ['slug' => 'gaming', 'name' => 'Gaming', 'short_name' => 'Gaming', 'icon' => '🎮', 'sort_order' => 2],
            ['slug' => 'music', 'name' => 'Música', 'short_name' => 'Música', 'icon' => '🎵', 'sort_order' => 3],
            ['slug' => 'comedy', 'name' => 'Comedia', 'short_name' => 'Comedia', 'icon' => '🎭', 'sort_order' => 4],
            ['slug' => 'education', 'name' => 'Educación', 'short_name' => 'Educación', 'icon' => '📚', 'sort_order' => 5],
            ['slug' => 'fitness', 'name' => 'Fitness', 'short_name' => 'Fitness', 'icon' => '💪', 'sort_order' => 6],
            ['slug' => 'food', 'name' => 'Cocina', 'short_name' => 'Cocina', 'icon' => '🍳', 'sort_order' => 7],
            ['slug' => 'beauty', 'name' => 'Moda y belleza', 'short_name' => 'Belleza', 'icon' => '💅', 'sort_order' => 8],
            ['slug' => 'business', 'name' => 'Negocios y finanzas', 'short_name' => 'Negocios', 'icon' => '📈', 'sort_order' => 9],
            ['slug' => 'art', 'name' => 'Arte y diseño', 'short_name' => 'Arte', 'icon' => '🎨', 'sort_order' => 10],
            ['slug' => 'lifestyle', 'name' => 'Vlogs y lifestyle', 'short_name' => 'Lifestyle', 'icon' => '🌴', 'sort_order' => 11],
            ['slug' => 'news', 'name' => 'Actualidad', 'short_name' => 'Actualidad', 'icon' => '🗞️', 'sort_order' => 12],
            ['slug' => 'sports', 'name' => 'Deportes', 'short_name' => 'Deportes', 'icon' => '🏆', 'sort_order' => 13],
            ['slug' => 'science', 'name' => 'Ciencia', 'short_name' => 'Ciencia', 'icon' => '🔬', 'sort_order' => 14],
            ['slug' => 'saas', 'name' => 'SaaS y software', 'short_name' => 'SaaS', 'icon' => '🛠️', 'sort_order' => 15],
            ['slug' => 'app', 'name' => 'Apps y móvil', 'short_name' => 'Apps', 'icon' => '📱', 'sort_order' => 16],
            ['slug' => 'shop', 'name' => 'Tienda y ecommerce', 'short_name' => 'Shop', 'icon' => '🛒', 'sort_order' => 17],
            ['slug' => 'indie', 'name' => 'Indie y proyectos propios', 'short_name' => 'Indie', 'icon' => '🚀', 'sort_order' => 18],
        ];

        foreach ($categories as $category) {
            ProfileCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'short_name' => $category['short_name'],
                    'description' => 'Categoria base para el onboarding self-service.',
                    'hero_title' => null,
                    'hero_description' => null,
                    'seo_title' => null,
                    'seo_description' => null,
                    'seo_content' => null,
                    'icon' => $category['icon'],
                    'is_active' => true,
                    'show_in_navigation' => true,
                    'is_indexable' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}
