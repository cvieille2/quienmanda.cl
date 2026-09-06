<?php

namespace App\Enums;

enum PaymentGatewayDriver: string
{
    case MercadoPago = 'mercadopago';
    case Local = 'local';
}
