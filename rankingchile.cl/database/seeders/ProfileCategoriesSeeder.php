<?php

namespace Database\Seeders;

use App\Models\ProfileCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfileCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Actualidad', 'short_name' => 'Actualidad', 'description' => 'Categoría demo para actualidad', 'sort_order' => 10],
            ['name' => 'Apps y móvil', 'short_name' => 'Apps y móvil', 'description' => 'Categoría demo para apps y móvil', 'sort_order' => 20],
            ['name' => 'Arte y diseño', 'short_name' => 'Arte y diseño', 'description' => 'Categoría demo para arte y diseño', 'sort_order' => 30],
            ['name' => 'Balada', 'short_name' => 'Balada', 'description' => 'Categoría demo para balada', 'sort_order' => 40],
            ['name' => 'Ciencia', 'short_name' => 'Ciencia', 'description' => 'Categoría demo para ciencia', 'sort_order' => 50],
            ['name' => 'Cocina', 'short_name' => 'Cocina', 'description' => 'Categoría demo para cocina', 'sort_order' => 60],
            ['name' => 'Comedia', 'short_name' => 'Comedia', 'description' => 'Categoría demo para comedia', 'sort_order' => 70],
            ['name' => 'Cumbia', 'short_name' => 'Cumbia', 'description' => 'Categoría demo para cumbia', 'sort_order' => 80],
            ['name' => 'Deportes', 'short_name' => 'Deportes', 'description' => 'Categoría demo para deportes', 'sort_order' => 90],
            ['name' => 'Educación', 'short_name' => 'Educación', 'description' => 'Categoría demo para educación', 'sort_order' => 100],
            ['name' => 'Estilo de vida', 'short_name' => 'Estilo de vida', 'description' => 'Categoría demo para estilo de vida', 'sort_order' => 110],
            ['name' => 'Fitness', 'short_name' => 'Fitness', 'description' => 'Categoría demo para fitness', 'sort_order' => 120],
            ['name' => 'Folklore', 'short_name' => 'Folklore', 'description' => 'Categoría demo para folklore', 'sort_order' => 130],
            ['name' => 'Funk', 'short_name' => 'Funk', 'description' => 'Categoría demo para funk', 'sort_order' => 140],
            ['name' => 'Gaming', 'short_name' => 'Gaming', 'description' => 'Categoría demo para gaming', 'sort_order' => 150],
            ['name' => 'General', 'short_name' => 'General', 'description' => 'Categoría demo para general', 'sort_order' => 160],
            ['name' => 'Indie y proyectos propios', 'short_name' => 'Indie y proyectos propios', 'description' => 'Categoría demo para indie y proyectos propios', 'sort_order' => 170],
            ['name' => 'Moda y belleza', 'short_name' => 'Moda y belleza', 'description' => 'Categoría demo para moda y belleza', 'sort_order' => 180],
            ['name' => 'Música', 'short_name' => 'Música', 'description' => 'Categoría demo para música', 'sort_order' => 190],
            ['name' => 'Negocios y finanzas', 'short_name' => 'Negocios y finanzas', 'description' => 'Categoría demo para negocios y finanzas', 'sort_order' => 200],
            ['name' => 'Pop', 'short_name' => 'Pop', 'description' => 'Categoría demo para pop', 'sort_order' => 210],
            ['name' => 'Reggae', 'short_name' => 'Reggae', 'description' => 'Categoría demo para reggae', 'sort_order' => 220],
            ['name' => 'Rock', 'short_name' => 'Rock', 'description' => 'Categoría demo para rock', 'sort_order' => 230],
            ['name' => 'SaaS y software', 'short_name' => 'SaaS y software', 'description' => 'Categoría demo para saas y software', 'sort_order' => 240],
            ['name' => 'Tecnología', 'short_name' => 'Tecnología', 'description' => 'Categoría demo para tecnología', 'sort_order' => 250],
            ['name' => 'Tienda y ecommerce', 'short_name' => 'Tienda y ecommerce', 'description' => 'Categoría demo para tienda y ecommerce', 'sort_order' => 260],
            ['name' => 'Viajes y turismo', 'short_name' => 'Viajes y turismo', 'description' => 'Categoría demo para viajes y turismo', 'sort_order' => 270],
        ];

        foreach ($categories as $categoryData) {
            $name = $categoryData['name'];
            $slug = Str::slug($name);
            $category = ProfileCategory::query()->where('slug', $slug)->first();

            if (! $category) {
                $category = ProfileCategory::create([
                    ...$categoryData,
                    'slug' => $slug,
                    'is_active' => true,
                    'show_in_navigation' => true,
                    'is_indexable' => true,
                ]);
            } else {
                $category->update([
                    ...$categoryData,
                    'slug' => $slug,
                    'is_active' => true,
                    'show_in_navigation' => true,
                    'is_indexable' => true,
                ]);
            }

            ProfileCategory::query()
                ->where('name', $name)
                ->whereKeyNot($category->id)
                ->delete();
        }

        ProfileCategory::query()
            ->where('name', 'Vlogs y lifestyle')
            ->update([
                'description' => 'Categoría combinada obsoleta',
                'is_active' => false,
                'show_in_navigation' => false,
                'is_indexable' => false,
            ]);
    }
}
