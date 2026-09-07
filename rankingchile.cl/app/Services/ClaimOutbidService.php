<?php

namespace App\Services;

use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\Profile;
use App\Models\RankingPeriod;
use App\Models\SupportTransaction;
use Illuminate\Support\Facades\DB;

/**
 * D-CLAIM: Modelo de cobro claim/outbid.
 */
class ClaimOutbidService
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
        private RankingProjectionService $projection,
    ) {}

    public function claimContext(Profile $profile): array
    {
        $period = $this->periods->activePeriod();
        if (! $period) {
            return $this->emptyContext($profile);
        }

        $ranking = $this->ranking->rankingForPeriod($period->id, true);
        $entry = collect($ranking)->firstWhere('profile_id', $profile->id);

        if (! $entry) {
            return $this->emptyContext($profile);
        }

        $position = $entry['position'];
        $total = $entry['total_real_clp'];
        $minIncrement = RankingService::MIN_INCREMENT_CLP;

        $above = $position > 1 ? ($ranking[$position - 2] ?? null) : null;
        $aboveTotal = $above ? $above['total_real_clp'] : 0;
        $below = $position < count($ranking) ? ($ranking[$position] ?? null) : null;
        $belowTotal = $below ? $below['total_real_clp'] : 0;

        $claimAmount = $above
            ? ($aboveTotal - $total) + $minIncrement
            : $minIncrement;

        $outbidThreshold = $below
            ? ($total - $belowTotal) + $minIncrement
            : null;

        $newPositionIfOutbid = $this->calculateNewPositionIfOutbid($ranking, $position, $belowTotal, $minIncrement);
        $toTop = $entry['to_number_one_clp'];
        $cfg = $period->configuration ?? [];
        $minLimit = (int) ($cfg['minimum_support_clp'] ?? PaymentLimitsService::MIN_CLP);
        $maxLimit = (int) ($cfg['maximum_support_clp'] ?? PaymentLimitsService::MAX_CLP);

        return [
            'profile_id' => $profile->id,
            'profile_name' => $profile->display_name,
            'current_position' => $position,
            'current_amount' => $total,
            'supporter_count' => $entry['supporter_count'],
            'claim_amount' => max($minLimit, min($claimAmount, $maxLimit)),
            'claim_amount_raw' => $claimAmount,
            'claim_description' => $this->buildClaimDescription($position, $above, $claimAmount, $minLimit, $maxLimit),
            'outbid_threshold' => $outbidThreshold,
            'outbid_description' => $this->buildOutbidDescription($profile->display_name, $outbidThreshold, $newPositionIfOutbid),
            'to_top_amount' => $toTop,
            'to_top_description' => $this->buildToTopDescription($profile->display_name, $toTop, $minLimit, $maxLimit),
            'min_limit' => $minLimit,
            'max_limit' => $maxLimit,
            'above_profile' => $above ? [
                'name' => $above['display_name'],
                'amount' => $above['total_real_clp'],
                'rank' => $above['position'],
            ] : null,
            'below_profile' => $below ? [
                'name' => $below['display_name'],
                'amount' => $below['total_real_clp'],
                'rank' => $below['position'],
            ] : null,
        ];
    }

    public function generateReceipt(SupportTransaction $tx): array
    {
        $tx->load('profile', 'rankingPeriod');

        // La posición que se mostró antes del checkout puede cambiar mientras
        // el usuario paga. El comprobante debe reflejar el ranking confirmado
        // después de aplicar este movimiento, no la posición objetivo original.
        $finalPosition = null;
        if ($tx->ranking_period_id && $tx->profile_id) {
            $finalEntry = collect($this->ranking->rankingForPeriod($tx->ranking_period_id, false))
                ->firstWhere('profile_id', $tx->profile_id);
            $finalPosition = $finalEntry['position'] ?? null;
        }

        $periodCode = $tx->rankingPeriod?->code ?? 'N/A';
        $periodDates = $tx->rankingPeriod
            ? $tx->rankingPeriod->starts_at->timezone('America/Santiago')->format('d/m/Y')
              . ' – '
              . $tx->rankingPeriod->ends_at->timezone('America/Santiago')->format('d/m/Y')
            : '';

        $approvedAt = $tx->ranking_qualified_at
            ? $tx->ranking_qualified_at->timezone('America/Santiago')->format('d/m/Y H:mm')
            : ($tx->provider_approved_at
                ? $tx->provider_approved_at->timezone('America/Santiago')->format('d/m/Y H:mm')
                : '');

        return [
            'receipt_id' => 'RCH-' . strtoupper(substr($tx->external_reference ?? $tx->id, 0, 12)),
            'transaction_id' => $tx->id,
            'amount_clp' => $tx->amount_clp,
            'amount_formatted' => money_clp($tx->amount_clp),
            'profile_name' => $tx->profile?->display_name ?? 'N/A',
            'profile_slug' => $tx->profile?->slug ?? '',
            'final_position' => $finalPosition,
            'supporter_name' => $tx->supporter_name ?? 'Impulso anónimo',
            'is_anonymous' => $tx->is_anonymous,
            'period_code' => $periodCode,
            'period_dates' => $periodDates,
            'approved_at' => $approvedAt,
            'status' => $tx->status->value,
            'gateway' => $tx->payment_gateway->value,
            'reference' => $tx->external_reference ?? '',
            'description' => $this->buildReceiptDescription($tx),
        ];
    }

    public function projectPosition(Profile $profile, int $amountClp): array
    {
        return $this->projection->project($profile, $amountClp, $this->periods->activePeriod());
    }

    private function calculateNewPositionIfOutbid(array $ranking, int $currentPos, int $belowTotal, int $minIncrement): int
    {
        if ($currentPos >= count($ranking)) {
            return $currentPos;
        }

        $newAmount = $belowTotal + $minIncrement;
        $newRank = $currentPos;
        foreach ($ranking as $r) {
            if ($r['position'] === $currentPos) {
                continue;
            }
            if ($r['total_real_clp'] >= $newAmount && $r['position'] > $currentPos) {
                $newRank = max($newRank, $r['position']);
            }
        }

        return min($newRank + 1, count($ranking));
    }

    private function buildClaimDescription(int $position, ?array $above, int $claimAmount, int $min, int $max): string
    {
        if ($position <= 1) {
            return "Ya estás en el puesto #1. Tu impulso defiende la corona. Mínimo {$min} CLP.";
        }

        $aboveName = $above['display_name'] ?? 'el puesto anterior';
        $aboveAmount = $above['total_real_clp'] ?? 0;

        if ($claimAmount > $max) {
            return "Para superar a {$aboveName} (#{$above['position']}) necesitas más de {$max} CLP (el máximo por impulso).";
        }

        return "Mínimo {$claimAmount} CLP para superar a {$aboveName} (#{$above['position']}, con {$aboveAmount} CLP).";
    }

    private function buildOutbidDescription(string $profileName, ?int $outbidThreshold, int $newPosition): string
    {
        if ($outbidThreshold === null) {
            return "{$profileName} está en último lugar. Nadie puede superarla desde abajo en esta categoría.";
        }

        return "Si alguien impulsa con {$outbidThreshold} CLP a un perfil que está debajo, {$profileName} baja al puesto #{$newPosition}.";
    }

    private function buildToTopDescription(string $profileName, int $toTop, int $min, int $max): string
    {
        if ($toTop <= $max) {
            return "{$toTop} CLP para llevar a {$profileName} al puesto #1.";
        }

        $multiple = ceil($toTop / $max);
        return "Necesitas {$toTop} CLP para el #1 (aprox. {$multiple} impulsos de {$max} CLP c/u).";
    }

    private function buildReceiptDescription(SupportTransaction $tx): string
    {
        $profileName = $tx->profile?->display_name ?? 'perfil';
        $periodCode = $tx->rankingPeriod?->code ?? '';

        if ($tx->status === SupportTransactionStatus::Approved) {
            return "Impulso de {$tx->amount_clp} CLP a {$profileName} confirmado. Periodo {$periodCode}.";
        }

        return "Pago de {$tx->amount_clp} CLP a {$profileName} registrado. Estado: {$tx->status->value}.";
    }

    private function emptyContext(Profile $profile): array
    {
        return [
            'profile_id' => $profile->id,
            'profile_name' => $profile->display_name,
            'current_position' => null,
            'current_amount' => 0,
            'supporter_count' => 0,
            'claim_amount' => 0,
            'claim_amount_raw' => 0,
            'claim_description' => 'No hay período activo.',
            'outbid_threshold' => null,
            'outbid_description' => 'No hay período activo.',
            'to_top_amount' => 0,
            'to_top_description' => 'No hay período activo.',
            'min_limit' => PaymentLimitsService::MIN_CLP,
            'max_limit' => PaymentLimitsService::MAX_CLP,
            'above_profile' => null,
            'below_profile' => null,
        ];
    }
}
