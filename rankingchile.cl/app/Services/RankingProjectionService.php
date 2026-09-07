<?php

namespace App\Services;

use App\Domain\Ranking\RankingContext;
use App\Models\Profile;
use App\Models\ProfileCategory;
use App\Models\RankingPeriod;

/**
 * Proyecciones de ranking para la UI.
 *
 * Calcula posiciones alcanzables, costos para alcanzar un puesto objetivo,
 * y movimientos óptimos para múltiples perfiles.
 *
 * Reglas:
 * - MIN_INCREMENT_CLP (1000 CLP) es el mínimo para superar a alguien.
 * - Para alcanzar posición X, se debe superar al perfil actual en X.
 * - Las posiciones mostradas se calculan dinámicamente según el tamaño del ranking.
 */
class RankingProjectionService
{
    /**
     * Posiciones milestone preferidas, ordenadas por relevancia para el usuario.
     * Se filtra dinámicamente según el tamaño del ranking y la posición actual del perfil.
     */
    private const PREFERRED_POSITIONS = [10, 5, 3, 1];

    /**
     * Máximo número de opciones de posición a mostrar en la UI.
     */
    private const MAX_POSITION_OPTIONS = 4;

    /**
     * Default de perfiles asumidos cuando no hay período activo,
     * para calcular posiciones de referencia.
     */
    private const DEFAULT_RANKING_SIZE = 10;

    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
    ) {}

    public function projectAvailablePositionsForContext(RankingContext $context, ?int $profileId = null): array
    {
        $rows = $this->ranking->getRanking($context, false);

        $totalProfiles = count($rows);
        $currentPosition = null;
        $currentTotal = 0;

        if ($profileId !== null) {
            $entry = collect($rows)->firstWhere('profile_id', $profileId);

            if ($entry) {
                $currentPosition = $entry['position'];
                $currentTotal = $entry['total_real_clp'];
            }
        }

        return $this->computeDynamicPositions($rows, $totalProfiles, $currentPosition, $currentTotal);
    }

    public function projectMovesForProfilesForContext(array $profileIds, RankingContext $context): array
    {
        $rows = $this->ranking->getRanking($context, true);
        $results = [];

        if (empty($rows)) {
            return array_fill_keys($profileIds, [
                'profile_id' => null,
                'target_position' => null,
                'required_amount' => 0,
                'projected_position' => null,
                'projected_total_clp' => 0,
                'cta_label' => 'Sin ranking disponible.',
            ]);
        }

        foreach ($profileIds as $profileId) {
            $entry = collect($rows)->firstWhere('profile_id', $profileId);

            if (! $entry) {
                $results[$profileId] = [
                    'profile_id' => $profileId,
                    'target_position' => null,
                    'required_amount' => 0,
                    'projected_position' => null,
                    'projected_total_clp' => 0,
                    'cta_label' => 'Perfil no rankeable.',
                ];

                continue;
            }

            $currentPosition = (int) $entry['position'];
            $targetPosition = max(1, $currentPosition - 1);
            $targetEntry = $currentPosition === 1
                ? ($rows[1] ?? null)
                : ($rows[$targetPosition - 1] ?? null);

            // En la home la acción principal es concreta: superar al perfil
            // inmediatamente superior. El #1 puede defenderse superando al
            // #2 (o con el mínimo si está solo).
            $requiredAmount = $targetEntry
                ? max(
                    RankingService::MIN_INCREMENT_CLP,
                    ($targetEntry['total_real_clp'] - $entry['total_real_clp']) + RankingService::MIN_INCREMENT_CLP,
                )
                : RankingService::MIN_INCREMENT_CLP;

            $results[$profileId] = [
                'profile_id' => $profileId,
                'target_position' => $targetPosition,
                'required_amount' => $requiredAmount,
                'current_position' => $currentPosition,
                'current_total_clp' => $entry['total_real_clp'],
                'projected_position' => $targetPosition,
                'projected_total_clp' => $entry['total_real_clp'] + $requiredAmount,
                'cta_label' => $currentPosition === 1 ? 'DEFENDER LA CORONA' : "ROBAR EL #{$targetPosition}",
            ];
        }

        return $results;
    }

    public function projectProfileMoveForContext(int $profileId, RankingContext $context, int $targetPosition = 1): array
    {
        $rows = $this->ranking->getRanking($context, false);
        $profileEntry = collect($rows)->firstWhere('profile_id', $profileId);

        if (! $profileEntry) {
            return $this->emptyProfileMove($profileId, $targetPosition);
        }

        $targetEntry = $rows[$targetPosition - 1] ?? null;

        if (! $targetEntry) {
            return $this->emptyProfileMove($profileId, $targetPosition);
        }

        $requiredAmount = max(
            RankingService::MIN_INCREMENT_CLP,
            ($targetEntry['total_real_clp'] - $profileEntry['total_real_clp']) + RankingService::MIN_INCREMENT_CLP,
        );

        return [
            'profile_id' => $profileId,
            'current_position' => $profileEntry['position'],
            'target_position' => $targetPosition,
            'required_amount' => $requiredAmount,
            'before' => $this->extractProfilesBefore($rows, $targetPosition),
            'after' => $this->extractProfilesAfter($rows, $targetPosition),
            'displaced_profiles' => $this->extractDisplacedProfiles($rows, $targetPosition, $profileEntry['position']),
        ];
    }

    /**
     * Proyecta la posición de un perfil tras agregar un monto dado.
     *
     * @return array{
     *     rank: int|null,
     *     tied: bool,
     *     description: string,
     *     current_position: int|null,
     *     current_total_clp: int,
     *     projected_total_clp: int,
     *     projected_position: int|null,
     *     to_top_amount: int,
     *     period_code?: string,
     * }
     */
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
     * Calcula posiciones alcanzables dinámicamente para un perfil (o perfil ficticio con 0 CLP).
     *
     * Las posiciones se computan según:
     * - Total de perfiles activos en el ranking.
     * - Posición actual del perfil (si se proporciona profileId).
     * - Máximo MAX_POSITION_OPTIONS opciones.
     * - Preferencias: [10, 5, 3, 1] pero solo las que tienen sentido.
     * - Si el ranking tiene N perfiles, no se ofrecen posiciones > N (se usa N).
     * - Si el perfil ya está en X, no se ofrecen posiciones >= X.
     * - Siempre se ofrece #1 si es alcanzable.
     *
     * @return list<array{position: int, amount: int, reachable: bool}>
     */
    public function projectAvailablePositions(
        ?RankingPeriod $period = null,
        ?int $profileId = null,
        ?ProfileCategory $category = null,
    ): array {
        $period ??= $this->periods->activePeriod();

        if (! $period) {
            return $this->emptyPositionProjections();
        }

        $rows = $category
            ? $this->ranking->rankingForCategory($period->id, $category, false)
            : $this->ranking->rankingForPeriod($period->id, false);

        $totalProfiles = count($rows);
        $currentPosition = null;
        $currentTotal = 0;

        if ($profileId) {
            $entry = collect($rows)->firstWhere('profile_id', $profileId);
            if ($entry) {
                $currentPosition = $entry['position'];
                $currentTotal = $entry['total_real_clp'];
            }
        }

        return $this->computeDynamicPositions($rows, $totalProfiles, $currentPosition, $currentTotal);
    }

    /**
     * Proyecta el movimiento de un perfil hacia una posición objetivo.
     *
     * Calcula el monto requerido para superar al perfil en la posición objetivo,
     * junto con los perfiles afectados por el movimiento.
     *
     * @return array{
     *     profile_id: int,
     *     current_position: int|null,
     *     target_position: int,
     *     required_amount: int,
     *     before: list<array{profile_id: int, display_name: string, position: int, total_real_clp: int}>,
     *     after: list<array{profile_id: int, display_name: string, position: int, total_real_clp: int}>,
     *     displaced_profiles: list<array{profile_id: int, display_name: string, position: int, total_real_clp: int}>,
     * }
     */
    public function projectProfileMove(
        int $profileId,
        ?int $rankingPeriodId = null,
        ?int $categoryId = null,
        int $targetPosition = 1,
    ): array {
        $period = $rankingPeriodId
            ? RankingPeriod::find($rankingPeriodId)
            : $this->periods->activePeriod();

        if (! $period) {
            return $this->emptyProfileMove($profileId, $targetPosition);
        }

        $category = $categoryId ? ProfileCategory::find($categoryId) : null;

        $rows = $category
            ? $this->ranking->rankingForCategory($period->id, $category, false)
            : $this->ranking->rankingForPeriod($period->id, false);

        $profileEntry = collect($rows)->firstWhere('profile_id', $profileId);

        if (! $profileEntry) {
            return $this->emptyProfileMove($profileId, $targetPosition);
        }

        $currentPosition = $profileEntry['position'];
        $currentTotal = $profileEntry['total_real_clp'];
        $minIncrement = RankingService::MIN_INCREMENT_CLP;

        // Ya está en o encima del objetivo → no requiere monto
        if ($currentPosition <= $targetPosition) {
            return [
                'profile_id' => $profileId,
                'current_position' => $currentPosition,
                'target_position' => $targetPosition,
                'required_amount' => 0,
                'before' => $this->extractProfilesBefore($rows, $targetPosition),
                'after' => $this->extractProfilesAfter($rows, $targetPosition),
                'displaced_profiles' => [],
            ];
        }

        // Perfil en la posición objetivo
        $targetEntry = $rows[$targetPosition - 1] ?? null;

        if (! $targetEntry) {
            return $this->emptyProfileMove($profileId, $targetPosition);
        }

        $requiredAmount = max(
            $minIncrement,
            ($targetEntry['total_real_clp'] - $currentTotal) + $minIncrement,
        );

        return [
            'profile_id' => $profileId,
            'current_position' => $currentPosition,
            'target_position' => $targetPosition,
            'required_amount' => $requiredAmount,
            'before' => $this->extractProfilesBefore($rows, $targetPosition),
            'after' => $this->extractProfilesAfter($rows, $targetPosition),
            'displaced_profiles' => $this->extractDisplacedProfiles($rows, $targetPosition, $currentPosition),
        ];
    }

    /**
     * Calcula el mejor movimiento posible para cada perfil (posición más alta alcanzable).
     *
     * Para cada perfil, determina la posición más alta (#1 si es posible) que puede alcanzar
     * y el monto mínimo requerido para llegar ahí.
     *
     * @param list<int> $profileIds
     * @return array<int, array{target_position: int|null, required_amount: int, cta_label: string}>
     *         Claveados por profile_id.
     */
    public function projectMovesForProfiles(
        array $profileIds,
        ?RankingPeriod $period = null,
    ): array {
        $period ??= $this->periods->activePeriod();

        if (! $period) {
            return collect($profileIds)
                ->mapWithKeys(fn (int $id) => [$id => [
                    'target_position' => null,
                    'required_amount' => 0,
                    'cta_label' => 'No hay período activo.',
                ]])
                ->all();
        }

        $rows = $this->ranking->rankingForPeriod($period->id, true);
        $minIncrement = RankingService::MIN_INCREMENT_CLP;
        $totalProfiles = count($rows);
        $results = [];

        foreach ($profileIds as $profileId) {
            $entry = collect($rows)->firstWhere('profile_id', $profileId);

            if (! $entry) {
                $results[$profileId] = [
                    'target_position' => null,
                    'required_amount' => 0,
                    'cta_label' => 'Perfil no rankeable.',
                ];
                continue;
            }

            $currentPosition = $entry['position'];
            $currentTotal = $entry['total_real_clp'];

            $targetPosition = max(1, $currentPosition - 1);
            $targetEntry = $currentPosition === 1
                ? ($rows[1] ?? null)
                : ($rows[$targetPosition - 1] ?? null);
            $requiredAmount = max(
                $minIncrement,
                (($targetEntry['total_real_clp'] ?? 0) - $currentTotal) + $minIncrement,
            );

            $results[$profileId] = [
                'profile_id' => $profileId,
                'required_amount' => $requiredAmount,
                'current_position' => $currentPosition,
                'current_total_clp' => $currentTotal,
                'projected_position' => $targetPosition,
                'projected_total_clp' => $currentTotal + $requiredAmount,
                'target_position' => $targetPosition,
                'cta_label' => $currentPosition === 1 ? 'DEFENDER LA CORONA' : "ROBAR EL #{$targetPosition}",
            ];
        }

        return $results;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Calcula las posiciones dinámicas a ofrecer según el contexto del ranking y perfil.
     *
     * Reglas:
     * 1. Itera las posiciones preferidas [10, 5, 3, 1].
     * 2. Si N > 0 y la posición preferida > N, usa N (salvo si N < 1, usa 1).
     * 3. Si el perfil ya está en X, descarta posiciones >= X.
     * 4. Deduplica posiciones resultantes.
     * 5. Siempre incluye #1 si es alcanzable.
     * 6. Limita a MAX_POSITION_OPTIONS.
     *
     * @param list<array{position: int, profile_id: int, total_real_clp: int, ...}> $rows
     * @return list<array{position: int, amount: int, reachable: bool}>
     */
    private function computeDynamicPositions(
        array $rows,
        int $totalProfiles,
        ?int $currentPosition,
        int $currentTotal,
    ): array {
        $candidates = [];

        foreach (self::PREFERRED_POSITIONS as $preferredPosition) {
            if (count($candidates) >= self::MAX_POSITION_OPTIONS) {
                break;
            }

            // Ajustar al tamaño real del ranking
            $targetPosition = $this->capPositionToRanking($preferredPosition, $totalProfiles);

            // Si el perfil ya está en o encima de esta posición, descartar
            if ($currentPosition !== null && $currentPosition <= $targetPosition) {
                // Siempre ofrecer #1 si es alcanzable (perfil no está en #1)
                if ($targetPosition !== 1 || $currentPosition === 1) {
                    continue;
                }
            }

            // Deduplicar
            if (in_array($targetPosition, array_column($candidates, 'position'), strict: true)) {
                continue;
            }

            $required = $this->computeRequiredAmount($rows, $targetPosition, $currentTotal);

            $candidates[] = [
                'position' => $targetPosition,
                'amount' => $required,
                'reachable' => true,
            ];
        }

        // Garantizar que #1 siempre esté si el perfil no está en #1
        $hasPosition1 = in_array(1, array_column($candidates, 'position'), strict: true);
        $shouldOfferPosition1 = $currentPosition === null || $currentPosition > 1;

        if (! $hasPosition1 && $shouldOfferPosition1) {
            $required = $this->computeRequiredAmount($rows, 1, $currentTotal);

            array_unshift($candidates, [
                'position' => 1,
                'amount' => $required,
                'reachable' => true,
            ]);

            // Recortar si excede el máximo
            if (count($candidates) > self::MAX_POSITION_OPTIONS) {
                $candidates = array_slice($candidates, 0, self::MAX_POSITION_OPTIONS);
            }
        }

        return $candidates;
    }

    /**
     * Ajusta una posición preferida al tamaño real del ranking.
     *
     * Si el ranking tiene N perfiles y la posición preferida > N, usa N.
     * Si N = 0, usa 1 (mínimo viable).
     */
    private function capPositionToRanking(int $preferredPosition, int $totalProfiles): int
    {
        if ($totalProfiles <= 0) {
            return 1;
        }

        return min($preferredPosition, $totalProfiles);
    }

    /**
     * Calcula el monto requerido para alcanzar una posición específica.
     *
     * Si la posición está fuera del ranking (más allá del último perfil),
     * solo se necesita el mínimo incremento.
     *
     * @param list<array{position: int, total_real_clp: int, ...}> $rows
     */
    private function computeRequiredAmount(array $rows, int $targetPosition, int $currentTotal): int
    {
        $minIncrement = RankingService::MIN_INCREMENT_CLP;

        if (empty($rows) || $targetPosition > count($rows)) {
            return $minIncrement;
        }

        $profileAtPosition = $rows[$targetPosition - 1];

        return max(
            $minIncrement,
            ($profileAtPosition['total_real_clp'] - $currentTotal) + $minIncrement,
        );
    }

    /**
     * Proyecciones de fallback cuando no hay período activo.
     *
     * Usa el tamaño de ranking por defecto para calcular posiciones dinámicas.
     *
     * @return list<array{position: int, amount: int, reachable: bool}>
     */
    private function emptyPositionProjections(): array
    {
        return $this->computeDynamicPositions(
            rows: [],
            totalProfiles: self::DEFAULT_RANKING_SIZE,
            currentPosition: null,
            currentTotal: 0,
        );
    }

    /**
     * Resultado vacío para projectProfileMove cuando no hay datos disponibles.
     *
     * @return array{profile_id: int, current_position: null, target_position: int, required_amount: 0, before: list, after: list, displaced_profiles: list}
     */
    private function emptyProfileMove(int $profileId, int $targetPosition): array
    {
        return [
            'profile_id' => $profileId,
            'current_position' => null,
            'target_position' => $targetPosition,
            'required_amount' => 0,
            'before' => [],
            'after' => [],
            'displaced_profiles' => [],
        ];
    }

    /**
     * Extrae los perfiles en posiciones 1..targetPosition (inclusive).
     *
     * Son los perfiles que están en o "antes" de la posición objetivo.
     *
     * @param list<array{profile_id: int, display_name: string, position: int, total_real_clp: int, ...}> $rows
     * @return list<array{profile_id: int, display_name: string, position: int, total_real_clp: int}>
     */
    private function extractProfilesBefore(array $rows, int $targetPosition): array
    {
        return array_map(
            $this->extractProfileSummary(...),
            array_slice($rows, 0, $targetPosition),
        );
    }

    /**
     * Extrae los perfiles en posiciones (targetPosition+1)..N.
     *
     * Son los perfiles que están "después" de la posición objetivo.
     *
     * @param list<array{profile_id: int, display_name: string, position: int, total_real_clp: int, ...}> $rows
     * @return list<array{profile_id: int, display_name: string, position: int, total_real_clp: int}>
     */
    private function extractProfilesAfter(array $rows, int $targetPosition): array
    {
        return array_map(
            $this->extractProfileSummary(...),
            array_slice($rows, $targetPosition),
        );
    }

    /**
     * Extrae los perfiles que serían desplazados hacia abajo si el perfil sube.
     *
     * Son los perfiles en posiciones [targetPosition .. currentPosition-1].
     * Estos perfiles bajarían 1 posición al insertar al perfil en targetPosition.
     *
     * @param list<array{profile_id: int, display_name: string, position: int, total_real_clp: int, ...}> $rows
     * @return list<array{profile_id: int, display_name: string, position: int, total_real_clp: int}>
     */
    private function extractDisplacedProfiles(array $rows, int $targetPosition, int $currentPosition): array
    {
        $start = $targetPosition - 1; // 0-indexed
        $length = $currentPosition - $targetPosition;

        if ($length <= 0) {
            return [];
        }

        return array_map(
            $this->extractProfileSummary(...),
            array_slice($rows, $start, $length),
        );
    }

    /**
     * Extrae un resumen simplificado de un perfil del ranking.
     *
     * @param array{profile_id: int, display_name: string, position: int, total_real_clp: int, ...} $row
     * @return array{profile_id: int, display_name: string, position: int, total_real_clp: int}
     */
    private function extractProfileSummary(array $row): array
    {
        return [
            'profile_id' => $row['profile_id'],
            'display_name' => $row['display_name'],
            'position' => $row['position'],
            'total_real_clp' => $row['total_real_clp'],
        ];
    }
}
