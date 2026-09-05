<?php

namespace App\Enums;

enum SupportTransactionType: string
{
    case Real = 'real';
    case Promotional = 'promotional';
}