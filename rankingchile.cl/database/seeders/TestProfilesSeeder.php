<?php

namespace Database\Seeders;

use App\Enums\ProfileStatus;
use App\Enums\ProfileType;
use App\Enums\VerificationStatus;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestProfilesSeeder extends Seeder
{
    /**
     * Perfiles de prueba para desarrollo (B6: lista seed de perfiles 10-15).
     */
    public function run(): void
    {
        $profiles = [
            ['display_name' => 'Los Kjarkas', 'category' => 'folklore'],
            ['display_name' => 'Javiera Mena', 'category' => 'pop'],
            ['display_name' => 'Alexis Vegara', 'category' => 'cumbia'],
            ['display_name' => 'Gepe', 'category' => 'indie'],
            ['display_name' => 'Francisca Valenzuela', 'category' => 'pop'],
            ['display_name' => 'Víctor Jara', 'category' => 'folklore'],
            ['display_name' => 'Los Bunkers', 'category' => 'rock'],
            ['display_name' => 'Chancho en Piedra', 'category' => 'funk'],
            ['display_name' => 'Django', 'category' => 'reggae'],
            ['display_name' => 'La Ley', 'category' => 'rock'],
            ['display_name' => 'Los Tres', 'category' => 'rock'],
            ['display_name' => 'Cecilia', 'category' => 'balada'],
        ];

        foreach ($profiles as $profile) {
            $slug = Str::slug($profile['display_name']);

            Profile::firstOrCreate(
                ['slug' => $slug],
                [
                    'public_id' => (string) Str::ulid(),
                    'display_name' => $profile['display_name'],
                    'category' => $profile['category'],
                    'type' => ProfileType::PublicFigure->value,
                    'status' => ProfileStatus::Active->value,
                    'verification_status' => VerificationStatus::Verified->value,
                    'is_community_created' => false,
                ]
            );
        }
    }
}
