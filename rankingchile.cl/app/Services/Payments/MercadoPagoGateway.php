<?php

namespace App\Services\Payments;

use App\Enums\PaymentGatewayConfirmationStatus;
use Illuminate\Support\Facades\Http;

/**
 * Mercado Pago Chile — Checkout API (D-30). ÚNICA pasarela del MVP.
 * Tarifa 3,19% + IVA, costo fijo $0 CLP, liberación al instante.
 * Webhook server-to-server con topic configurable (DECISION_REQUIRED-003 / TASK-001).
 *
 * Integración HTTP puro (sin SDK) para minimizar superficie y facilitar deploy FTP-only.
 * NO firma el payload con HMAC: la validación se hace con GET /v1/payments/{id} en confirm().
 */
class MercadoPagoGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.mercadopago.com';

    public function __construct(
        private string $accessToken,
        private string $notificationUrl, // notification_url (webhook server-to-server, fuente de verdad)
        private string $returnUrl,       // back_urls.success (frontend redirect, NO fuente de verdad)
    ) {}

    public function create(int $amountClp, string $externalReference, string $sessionId, array $metadata = []): array
    {
        $webhookUrl = $metadata['notification_url'] ?? $this->notificationUrl;

        $body = [
            'items' => [[
                'title' => 'Impulso a '.($metadata['subject'] ?? 'perfil').' en Quien Manda',
                'quantity' => 1,
                'unit_price' => $amountClp,
            ]],
            'external_reference' => $externalReference,
            'notification_url' => $webhookUrl,
            'back_urls' => [
                'success' => $metadata['success_url'] ?? $this->returnUrl,
                'pending' => $metadata['pending_url'] ?? $this->returnUrl,
                'failure' => $metadata['failure_url'] ?? $this->returnUrl,
            ],
            'auto_return' => 'approved',
        ];

        $resp = Http::withToken($this->accessToken)
            ->contentType('application/json')
            ->post(self::BASE_URL.'/checkout/preferences', $body);

        $body = $resp->json() ?? [];
        if (! $resp->successful() || empty($body['init_point']) || empty($body['id'])) {
            throw new \RuntimeException('MercadoPago: no se pudo crear la preferencia');
        }

        return ['url' => $body['init_point'], 'token' => $body['id']];
    }

    /** Confirma el estado del pago (server-to-server). GET /v1/payments/{payment_id}. */
    public function confirm(string $transactionToken): array
    {
        $resp = Http::withToken($this->accessToken)
            ->get(self::BASE_URL.'/v1/payments/'.rawurlencode($transactionToken));

        $body = $resp->json() ?? [];
        $mpStatus = strtolower((string) ($body['status'] ?? ''));
        // Mapeo: approved -> approved; pending/in_process -> pending; rejected/cancelled -> failed.
        $status = match ($mpStatus) {
            'approved' => PaymentGatewayConfirmationStatus::Approved->value,
            'pending', 'in_process' => PaymentGatewayConfirmationStatus::Pending->value,
            'rejected', 'cancelled' => PaymentGatewayConfirmationStatus::Failed->value,
            default => PaymentGatewayConfirmationStatus::Pending->value,
        };

        return [
            'status' => $status,
            'amount' => (int) ($body['transaction_amount'] ?? 0),
            'transaction_id' => $body['id'] ?? null,        // id del pago (payment_id)
            'approved_at' => $body['date_approved'] ?? null, // fecha proveedor (fallback ranking_qualified_at)
            'payer_id' => $body['payer']['id'] ?? null,   // userId/payer.id del comprador
            'raw' => $body,
        ];
    }

    public function isAuthorized(array $confirmation): bool
    {
        return ($confirmation['status'] ?? null) === PaymentGatewayConfirmationStatus::Approved->value; // status approved (200) = pagado
    }

    public function statusInfo(string $transactionToken): array
    {
        return $this->confirm($transactionToken);
    }
}
