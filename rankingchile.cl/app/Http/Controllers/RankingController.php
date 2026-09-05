<?php

namespace App\Http\Controllers;

use App\Services\RankingPeriodService;
use App\Services\RankingService;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller
{
    public function current(RankingPeriodService $periods, RankingService $ranking): JsonResponse
    {
        $period = $periods->closeExpiredAndSettle();
        $compact = $ranking->periodCompact();

        return response()->json([
            'period' => [
                'external_key'    => $period?->public_id,
                'code'            => $period?->code,
                'starts_at_utc'   => $period?->starts_at,
                'ends_at_utc'     => $period?->ends_at,
                'ends_in_seconds' => $period ? max(0, $period->ends_at->diffInSeconds(now())) : 0,
            ],
            'ranking' => $compact,
        ])->header('Cache-Control', 'no-store');
    }
}
