<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\RankingPeriod;

class RankingProjectionService
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
    ) {}

    public function project(Profile $profile, int $amountClp, ?RankingPeriod $period = null): array
    {
        $period ??= $this->periods->activePeriod();

        if (! $period) {
            return [
                'rank' => null,
                'tied' => false,
                'description' => 'No hay período activo.',
                'current_position' => null,
                'current_total_clp' => 0,
                'projected_total_clp' => $amountClp,
                'projected_position' => null,
                'to_top_amount' => 0,
            ];
        }

        $rows = $this->ranking->rankingForPeriod($period->id, true);
        $entry = collect($rows)->firstWhere('profile_id', $profile->id);

        if (! $entry) {
            return [
                'rank' => null,
                'tied' => false,
                'description' => 'Perfil no rankeable todavía.',
                'current_position' => null,
                'current_total_clp' => 0,
                'projected_total_clp' => $amountClp,
                'projected_position' => null,
                'to_top_amount' => 0,
            ];
        }

        $projectedTotal = $entry['total_real_clp'] + $amountClp;
        $ahead = 0;

        foreach ($rows as $row) {
            if ($row['total_real_clp'] >= $projectedTotal) {
                $ahead++;
            }
        }

        $projectedRank = $ahead + 1;
        $tied = $ahead > 0 && $rows[$ahead - 1]['total_real_clp'] === $projectedTotal;
        $toTop = max(0, (int) $entry['to_number_one_clp'] - $amountClp);

        $description = $projectedRank === 1
            ? "Con {$amountClp} CLP, {$profile->display_name} queda #1."
            : "Con {$amountClp} CLP, {$profile->display_name} sube al puesto #{$projectedRank}.";

        if ($tied) {
            $description .= ' (empate en monto)';
        }

        return [
            'rank' => $projectedRank,
            'tied' => $tied,
            'description' => $description,
            'current_position' => $entry['position'],
            'current_total_clp' => $entry['total_real_clp'],
            'projected_total_clp' => $projectedTotal,
            'projected_position' => $projectedRank,
            'to_top_amount' => $toTop,
            'period_code' => $period->code,
        ];
    }

    /**
     * Calculate required amount to reach target positions (#10, #5, #3, #1)
     * for a new entrant or existing profile with 0 CLP.
     *
     * @return array{position: int, required_amount: int, reachable: bool}[]
     */
    public function projectAvailablePositions(?RankingPeriod $period = null, ?int $profileId = null): array
    {
        $period ??= $this->periods->activePeriod();

        if (! $period) {
            return $this->emptyPositionProjections();
        }

        $rows = $this->ranking->rankingForPeriod($period->id, false);
        $currentTotal = 0;

        if ($profileId) {
            $entry = collect($rows)->firstWhere('profile_id', $profileId);
            $currentTotal = $entry['total_real_clp'] ?? 0;
        }

        $minIncrement = RankingService::MIN_INCREMENT_CLP;
        $targets = [10, 5, 3, 1];
        $results = [];

        foreach ($targets as $targetPosition) {
            $count = count($rows);

            if ($targetPosition > $count) {
                // Position is below all current profiles — free to enter
                $results[] = [
                    'position' => $targetPosition,
                    'amount' => $minIncrement,
                    'reachable' => true,
                ];
                continue;
            }

            // The profile at targetPosition has total_real_clp
            // To surpass them, you need their total + minIncrement
            $profileAtPosition = $rows[$targetPosition - 1];
            $required = ($profileAtPosition['total_real_clp'] - $currentTotal) + $minIncrement;
            $required = max($minIncrement, $required);

            $results[] = [
                'position' => $targetPosition,
                'amount' => $required,
                'reachable' => true,
            ];
        }

        return $results;
    }

    private function emptyPositionProjections(): array
    {
        return [
            ['position' => 10, 'amount' => RankingService::MIN_INCREMENT_CLP, 'reachable' => true],
            ['position' => 5, 'amount' => RankingService::MIN_INCREMENT_CLP, 'reachable' => true],
            ['position' => 3, 'amount' => RankingService::MIN_INCREMENT_CLP, 'reachable' => true],
            ['position' => 1, 'amount' => RankingService::MIN_INCREMENT_CLP, 'reachable' => true],
        ];
    }

}