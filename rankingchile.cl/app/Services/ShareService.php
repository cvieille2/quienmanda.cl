<?php

namespace App\Services;

use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\ShareEvent;
use App\Models\SupportTransaction;

class ShareService
{
    public function generateFor(SupportTransaction $tx): ?ShareEvent
    {
        if ($tx->type !== SupportTransactionType::Real || $tx->status !== SupportTransactionStatus::Approved) {
            return null;
        }

        return ShareEvent::firstOrCreate(
            ['support_transaction_id' => $tx->id],
            [
                'profile_id'  => $tx->profile_id,
                'source'      => 'share_trophy',
                'period_code' => $tx->rankingPeriod?->code,
            ]
        );
    }
}
