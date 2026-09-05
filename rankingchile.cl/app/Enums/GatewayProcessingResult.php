<?php

namespace App\Enums;

enum GatewayProcessingResult: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Duplicate = 'duplicate';
    case Ignored = 'ignored';
    case Error = 'error';
}
