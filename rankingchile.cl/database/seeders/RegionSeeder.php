<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'XV de Arica y Parinacota', 'sort_order' => 1],
            ['name' => 'I de Tarapacá', 'sort_order' => 2],
            ['name' => 'II de Antofagasta', 'sort_order' => 3],
            ['name' => 'III de Atacama', 'sort_order' => 4],
            ['name' => 'IV de Coquimbo', 'sort_order' => 5],
            ['name' => 'V de Valparaíso', 'sort_order' => 6],
            ['name' => "VI del Libertador General Bernardo O'Higgins", 'sort_order' => 7],
            ['name' => 'VII del Maule', 'sort_order' => 8],
            ['name' => 'VIII del Biobío', 'sort_order' => 9],
            ['name' => 'IX de la Araucanía', 'sort_order' => 10],
            ['name' => 'XIV de los Ríos', 'sort_order' => 11],
            ['name' => 'X de los Lagos', 'sort_order' => 12],
            ['name' => "XI Aysén del General Carlos Ibáñez del Campo", 'sort_order' => 13],
            ['name' => "XII de Magallanes y Antártica Chilena", 'sort_order' => 14],
            ['name' => 'Metropolitana de Santiago', 'sort_order' => 15],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(
                ['slug' => Str::slug($region['name'])],
                [
                    'name' => $region['name'],
                    'sort_order' => $region['sort_order'],
                ]
            );
        }
    }
}
