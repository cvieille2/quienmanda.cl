<?php

namespace App\Enums;

enum OutboundClickSource: string
{
    case Ranking = 'ranking';
    case Profile = 'profile';
    case Category = 'category';
    case Share = 'share';

    public static function fromRequestValue(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
