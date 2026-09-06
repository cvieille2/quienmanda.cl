<?php

namespace App\Services;

use App\Enums\ProfileLinkType;
use App\Enums\ProfileSourceType;

class ProfileSourceDetectionService
{
    public function __construct(private ProfileUrlNormalizer $normalizer) {}

    public function detect(string $url): array
    {
        $normalized = $this->normalizer->normalize($url);
        $host = (string) parse_url($normalized, PHP_URL_HOST);
        $path = trim((string) parse_url($normalized, PHP_URL_PATH), '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (str_ends_with($host, 'instagram.com')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::Instagram, ProfileLinkType::Social, 'Instagram'];
        } elseif (str_ends_with($host, 'facebook.com')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::Facebook, ProfileLinkType::Social, 'Facebook'];
        } elseif (str_ends_with($host, 'x.com') || str_ends_with($host, 'twitter.com')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::X, ProfileLinkType::Social, 'X'];
        } elseif (str_ends_with($host, 'linkedin.com')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::LinkedIn, ProfileLinkType::Social, 'LinkedIn'];
        } elseif (str_ends_with($host, 'youtube.com') || str_ends_with($host, 'youtu.be')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::YouTube, ProfileLinkType::Social, 'YouTube'];
        } elseif (str_ends_with($host, 'tiktok.com')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::TikTok, ProfileLinkType::Social, 'TikTok'];
        } elseif (str_ends_with($host, 'spotify.com')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::Spotify, ProfileLinkType::Social, 'Spotify'];
        } elseif (str_ends_with($host, 'mercadopublico.cl')) {
            [$sourceType, $linkType, $label] = [ProfileSourceType::MercadoPublico, ProfileLinkType::Reference, 'Mercado Público'];
        } else {
            [$sourceType, $linkType, $label] = [ProfileSourceType::Website, ProfileLinkType::Website, ucfirst($host)];
        }

        $handle = $segments[0] ?? null;
        if ($sourceType === ProfileSourceType::YouTube && $handle === 'channel') {
            $handle = $segments[1] ?? null;
        }

        return [
            'original_url' => $url,
            'normalized_url' => $normalized,
            'host' => $host,
            'path' => $path,
            'handle' => $handle !== '' ? $handle : null,
            'source_type' => $sourceType,
            'link_type' => $linkType,
            'label' => $label,
            'detected_title' => $this->buildTitle($label, $handle),
        ];
    }

    private function buildTitle(string $label, ?string $handle): string
    {
        if (! $handle) {
            return $label;
        }

        return $label.' · '.$handle;
    }
}
