<?php

namespace App\Services;

use InvalidArgumentException;

class ProfileUrlNormalizer
{
    private const TRACKING_PARAMS = [
        'fbclid',
        'gclid',
        'igshid',
        'mibextid',
        'mc_cid',
        'mc_eid',
        'ref',
        'si',
        'spm',
        'utm_campaign',
        'utm_content',
        'utm_medium',
        'utm_source',
        'utm_term',
    ];

    public function normalize(string $url): string
    {
        $value = trim($url);
        if ($value === '') {
            throw new InvalidArgumentException('La URL no puede estar vacía.');
        }

        if (! str_contains($value, '://')) {
            $value = 'https://'.$value;
        }

        $parts = parse_url($value);
        if (! is_array($parts) || empty($parts['host'])) {
            throw new InvalidArgumentException('La URL entregada no es válida.');
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Solo se permiten URLs http(s).');
        }

        $host = strtolower($parts['host']);
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $this->assertSafePublicHost($host);

        $path = $this->normalizePath($parts['path'] ?? '/');
        $query = $this->normalizeQuery($parts['query'] ?? '');

        $normalized = $scheme.'://'.$host.$path;

        if ($query !== '') {
            $normalized .= '?'.$query;
        }

        return $normalized;
    }

    public function host(string $url): string
    {
        $value = $this->normalize($url);
        return (string) parse_url($value, PHP_URL_HOST);
    }

    /** Bloquea hosts locales, privados, de enlace local y metadata cloud (SSRF). */
    private function assertSafePublicHost(string $host): void
    {
        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            '0.0.0.0',
            '169.254.169.254',
        ];

        if (in_array($host, $blockedHosts, true)) {
            throw new InvalidArgumentException('El host entregado no es una URL pública válida.');
        }

        if (str_ends_with($host, '.localhost')) {
            throw new InvalidArgumentException('El host entregado no es una URL pública válida.');
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            throw new InvalidArgumentException('El host entregado no es una URL pública válida.');
        }

        $isIp = filter_var($host, FILTER_VALIDATE_IP);
        if ($isIp !== false && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new InvalidArgumentException('El host entregado no es una URL pública válida.');
        }
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?? $path;
        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }

    private function normalizeQuery(string $query): string
    {
        if ($query === '') {
            return '';
        }

        parse_str($query, $params);
        $params = array_filter($params, function ($value, string $key) {
            return ! in_array(strtolower($key), self::TRACKING_PARAMS, true) && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        if ($params === []) {
            return '';
        }

        ksort($params);

        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}
