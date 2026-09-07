<?php

namespace App\Services;

use App\Models\RankingMovement;
use App\Models\SupportTransaction;
use Illuminate\Support\Facades\DB;

class RankingMovementService
{
    public function __construct(private RankingService $ranking) {}

    public function recordForApprovedTransaction(SupportTransaction $transaction, array $before): RankingMovement
    {
        $after = $this->ranking->rankingForPeriod((int) $transaction->ranking_period_id, false);
        $beforeRow = collect($before)->firstWhere('profile_id', $transaction->profile_id);
        $afterRow = collect($after)->firstWhere('profile_id', $transaction->profile_id);
        $movement = RankingMovement::query()->firstOrCreate(
            ['support_transaction_id' => $transaction->id],
            [
                'profile_id' => $transaction->profile_id,
                'amount_clp' => $transaction->amount_clp,
                'position_before' => $beforeRow['position'] ?? null,
                'position_after' => $afterRow['position'] ?? 0,
                'total_before_clp' => $beforeRow['total_real_clp'] ?? 0,
                'total_after_clp' => $afterRow['total_real_clp'] ?? 0,
                'leader_before_profile_id' => $before[0]['profile_id'] ?? null,
                'leader_after_profile_id' => $after[0]['profile_id'] ?? null,
                'ranking_version' => $transaction->id,
                'approved_at' => $transaction->ranking_qualified_at ?? $transaction->provider_approved_at ?? now(),
            ],
        );

        return $movement;
    }
}
