<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (quienmanda.cl)
|--------------------------------------------------------------------------
| Webhooks server-to-server y endpoints JSON consumidos por el frontend/poll.
| Todas las rutas devuelven payload mínimo (nunca HTML) y no cachean ranking.
*/

use App\Services\RankingPeriodService;
use App\Services\RankingService;

// POST /api/webhooks/mercadopago -> webhook server-to-server (source of truth)
// petición externa MP: se excluye CSRF (se valida contra la pasarela en approve()).
Route::post('/webhooks/mercadopago', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// POST /api/pagos -> creación del checkout (sección 4)
Route::post('/pagos', [PaymentController::class, 'start'])
    ->middleware(['throttle:10,60']);

// GET /api/pagos/return -> retorno del frontend (NUNCA confirma)
Route::get('/pagos/return', [PaymentController::class, 'return']);

// GET /api/ranking/current -> polling adaptativo (payload mínimo, no-store)
// Defensivo: cierra periodos vencidos, ejecuta settlement window y garantiza periodo activo.
Route::get('/ranking/current', function () {
    $periods = app(RankingPeriodService::class);
    $period  = $periods->closeExpiredAndSettle(); // defensivo + settlement + garantiza activo

    $ranking = app(RankingService::class)->periodCompact();

    return response()->json([
        'period' => [
            'external_key'    => $period?->public_id, // ULID público del periodo (D-044)
            'code'            => $period?->code,
            'starts_at_utc'   => $period?->starts_at,
            'ends_at_utc'     => $period?->ends_at,
            'ends_in_seconds' => $period ? max(0, $period->ends_at->diffInSeconds(now())) : 0,
        ],
        'ranking' => $ranking, // [{ rank, slug, total_real_clp, position }]
    ])->header('Cache-Control', 'no-store');
})->middleware(['throttle:180,1', \App\Http\Middleware\NoCacheLiveRanking::class]);