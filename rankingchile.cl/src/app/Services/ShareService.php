<?php

namespace App\Services;

use App\Models\ShareEvent;
use App\Models\SupportTransaction;

class ShareService
{
    /** Genera un share_event por transacción aprobada real (idempotente por tx). */
    public function generateFor(SupportTransaction $tx): ?ShareEvent
    {
        if ($tx->type !== SupportTransaction::TYPE_REAL || $tx->status !== SupportTransaction::STATUS_APPROVED) {
            return null;
        }

        return ShareEvent::firstOrCreate(
            ['support_transaction_id' => $tx->id],
            [
                'profile_id'   => $tx->profile_id,
                'source'       => 'share_trophy',
                'period_code'  => $tx->rankingPeriod?->code, // renombrado de cycle_code (rebaseline)
            ]
        );
    }
}