<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagsSeeder extends Seeder
{
    /**
     * Flags oficiales del MVP (gobernanza v1.0.0).
     * payments_enabled = true (kill switch principal; si false -> no checkout ni pagos nuevos)
     * community_profile_submission_enabled = false (MVP: SOLO admin crea perfiles)
     * public_ranking_enabled = true
     * promotional_credits_enabled = false (función admin; OFF por defecto en MVP)
     * sharing_enabled = true
     */
    public function run(): void
    {
        $flags = [
            ['key' => 'payments_enabled', 'value' => true, 'description' => 'Financial kill switch. Si false, no se crean nuevos checkouts ni se procesan pagos.'],
            ['key' => 'community_profile_submission_enabled', 'value' => false, 'description' => 'Permite a la comunidad enviar perfiles. OFF en MVP, solo admin crea perfiles.'],
            ['key' => 'public_ranking_enabled', 'value' => true, 'description' => 'Muestra el ranking público.'],
            ['key' => 'promotional_credits_enabled', 'value' => false, 'description' => 'Permite a admin crear créditos promocionales. OFF por defecto.'],
            ['key' => 'sharing_enabled', 'value' => true, 'description' => 'Habilita la función de compartir eventos.'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(
                ['key' => $flag['key']],
                [
                    'value'       => $flag['value'],
                    'description' => $flag['description'],
                    'updated_at'  => now(),
                ]
            );
        }
    }
}
