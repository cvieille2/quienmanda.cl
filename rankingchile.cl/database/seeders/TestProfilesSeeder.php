<?php

namespace Database\Seeders;

use App\Enums\ProfileLinkType;
use App\Enums\ProfileStatus;
use App\Enums\ProfileType;
use App\Enums\VerificationStatus;
use App\Models\Profile;
use App\Models\ProfileCategory;
use App\Models\ProfileLink;
use App\Services\ProfileUrlNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestProfilesSeeder extends Seeder
{
    /**
     * Perfiles de prueba para desarrollo (lista seed de perfiles para la home).
     */
    public function run(): void
    {
        $normalizer = app(ProfileUrlNormalizer::class);

        $profiles = [
            [
                'display_name' => 'Los Kjarkas',
                'category' => 'folklore',
                'project_description' => 'Folklore andino con presencia en Chile y LATAM.',
                'website_url' => 'https://loskjarkas.example.com',
                'instagram_url' => 'https://instagram.com/loskjarkas',
                'tiktok_url' => 'https://tiktok.com/@loskjarkas',
                'x_url' => 'https://x.com/loskjarkas',
                'use_profile_as_destination' => false,
            ],
            [
                'display_name' => 'Javiera Mena',
                'category' => 'pop',
                'project_description' => 'Pop electrónico y cultura visual de alto alcance.',
                'website_url' => 'https://javieramena.example.com',
                'instagram_url' => 'https://instagram.com/javieramena',
                'tiktok_url' => 'https://tiktok.com/@javieramena',
                'x_url' => 'https://x.com/javieramena',
                'use_profile_as_destination' => true,
            ],
            [
                'display_name' => 'Alexis Vegara',
                'category' => 'cumbia',
                'project_description' => 'Show en vivo, baile y comunidad en crecimiento.',
                'website_url' => 'https://alexisvegara.example.com',
                'instagram_url' => 'https://instagram.com/alexisvegara',
                'tiktok_url' => 'https://tiktok.com/@alexisvegara',
                'x_url' => 'https://x.com/alexisvegara',
                'use_profile_as_destination' => false,
            ],
            [
                'display_name' => 'Gepe',
                'category' => 'indie',
                'project_description' => 'Indie chileno con base de fans transversal.',
                'website_url' => 'https://gepe.example.com',
                'instagram_url' => 'https://instagram.com/gepeoficial',
                'tiktok_url' => 'https://tiktok.com/@gepeoficial',
                'x_url' => 'https://x.com/gepeoficial',
                'use_profile_as_destination' => true,
            ],
            [
                'display_name' => 'Francisca Valenzuela',
                'category' => 'pop',
                'project_description' => 'Artista pop con foco en comunidad y visibilidad.',
                'website_url' => 'https://franciscavalenzuela.example.com',
                'instagram_url' => 'https://instagram.com/franciscavalenzuela',
                'tiktok_url' => 'https://tiktok.com/@franciscavalenzuela',
                'x_url' => 'https://x.com/franvalenzuela',
                'use_profile_as_destination' => false,
            ],
            [
                'display_name' => 'Víctor Jara',
                'category' => 'folklore',
                'project_description' => 'Legado cultural y patrimonial de enorme arraigo.',
                'website_url' => 'https://victorjara.example.com',
                'instagram_url' => 'https://instagram.com/victorjara',
                'tiktok_url' => 'https://tiktok.com/@victorjara',
                'x_url' => 'https://x.com/victorjara',
                'use_profile_as_destination' => true,
            ],
            [
                'display_name' => 'Los Bunkers',
                'category' => 'rock',
                'project_description' => 'Rock chileno con comunidad sólida y alto reconocimiento.',
                'website_url' => 'https://losbunkers.example.com',
                'instagram_url' => 'https://instagram.com/losbunkers',
                'tiktok_url' => 'https://tiktok.com/@losbunkers',
                'x_url' => 'https://x.com/losbunkers',
                'use_profile_as_destination' => false,
            ],
            [
                'display_name' => 'Chancho en Piedra',
                'category' => 'funk',
                'project_description' => 'Funk rock con fandom histórico y buen engagement.',
                'website_url' => 'https://chanchoenpiedra.example.com',
                'instagram_url' => 'https://instagram.com/chanchoenpiedra',
                'tiktok_url' => 'https://tiktok.com/@chanchoenpiedra',
                'x_url' => 'https://x.com/chanchoenpiedra',
                'use_profile_as_destination' => false,
            ],
            [
                'display_name' => 'Django',
                'category' => 'reggae',
                'project_description' => 'Reggae chileno con estética de playa y movimiento.',
                'website_url' => 'https://django.example.com',
                'instagram_url' => 'https://instagram.com/djangoband',
                'tiktok_url' => 'https://tiktok.com/@djangoband',
                'x_url' => 'https://x.com/djangoband',
                'use_profile_as_destination' => true,
            ],
            [
                'display_name' => 'La Ley',
                'category' => 'rock',
                'project_description' => 'Rock clásico con alcance masivo y fuerte identidad.',
                'website_url' => 'https://laley.example.com',
                'instagram_url' => 'https://instagram.com/laleyoficial',
                'tiktok_url' => 'https://tiktok.com/@laleyoficial',
                'x_url' => 'https://x.com/laleyoficial',
                'use_profile_as_destination' => false,
            ],
            [
                'display_name' => 'Los Tres',
                'category' => 'rock',
                'project_description' => 'Ícono nacional con público fiel y amplio reconocimiento.',
                'website_url' => 'https://lostres.example.com',
                'instagram_url' => 'https://instagram.com/lostres',
                'tiktok_url' => 'https://tiktok.com/@lostres',
                'x_url' => 'https://x.com/lostres',
                'use_profile_as_destination' => true,
            ],
            [
                'display_name' => 'Cecilia',
                'category' => 'balada',
                'project_description' => 'Balada y patrimonio musical con impacto transversal.',
                'website_url' => 'https://cecilia.example.com',
                'instagram_url' => 'https://instagram.com/ceciliaoficial',
                'tiktok_url' => 'https://tiktok.com/@ceciliaoficial',
                'x_url' => 'https://x.com/ceciliaoficial',
                'use_profile_as_destination' => false,
            ],
        ];

        foreach ($profiles as $profile) {
            $slug = Str::slug($profile['display_name']);
            $category = ProfileCategory::firstOrCreate(
                ['slug' => $profile['category']],
                [
                    'name' => ucfirst($profile['category']),
                    'short_name' => ucfirst($profile['category']),
                    'description' => 'Categoría demo para la home',
                    'hero_title' => null,
                    'hero_description' => null,
                    'seo_title' => null,
                    'seo_description' => null,
                    'seo_content' => null,
                    'icon' => null,
                    'is_active' => true,
                    'show_in_navigation' => true,
                    'is_indexable' => true,
                    'sort_order' => 0,
                ]
            );

            $model = Profile::firstOrCreate(
                ['slug' => $slug],
                [
                    'public_id' => (string) Str::ulid(),
                    'display_name' => $profile['display_name'],
                    'project_description' => $profile['project_description'],
                    'instagram_url' => $profile['instagram_url'],
                    'tiktok_url' => $profile['tiktok_url'],
                    'x_url' => $profile['x_url'],
                    'website_url' => $profile['website_url'],
                    'category' => $profile['category'],
                    'profile_category_id' => $category->id,
                    'type' => ProfileType::PublicFigure->value,
                    'status' => ProfileStatus::Active->value,
                    'verification_status' => VerificationStatus::Verified->value,
                    'is_community_created' => false,
                    'profile_image_url' => 'https://images.unsplash.com/photo-1511367461989-f85a21fda167?auto=format&fit=crop&w=300&q=80',
                    'featured_media_url' => null,
                ]
            );

            $links = [
                ['label' => 'Sitio web', 'url' => $profile['website_url'], 'type' => ProfileLinkType::Website],
                ['label' => 'Instagram', 'url' => $profile['instagram_url'], 'type' => ProfileLinkType::Social],
                ['label' => 'TikTok', 'url' => $profile['tiktok_url'], 'type' => ProfileLinkType::Social],
                ['label' => 'X', 'url' => $profile['x_url'], 'type' => ProfileLinkType::Social],
            ];

            foreach ($links as $i => $link) {
                $normalized = $normalizer->normalize($link['url']);
                ProfileLink::updateOrCreate(
                    [
                        'profile_id' => $model->id,
                        'original_url' => $link['url'],
                    ],
                    [
                        'profile_submission_id' => null,
                        'link_type' => $link['type'],
                        'label' => $link['label'],
                        'normalized_url' => $normalized,
                        'host' => (string) parse_url($normalized, PHP_URL_HOST),
                        'is_primary' => $i === 0,
                        'sort_order' => $i,
                    ]
                );
            }
        }
    }
}