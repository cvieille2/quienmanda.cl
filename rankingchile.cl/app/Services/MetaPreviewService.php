<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MetaPreviewService
{
    public function __construct(
        private ProfileUrlNormalizer $normalizer,
    ) {}

    public function preview(string $url): array
    {
        try {
            $normalized = $this->normalizer->normalize($url);
        } catch (\Throwable) {
            return $this->empty($url);
        }

        try {
            $response = Http::timeout(5)
                // No seguir redirects: el host de destino debe ser el mismo que validamos.
                // Esto evita que un sitio público redirija el servidor hacia localhost/private IP.
                ->withOptions(['allow_redirects' => false])
                ->accept('text/html,application/xhtml+xml')
                ->withHeaders([
                    'User-Agent' => 'QuienMandaMetaPreview/1.0',
                ])
                ->get($normalized);
        } catch (\Throwable) {
            return $this->empty($normalized);
        }

        if (! $response->ok()) {
            return $this->empty($normalized);
        }

        $body = (string) $response->body();
        if ($body === '') {
            return $this->empty($normalized);
        }

        [$title, $description] = $this->extractMeta($body);
        $title = $this->sanitize($title, 140);
        $description = $this->sanitize($description, 240);

        $hasTitle = $title !== null;
        $hasDescription = $description !== null;

        return [
            'url' => $normalized,
            'title' => $title,
            'description' => $description,
            'has_title' => $hasTitle,
            'has_description' => $hasDescription,
            'has_good_paint' => $hasTitle && $hasDescription,
            'badge_label' => $hasTitle && $hasDescription ? 'Esto tiene buena pinta' : null,
        ];
    }

    private function extractMeta(string $html): array
    {
        $title = null;
        $description = null;

        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $title = html_entity_decode(trim(strip_tags($matches[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/is', $html, $matches)) {
            $description = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/is', $html, $matches)) {
            $description = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (! $title && preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/is', $html, $matches)) {
            $title = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return [$title, $description];
    }

    private function sanitize(?string $value, int $maxLength): ?string
    {
        $value = is_string($value) ? trim(preg_replace('/\s+/', ' ', $value) ?? $value) : null;

        if (! $value) {
            return null;
        }

        if (mb_strlen($value) < 3) {
            return null;
        }

        if (mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength - 1).'…';
        }

        return $value;
    }

    private function empty(string $url): array
    {
        return [
            'url' => $url,
            'title' => null,
            'description' => null,
            'has_title' => false,
            'has_description' => false,
            'has_good_paint' => false,
            'badge_label' => null,
        ];
    }
}
