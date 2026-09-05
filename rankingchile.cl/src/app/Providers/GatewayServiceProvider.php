<?php

namespace App\Providers;

use App\Services\Payments\MercadoPagoGateway;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class GatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // D-30: el MVP usa UNA sola pasarela = Mercado Pago Chile (Checkout API).
        $this->app->singleton(MercadoPagoGateway::class, function ($app) {
            $cfg = config('mercadopago');
            return new MercadoPagoGateway(
                (string) $cfg['access_token'],
                (string) $cfg['webhook_url'],
                (string) url('/pagos/return')
            );
        });

        $this->app->bind(PaymentGatewayInterface::class, MercadoPagoGateway::class);
    }

    public function boot(): void
    {
        //
    }
}
