<?php

namespace Database\Seeders;

use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\SupportTransaction;
use Illuminate\Database\Seeder;

class HomeDemoSeeder extends Seeder
{
    /**
     * Seed demo para levantar la home con ranking, categorías y links.
     * Úsalo en local: php artisan db:seed --class=HomeDemoSeeder
     */
    public function run(): void
    {
        $this->call([
            FeatureFlagsSeeder::class,
            RankingSettingsSeeder::class,
            InitialPeriodSeeder::class,
            ProfileCategoriesSeeder::class,
            RegionSeeder::class,
            TestProfilesSeeder::class,
            DemoTransactionsSeeder::class,
        ]);

        $hasDemoRanking = SupportTransaction::query()
            ->where('type', SupportTransactionType::Real->value)
            ->where('status', SupportTransactionStatus::Approved->value)
            ->exists();

        if (! $hasDemoRanking) {
            $this->call(DemoTransactionsSeeder::class);
        }
    }
}