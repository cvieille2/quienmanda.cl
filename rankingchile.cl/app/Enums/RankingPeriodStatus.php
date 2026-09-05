<?php

namespace App\Enums;

enum RankingPeriodStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case ClosedPendingSettlement = 'closed_pending_settlement';
    case Closed = 'closed';
    case Snapshotted = 'snapshotted';
    case Cancelled = 'cancelled';

    /** Transiciones válidas de la máquina de estados del periodo (D-042/D-043). */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Scheduled, self::Cancelled], true),
            self::Scheduled => in_array($target, [self::Active, self::Cancelled], true),
            self::Active => $target === self::ClosedPendingSettlement,
            self::ClosedPendingSettlement => $target === self::Closed,
            self::Closed => $target === self::Snapshotted,
            self::Snapshotted, self::Cancelled => false, // estados terminales
        };
    }
}