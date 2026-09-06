<?php

namespace App\Services\Payments;

use App\Enums\PaymentGatewayConfirmationStatus;
use Illuminate\Support\Str;

class LocalMercadoPagoGateway implements PaymentGatewayInterface
{
    private const TOKEN_PREFIX = 'local_mp_';

    public function __construct(private LocalMercadoPagoStore $store) {}

    public function create(int $amountClp, string $externalReference, string $sessionId, array $metadata = []): array
    {
        $token = self::TOKEN_PREFIX.Str::ulid();

        $this->store->createPending($token, $amountClp, $externalReference);

        return [
            'url' => route('local.mercadopago.checkout', ['token' => $token]),
            'token' => $token,
        ];
    }

    public function confirm(string $transactionToken): array
    {
        $payload = $this->store->find($transactionToken);

        if (! $payload) {
            return [
                'status' => PaymentGatewayConfirmationStatus::Failed->value,
                'amount' => 0,
                'transaction_id' => $transactionToken,
                'approved_at' => null,
                'payer_id' => null,
                'raw' => [],
            ];
        }

        return [
            'status' => $payload['status'],
            'amount' => (int) $payload['amount'],
            'transaction_id' => $transactionToken,
            'approved_at' => $payload['approved_at'] ?? null,
            'payer_id' => $payload['payer_id'] ?? null,
            'raw' => $payload,
        ];
    }

    public function isAuthorized(array $confirmation): bool
    {
        return ($confirmation['status'] ?? null) === PaymentGatewayConfirmationStatus::Approved->value;
    }

    public function statusInfo(string $transactionToken): array
    {
        return $this->confirm($transactionToken);
    }
}
