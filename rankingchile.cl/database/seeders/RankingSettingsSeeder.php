<?php

namespace Database\Seeders;

use App\Models\RankingSetting;
use Illuminate\Database\Seeder;

class RankingSettingsSeeder extends Seeder
{
    /**
     * Fila única de defaults (D-048).
     * Los cambios aquí solo afectan periodos FUTUROS; la configuración del periodo activo está congelada.
     */
    public function run(): void
    {
        RankingSetting::updateOrCreate(
            ['scope' => 'default'],
            [
                'period_type'                 => 'weekly',
                'settlement_delay_minutes'    => 5,
                'minimum_support_clp'         => 1000,
                'maximum_support_clp'         => 500000,
                'show_real_amounts'           => true,
                'show_supporter_count'        => true,
                'max_public_positions'        => 10,
                'category_scope'              => 'all',
                'category'                    => null,
                'sharing_enabled'             => true,
                'community_profiles_enabled'  => false,
                'promotional_credits_enabled' => false,
            ]
        );
    }
}
