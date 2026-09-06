<?php

namespace App\Enums;

enum ProfileSourceType: string
{
    case Website = 'website';
    case MercadoPublico = 'mercado_publico';
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case X = 'x';
    case LinkedIn = 'linkedin';
    case YouTube = 'youtube';
    case TikTok = 'tiktok';
    case Spotify = 'spotify';
    case Other = 'other';
}
