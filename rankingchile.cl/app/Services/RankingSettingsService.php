<?php

namespace App\Services;

use App\Models\RankingSetting;

/**
 * D-048: ranking_settings = fila única (singleton) con los DEFAULTS usados al crear
 * periodos futuros. Nunca altera configuraciones ya congeladas de periodos existentes.
 */
class RankingSettingsService
{
    public const SCOPE_DEFAULT = 'default';

    /** Devuelve la fila única de defaults; la crea con valores por defecto si no existe. */
    public function defaults(): RankingSetting
    {
        $row = RankingSetting::query()->where('scope', self::SCOPE_DEFAULT)->first();

        return $row ?? RankingSetting::create([
            'scope' => self::SCOPE_DEFAULT,
            // resto de columnas -> defaults de la migración 000013
        ]);
    }

    public function updateDefaults(array $attributes, ?int $adminId = null): RankingSetting
    {
        $this->assertCategoryInvariant($attributes['category_scope'] ?? null, $attributes['category'] ?? null);

        $defaults = $this->defaults();
        $defaults->fill($attributes)->save(); // scope jamás cambia (singleton por BD: UNIQUE(scope))

        audit('ranking_settings_updated', 'ranking_setting', $defaults->id, [
            'scope' => self::SCOPE_DEFAULT,
        ], 'admin', $adminId);

        return $defaults;
    }

    /**
     * Configuración CONGELADA que se copia a ranking_periods.configuration al crear el periodo.
     * Regla settlement (decidida en Fase 1: D-042): el valor efectivo vive en la columna
     * ranking_periods.settlement_delay_minutes; NO se duplica dentro del JSON para evitar
     * valores contradictorios. Los límites de dinero SÍ van al JSON porque son la fuente
     * efectiva de validación de pagos del periodo.
     */
    public function frozenConfiguration(RankingSetting $s): array
    {
        return [
            'period_type'                => $s->period_type,
            'minimum_support_clp'        => $s->minimum_support_clp,
            'maximum_support_clp'        => $s->maximum_support_clp,
            'show_real_amounts'          => $s->show_real_amounts,
            'show_supporter_count'       => $s->show_supporter_count,
            'max_public_positions'       => $s->max_public_positions,
            'category_scope'             => $s->category_scope,
            'category'                   => $s->category,
            'sharing_enabled'            => $s->sharing_enabled,
            'community_profiles_enabled' => $s->community_profiles_enabled,
            'promotional_credits_enabled'=> $s->promotional_credits_enabled,
        ];
    }

    /** Invariante cruzado (D-006): all => category null; single => category obligatoria. */
    public function assertCategoryInvariant(?string $scope, ?string $category): void
    {
        if ($scope === 'all' && $category !== null) {
            throw new \InvalidArgumentException('category_scope=all debe tener category NULL.');
        }
        if ($scope === 'single' && ($category === null || trim($category) === '')) {
            throw new \InvalidArgumentException('category_scope=single exige category no nula.');
        }
    }
}