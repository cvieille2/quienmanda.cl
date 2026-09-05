<?php

namespace App\Enums;

enum ProfileStatus: string
{
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';
    case Rejected = 'rejected';
}