<?php

namespace App\Http\Controllers;

use App\Enums\PaymentGatewayDriver;
use App\Models\SupportTransaction;
use App\Services\Payments\LocalMercadoPagoStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocalMercadoPagoController extends Controller
{
    public function __construct(private LocalMercadoPagoStore $store) {}

    public function checkout(string $token): View
    {
        $this->abortUnlessEnabled();

        $tx = SupportTransaction::where('provider_transaction_id', $token)->firstOrFail();

        return view('local.mercadopago-checkout', [
            'token' => $token,
            'transaction' => $tx,
            'profile' => $tx->profile,
        ]);
    }

    public function approve(string $token): RedirectResponse
    {
        $this->abortUnlessEnabled();

        $tx = SupportTransaction::where('provider_transaction_id', $token)->firstOrFail();

        $this->store->markApproved($token);
        $this->dispatchWebhook($tx);

        return redirect()->route('payments.return', ['payment_id' => $token]);
    }

    public function reject(string $token): RedirectResponse
    {
        $this->abortUnlessEnabled();

        $tx = SupportTransaction::where('provider_transaction_id', $token)->firstOrFail();

        $this->store->markFailed($token);
        $this->dispatchWebhook($tx);

        return redirect()->route('payments.return', ['payment_id' => $token]);
    }

    private function dispatchWebhook(SupportTransaction $tx): void
    {
        $payload = [
            'type' => 'payment',
            'data' => ['id' => $tx->provider_transaction_id],
            'external_reference' => $tx->external_reference,
        ];

        $request = Request::create(
            '/api/webhooks/mercadopago',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        app(PaymentController::class)->webhook($request);
    }

    private function abortUnlessEnabled(): void
    {
        abort_unless(
            config('mercadopago.driver') === PaymentGatewayDriver::Local->value && app()->environment(['local', 'testing']),
            404
        );
    }
}
