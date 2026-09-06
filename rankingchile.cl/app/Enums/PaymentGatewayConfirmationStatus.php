<?php

namespace App\Enums;

enum PaymentGatewayConfirmationStatus: string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Failed = 'failed';
}
