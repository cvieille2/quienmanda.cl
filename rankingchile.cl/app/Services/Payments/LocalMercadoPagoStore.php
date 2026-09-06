<?php

namespace App\Services\Payments;

use App\Enums\PaymentGatewayConfirmationStatus;
use Illuminate\Support\Facades\Cache;

class LocalMercadoPagoStore
{
    private const CACHE_KEY_PREFIX = 'local_mercadopago_payment:';

    private const LOCAL_PAYER_ID = 'local-browser';

    public function createPending(string $token, int $amountClp, string $externalReference): void
    {
        $this->put($token, [
            'token' => $token,
            'amount' => $amountClp,
            'external_reference' => $externalReference,
            'status' => PaymentGatewayConfirmationStatus::Pending->value,
            'approved_at' => null,
            'payer_id' => self::LOCAL_PAYER_ID,
        ]);
    }

    public function markApproved(string $token): void
    {
        $payload = $this->find($token) ?? ['token' => $token];

        $this->put($token, array_merge($payload, [
            'status' => PaymentGatewayConfirmationStatus::Approved->value,
            'approved_at' => now()->toISOString(),
        ]));
    }

    public function markFailed(string $token): void
    {
        $payload = $this->find($token) ?? ['token' => $token];

        $this->put($token, array_merge($payload, [
            'status' => PaymentGatewayConfirmationStatus::Failed->value,
            'approved_at' => null,
        ]));
    }

    public function find(string $token): ?array
    {
        $payload = Cache::get($this->key($token));

        return is_array($payload) ? $payload : null;
    }

    private function put(string $token, array $payload): void
    {
        Cache::put(
            $this->key($token),
            $payload,
            now()->addHours((int) config('mercadopago.local_cache_ttl_hours'))
        );
    }

    private function key(string $token): string
    {
        return self::CACHE_KEY_PREFIX.$token;
    }
}
