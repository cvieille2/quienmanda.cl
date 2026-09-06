<?php

namespace App\Enums;

enum ProfileLinkType: string
{
    case Source = 'source';
    case Website = 'website';
    case Social = 'social';
    case Destination = 'destination';
    case Reference = 'reference';
    case Other = 'other';
}
