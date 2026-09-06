<?php

namespace App\Providers;

use App\Enums\PaymentGatewayDriver;
use App\Services\Payments\LocalMercadoPagoGateway;
use App\Services\Payments\MercadoPagoGateway;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class GatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->shouldUseLocalGateway()) {
            $this->app->singleton(LocalMercadoPagoGateway::class);
            $this->app->bind(PaymentGatewayInterface::class, LocalMercadoPagoGateway::class);

            return;
        }

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

    private function shouldUseLocalGateway(): bool
    {
        return config('mercadopago.driver') === PaymentGatewayDriver::Local->value
            && $this->app->environment(['local', 'testing']);
    }

    public function boot(): void
    {
        //
    }
}
