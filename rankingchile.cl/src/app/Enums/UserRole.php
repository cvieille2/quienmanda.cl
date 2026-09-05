<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case Moderator = 'moderator';
    case FinanceReviewer = 'finance_reviewer';
}