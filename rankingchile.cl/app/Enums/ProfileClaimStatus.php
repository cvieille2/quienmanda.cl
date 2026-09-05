<?php

namespace App\Enums;

enum ProfileClaimStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}