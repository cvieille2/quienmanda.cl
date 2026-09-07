<?php

namespace App\Enums;

enum RankingPeriodWindow: string
{
    case TODAY = 'today';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public function label(): string
    {
        return match ($this) {
            self::TODAY => 'Hoy',
            self::WEEK => 'Semana',
            self::MONTH => 'Mes',
            self::YEAR => 'Año',
        };
    }

    public function headline(): string
    {
        return match ($this) {
            self::TODAY => '¿Quién manda hoy?',
            self::WEEK => '¿Quién manda esta semana?',
            self::MONTH => '¿Quién manda este mes?',
            self::YEAR => '¿Quién manda este año?',
        };
    }

    public function statsLabel(): string
    {
        return match ($this) {
            self::TODAY => 'hoy',
            self::WEEK => 'esta semana',
            self::MONTH => 'este mes',
            self::YEAR => 'este año',
        };
    }

    public static function fromQuery(?string $value): self
    {
        return self::tryFrom($value ?? '') ?? self::WEEK;
    }
}
