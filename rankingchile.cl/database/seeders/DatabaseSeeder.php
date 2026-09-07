<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            FeatureFlagsSeeder::class,
            RankingSettingsSeeder::class,
            InitialPeriodSeeder::class,            RegionSeeder::class,
            TestProfilesSeeder::class,
        ]);
    }
}
