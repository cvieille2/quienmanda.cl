<?php

namespace App\Enums;

enum SupportTransactionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Failed = 'failed';
    case Disputed = 'disputed';
    case Refunded = 'refunded';
    case Reversed = 'reversed';
}