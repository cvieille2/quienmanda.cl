<?php

namespace App\Services;

use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Models\RankingPeriod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ciclo de vida de ranking_periods (dominio de periodos, rebaseline v3.1.0).
 *
 * - Intervalo half-open [starts_at, ends_at) (D-041).
 * - Máximo 1 periodo activo en MVP (D-001/D-006): activación con transacción + lock.
 * - configuration CONGELADA desde la creación (D-005/D-048); los cambios solo aplican
 *   a periodos futuros vía ranking_settings.
 * - Settlement window (D-042): active -> closed_pending_settlement -> closed -> snapshotted.
 * - cancelled (D-043) nunca publica ni rankea.
 *
 * Las transiciones NO se asignan libremente: se validan contra RankingPeriodStatus::canTransitionTo().
 */
class RankingPeriodService
{
    public const TZ = 'America/Santiago';

    public function __construct(private RankingSettingsService $settings) {}

    public function activePeriod(): ?RankingPeriod
    {
        return RankingPeriod::query()
            ->where('status', RankingPeriodStatus::Active->value)
            ->latest('id')
            ->first();
    }

    /**
     * Periodo (half-open) que contiene $at. Si no existe, lo crea como 'scheduled'
     * a partir de los defaults de ranking_settings (MVP: semanal).
     */
    public function periodFor(CarbonInterface $at): RankingPeriod
    {
        $instant = CarbonImmutable::instance($at)->timezone('UTC');

        $existing = RankingPeriod::query()
            ->where('starts_at', '<=', $instant)
            ->where('ends_at', '>', $instant) // half-open: ends_at EXCLUSIVO (D-041)
            ->latest('ends_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        [$code, $starts, $ends] = $this->rangeFor($instant);

        return RankingPeriod::firstOrCreate(
            ['code' => $code],
            $this->payloadForNewPeriod($code, $starts, $ends)
        );
    }

    /**
     * Activa un periodo 'scheduled' garantizando máx. 1 activo.
     * Transacción + SELECT ... FOR UPDATE (concurrencia de workers).
     */
    public function activate(RankingPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            $locked = RankingPeriod::query()->whereKey($period->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== RankingPeriodStatus::Scheduled) {
                return; // idempotente
            }

            $alreadyActive = RankingPeriod::query()
                ->where('status', RankingPeriodStatus::Active->value)
                ->where('id', '!=', $locked->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyActive) {
                throw new \RuntimeException('Máximo 1 periodo activo permitido en MVP (D-001/D-006).');
            }

            $this->transition($locked, RankingPeriodStatus::Active);
        });
    }

    /** Máquina de estados validada; audit con event_type period_*. Devuelve el periodo actualizado. */
    public function transition(RankingPeriod $period, RankingPeriodStatus $target, ?CarbonImmutable $at = null): RankingPeriod
    {
        if (! $period->status->canTransitionTo($target)) {
            throw new \LogicException(sprintf(
                'Transición inválida %s -> %s',
                $period->status->value,
                $target->value
            ));
        }

        $now = $at ?? CarbonImmutable::now();

        $period->update([
            'status'             => $target,
            'closed_at'          => in_array($target, [RankingPeriodStatus::ClosedPendingSettlement, RankingPeriodStatus::Closed], true)
                ? ($period->closed_at ?? $now) : $period->closed_at,
            'settled_at'         => $target === RankingPeriodStatus::Closed ? ($period->settled_at ?? $now) : $period->settled_at,
            'snapshotted_at'     => $target === RankingPeriodStatus::Snapshotted ? ($period->snapshotted_at ?? $now) : $period->snapshotted_at,
            'cancelled_at'       => $target === RankingPeriodStatus::Cancelled ? ($period->cancelled_at ?? $now) : $period->cancelled_at,
        ]);

        $event = match ($target) {
            RankingPeriodStatus::Active => \App\Models\AuditLog::EVT_PERIOD_ACTIVATED,
            RankingPeriodStatus::ClosedPendingSettlement => \App\Models\AuditLog::EVT_PERIOD_CLOSED,
            RankingPeriodStatus::Closed => \App\Models\AuditLog::EVT_PERIOD_SETTLED,
            RankingPeriodStatus::Snapshotted => \App\Models\AuditLog::EVT_PERIOD_SNAPSHOTTED,
            RankingPeriodStatus::Cancelled => \App\Models\AuditLog::EVT_PERIOD_CANCELLED,
            default => \App\Models\AuditLog::EVT_PERIOD_CREATED,
        };

        audit($event, 'ranking_period', $period->id, [
            'code'       => $period->code,
            'starts_at'  => (string) $period->starts_at,
            'ends_at'    => (string) $period->ends_at,
        ]);

        return $period->fresh();
    }

    /**
     * Rutina defensiva (llamada por cron y por el endpoint público): cierra periodos vencidos,
     * ejecuta el settlement window y garantiza el periodo vigente activo. Idempotente.
     */
    public function closeExpiredAndSettle(?CarbonInterface $now = null): ?RankingPeriod
    {
        $moment = $now ?? CarbonImmutable::now()->timezone(self::TZ);

        // 1) active -> closed_pending_settlement cuando ends_at <= ahora.
        $due = RankingPeriod::query()
            ->where('status', RankingPeriodStatus::Active->value)
            ->where('ends_at', '<=', $moment->timezone('UTC'))
            ->lockForUpdate()
            ->get();

        foreach ($due as $period) {
            $this->transition($period, RankingPeriodStatus::ClosedPendingSettlement, $moment);
        }

        // 2) closed_pending_settlement -> closed -> snapshotted cuando ya venció la ventana.
        $deadline = $moment->timezone('UTC');
        $pending = RankingPeriod::query()
            ->where('status', RankingPeriodStatus::ClosedPendingSettlement->value)
            ->lockForUpdate()
            ->get();

        foreach ($pending as $period) {
            $windowEnds = $period->ends_at->copy()->addMinutes($period->settlement_delay_minutes);
            if ($windowEnds->lte($deadline)) {
                $this->transition($period, RankingPeriodStatus::Closed, $moment);
                app(RankingSnapshotService::class)->generateBaseAndMarkSnapshotted($period, $moment);
            }
        }

        // 3) Asegura el periodo vigente activo (máx 1 activo).
        $this->ensureActivePeriod($moment);

        return $this->activePeriod();
    }

    public function ensureActivePeriod(CarbonInterface $at): void
    {
        $period = $this->periodFor($at);

        if ($period->status === RankingPeriodStatus::Scheduled) {
            $this->activate($period);
        }
    }

    /** Límites del periodo que contendrá $at según el periodo por defecto (MVP: semanal). */
    private function rangeFor(CarbonInterface $at): array
    {
        $defaults  = $this->settings->defaults();
        $type      = $defaults->period_type ?? RankingPeriodType::Weekly->value;
        $local     = CarbonImmutable::instance($at)->timezone(self::TZ)->startOfDay()->startOfWeek(CarbonImmutable::MONDAY);

        if ($type !== RankingPeriodType::Weekly->value) {
            throw new \LogicException(sprintf(
                'MVP soporta solo periodos weekly (ranking_settings.period_type=%s).',
                $type
            ));
        }

        $starts = $local->timezone('UTC');
        $ends   = $local->addWeek()->timezone('UTC'); // half-open: ends_at = lunes 00:00 siguiente (D-041)

        return [
            sprintf('W%s-%s', $local->isoWeekYear(), $local->isoWeek()),
            $starts,
            $ends,
        ];
    }

    private function payloadForNewPeriod(string $code, CarbonImmutable $starts, CarbonImmutable $ends): array
    {
        $defaults = $this->settings->defaults();
        $config   = $this->settings->frozenConfiguration($defaults); // sin settlement duplicate en JSON

        return [
            'public_id'              => (string) Str::ulid(),
            'code'                   => $code,
            'period_type'            => RankingPeriodType::Weekly->value,
            'starts_at'              => $starts,
            'ends_at'                => $ends,
            'settlement_delay_minutes' => $defaults->settlement_delay_minutes,
            'status'                 => RankingPeriodStatus::Scheduled,
            'configuration'          => $config, // CONGELADA desde la creación (D-005/D-048)
        ];
    }
}