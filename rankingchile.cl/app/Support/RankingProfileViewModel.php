<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\VerificationStatus;

/**
 * View Model para presentar una fila de ranking en Blade.
 *
 * Envuelve los datos crudos de RankingService::rankingForPeriod() y
 * exponer propiedades formateadas listas para rendering, eliminando
 * lógica de presentación de las plantillas.
 *
 * @see RankingService::rankingForPeriod()
 * @see RankingProjectionService::project()
 * @see \App\Models\RankingSnapshot
 */
class RankingProfileViewModel
{
    private const CLP_SYMBOL = '$';
    private const THOUSAND_SEP = '.';

    public function __construct(
        /** @var array Fila cruda de RankingService::rankingForPeriod() */
        private array $rankingRow,
        /** @var array|null Proyección de RankingProjectionService::project() para este perfil */
        private ?array $projection = null,
        /** @var array|null Snapshot del periodo anterior (RankingSnapshot toArray) */
        private ?array $previousSnapshot = null,
    ) {}

    // ──────────────────────────────────────────────────────────────────
    // Core data
    // ──────────────────────────────────────────────────────────────────

    public function profileId(): int
    {
        return $this->rankingRow['profile_id'];
    }

    public function slug(): string
    {
        return $this->rankingRow['slug'];
    }

    public function displayName(): string
    {
        return $this->rankingRow['display_name'];
    }

    public function position(): int
    {
        return $this->rankingRow['position'];
    }

    public function totalRealClp(): int
    {
        return $this->rankingRow['total_real_clp'];
    }

    /**
     * Formatea el total en CLP con separadores de miles.
     * Ejemplo: 10000 → "$10.000"
     */
    public function totalRealClpFormatted(): string
    {
        return self::CLP_SYMBOL . number_format(
            $this->totalRealClp(),
            0,
            ',',
            self::THOUSAND_SEP,
        );
    }

    public function supporterCount(): int
    {
        return $this->rankingRow['supporter_count'];
    }

    public function profileImageUrl(): ?string
    {
        return $this->rankingRow['profile_image_url'] ?? null;
    }

    public function profileViewsCount(): int
    {
        return $this->rankingRow['profile_views_count'] ?? 0;
    }

    public function destinationClicksCount(): int
    {
        return $this->rankingRow['destination_clicks_count'] ?? 0;
    }

    /**
     * Pluralización simple para el conteo de impulsores.
     *
     * - 0 personas → "0 personas impulsan"
     * - 1 persona  → "1 persona impulsa"
     * - 2+ personas → "N personas impulsan"
     */
    public function supporterCountPluralized(): string
    {
        $count = $this->supporterCount();

        if ($count === 1) {
            return '1 persona impulsa';
        }

        return $count . ' personas impulsan';
    }

    public function isLeader(): bool
    {
        return $this->position() === 1;
    }

    public function category(): ?string
    {
        return $this->rankingRow['category'] ?? null;
    }

    // ──────────────────────────────────────────────────────────────────
    // Verification
    // ──────────────────────────────────────────────────────────────────

    /**
     * Estado de verificación normalizado.
     */
    public function verificationStatus(): string
    {
        return $this->rankingRow['verification_status'] ?? VerificationStatus::Unverified->value;
    }

    /**
     * Badge visual según estado de verificación.
     * - ✓ verificado
     * - … pendiente
     * - (vacío) sin verificar
     */
    public function verificationBadge(): string
    {
        return match ($this->verificationStatus()) {
            VerificationStatus::Verified->value => '✓',
            VerificationStatus::Pending->value  => '…',
            default                              => '',
        };
    }

    /**
     * Color de fondo para el badge de verificación (Tailwind class).
     */
    public function verificationBadgeColor(): string
    {
        return match ($this->verificationStatus()) {
            VerificationStatus::Verified->value => 'bg-[#0ea5e9]',
            VerificationStatus::Pending->value  => 'bg-orange-500',
            default                              => 'bg-gray-400',
        };
    }

    public function isCommunityCreated(): bool
    {
        return (bool) ($this->rankingRow['is_community_created'] ?? false);
    }

    // ──────────────────────────────────────────────────────────────────
    // Competitive signals (from ranking_snapshots)
    // ──────────────────────────────────────────────────────────────────

    /**
     * Delta de posición respecto al periodo anterior.
     *
     * - Positivo (ej. +2) → subió 2 puestos
     * - Negativo (ej. -1)  → bajó 1 puesto
     * - null → sin datos previos
     */
    public function positionDelta(): ?int
    {
        if ($this->previousSnapshot === null) {
            return null;
        }

        $previousPosition = $this->previousSnapshot['final_position'] ?? null;

        if ($previousPosition === null) {
            return null;
        }

        // delta positivo = mejor posición (posición menor = mejor)
        return (int) $previousPosition - $this->position();
    }

    /**
     * Señal competitiva para rendering en la card.
     *
     * @return array{key: string, copy: string, positions: int}|null
     */
    public function competitiveSignal(): ?array
    {
        $delta = $this->positionDelta();

        if ($delta === null) {
            return null;
        }

        if ($delta > 0) {
            return [
                'key'      => 'moved_up',
                'copy'     => '↑ SUBIÓ ' . $delta,
                'positions' => $delta,
            ];
        }

        if ($delta < 0) {
            return [
                'key'      => 'moved_down',
                'copy'     => '↓ BAJÓ ' . abs($delta),
                'positions' => abs($delta),
            ];
        }

        // delta === 0 → misma posición
        return [
            'key'      => 'same_position',
            'copy'     => '= MANTIENE',
            'positions' => 0,
        ];
    }

    public function hasCompetitiveSignal(): bool
    {
        return $this->competitiveSignal() !== null;
    }

    // ──────────────────────────────────────────────────────────────────
    // CTA data (from projection)
    // ──────────────────────────────────────────────────────────────────

    /**
     * Posición objetivo sugerida por la proyección.
     */
    public function ctaTargetPosition(): int
    {
        if ($this->projection === null) {
            // Sin proyección: si es líder, defender #1; si no, aspirar a subir
            return $this->isLeader() ? 1 : max(1, $this->position() - 1);
        }

        return $this->projection['projected_position'] ?? 1;
    }

    /**
     * Monto requerido en CLP (entero) para alcanzar la posición objetivo.
     */
    public function ctaRequiredAmount(): int
    {
        if ($this->projection === null) {
            return 0;
        }

        // to_top_amount es lo que falta para llegar al #1
        // Si hay proyección, usamos la diferencia entre projected y current
        $currentTotal = $this->totalRealClp();
        $projectedTotal = $this->projection['projected_total_clp'] ?? $currentTotal;

        return max(0, $projectedTotal - $currentTotal);
    }

    /**
     * Monto formateado para display.
     */
    public function ctaRequiredAmountFormatted(): string
    {
        $amount = $this->ctaRequiredAmount();

        if ($amount === 0) {
            return self::CLP_SYMBOL . '0';
        }

        return self::CLP_SYMBOL . number_format(
            $amount,
            0,
            ',',
            self::THOUSAND_SEP,
        );
    }

    /**
     * Label del CTA según contexto.
     *
     * - Líder: "DEFENDER LA CORONA" (propietario) / "IMPULSAR AL #1" (público)
     * - No líder con proyección: "SUBIR AL #{target} · {amount}"
     * - No líder sin proyección: "IMPULSAR"
     */
    public function ctaLabel(): string
    {
        if ($this->isLeader()) {
            return 'DEFENDER LA CORONA';
        }

        if ($this->projection !== null) {
            $target = $this->projection['projected_position'] ?? null;
            $amount = $this->ctaRequiredAmountFormatted();

            if ($target !== null && $amount !== self::CLP_SYMBOL . '0') {
                return "SUBIR AL #{$target} · {$amount}";
            }
        }

        return 'IMPULSAR';
    }

    public function hasProjection(): bool
    {
        return $this->projection !== null;
    }

    // ──────────────────────────────────────────────────────────────────
    // For checkout
    // ──────────────────────────────────────────────────────────────────

    /**
     * Payload para el flujo de checkout.
     *
     * @return array{profile_id: int, target_position: int, required_amount: int}
     */
    public function checkoutPayload(): array
    {
        return [
            'profile_id'      => $this->profileId(),
            'target_position' => $this->ctaTargetPosition(),
            'required_amount' => $this->ctaRequiredAmount(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // For the leader card
    // ──────────────────────────────────────────────────────────────────

    /**
     * Diferencia en CLP entre el #1 y el #2.
     *
     * Solo tiene sentido para el líder. Retorna 0 si no hay segundo puesto.
     * Se calcula usando la data del ranking row: el leader tiene position=1,
     * y asumimos que el ranking completo está disponible vía la colllection
     * que construye los ViewModels. Se pasa como dato externo o se calcula
     * con la fórmula: behind_clp del #2 (que es lo que falta para alcanzar al #1).
     *
     * Si el ranking row incluye 'behind_clp' para otros miembros,
     * el gap se calcula así. Para el #1, behind_clp = 0.
     * Usamos el campo to_number_one_clp del segundo si está disponible,
     * o bien retornamos la diferencia directa.
     */
    public function positionGapToSecond(): int
    {
        if (! $this->isLeader()) {
            return 0;
        }

        // Si el ranking row tiene 'behind_clp' del líder (siempre 0),
        // necesitamos la data del #2. Como ViewModels se crean en batch,
        // el gap se provee externamente o se calcula en collection().
        // Como alternativa, si el rankingRow tiene un campo extra 'gap_to_second',
        // lo usamos. Sino, retornamos 0 (se debe poblar desde collection()).
        return $this->rankingRow['gap_to_second'] ?? 0;
    }

    // ──────────────────────────────────────────────────────────────────
    // Extra: additional fields from ranking row
    // ──────────────────────────────────────────────────────────────────

    public function totalPromotionalClp(): int
    {
        return $this->rankingRow['total_promotional_clp'] ?? 0;
    }

    public function behindClp(): ?int
    {
        return $this->rankingRow['behind_clp'] ?? null;
    }

    public function overtakeAboveClp(): ?int
    {
        return $this->rankingRow['overtake_above_clp'] ?? null;
    }

    public function toNumberOneClp(): ?int
    {
        return $this->rankingRow['to_number_one_clp'] ?? null;
    }

    // ──────────────────────────────────────────────────────────────────
    // Static factories
    // ──────────────────────────────────────────────────────────────────

    /**
     * Crea una instancia desde una fila de ranking.
     */
    public static function fromRankingRow(
        array $row,
        ?array $projection = null,
        ?array $previousSnapshot = null,
    ): self {
        return new self(
            rankingRow: $row,
            projection: $projection,
            previousSnapshot: $previousSnapshot,
        );
    }

    /**
     * Crea una colección de ViewModels desde un array de filas de ranking.
     *
     * Opcionalmente empareja proyecciones y snapshots previos por profile_id.
     * Calcula el gap_to_second para el líder.
     *
     * @param  array<int, array>  $rankingRows
     * @param  array<int, array>|null  $projections     Keyed by profile_id
     * @param  array<int, array>|null  $previousSnapshots  Keyed by profile_id
     * @return array<int, self>
     */
    public static function collection(
        array $rankingRows,
        ?array $projections = null,
        ?array $previousSnapshots = null,
    ): array {
        $projections ??= [];
        $previousSnapshots ??= [];

        // Indexar proyecciones y snapshots por profile_id para acceso O(1)
        $projectionsByKey = [];
        foreach ($projections as $proj) {
            $pid = $proj['profile_id'] ?? $proj['current_position'] ?? null;
            // Las proyecciones de RankingProjectionService no traen profile_id,
            // se asocian externamente. Si vienen indexadas por profile_id, usar tal cual.
            if (isset($proj['profile_id'])) {
                $projectionsByKey[$proj['profile_id']] = $proj;
            }
        }

        $snapshotsByKey = [];
        foreach ($previousSnapshots as $snap) {
            if (isset($snap['profile_id'])) {
                $snapshotsByKey[$snap['profile_id']] = $snap;
            }
        }

        // Calcular gap_to_second: diferencia entre #1 y #2
        $gapToSecond = 0;
        if (count($rankingRows) >= 2) {
            $gapToSecond = $rankingRows[0]['total_real_clp'] - $rankingRows[1]['total_real_clp'];
        }

        $viewModels = [];

        foreach ($rankingRows as $row) {
            $profileId = $row['profile_id'];

            // Enriquecer la fila del líder con gap_to_second
            $enrichedRow = $row;
            if (($row['position'] ?? 0) === 1) {
                $enrichedRow['gap_to_second'] = $gapToSecond;
            }

            $viewModels[] = new self(
                rankingRow: $enrichedRow,
                projection: $projectionsByKey[$profileId] ?? null,
                previousSnapshot: $snapshotsByKey[$profileId] ?? null,
            );
        }

        return $viewModels;
    }
}
