<?php

namespace App\Enums;

enum RankingPeriodType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Semester = 'semester';
    case Yearly = 'yearly';
    case Custom = 'custom';
}