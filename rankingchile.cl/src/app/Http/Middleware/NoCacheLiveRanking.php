<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware global para endpoints de ranking en vivo (D-27).
 * Garantiza que el ranking público jamás sea cacheado a nivel HTTP/proxy (el cache es solo de app, TTL 5s).
 */
class NoCacheLiveRanking
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
