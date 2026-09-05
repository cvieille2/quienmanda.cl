<?php

namespace App\Services\Payments;

/**
 * Contrato de una pasarela de pago del MVP.
 * D-30: el MVP usa UNA sola pasarela = Mercado Pago Chile (Checkout API).
 * Esta interfaz permite aislar la integración y facilita testing/otras pasarelas futuras.
 */
interface PaymentGatewayInterface
{
    /**
     * Crea una preferencia/checkout de pago y devuelve la URL de redirección y token.
     *
     * @param int    $amountClp        Monto en CLP (integer, >= 1000)
     * @param string $externalReference ULID público (idempotencia)
     * @param string $sessionId        Identificador de sesión (anti-fraude/riesgo)
     * @param array  $metadata         subject, notification_url, success_url, pending_url, failure_url
     * @return array{url:string, token:string}
     */
    public function create(int $amountClp, string $externalReference, string $sessionId, array $metadata = []): array;

    /**
     * Consulta el estado real del pago server-to-server (source of truth).
     * @return array{status:string, amount:int, transaction_id, approved_at, payer_id, raw}
     */
    public function confirm(string $transactionToken): array;

    /** True si el estado devuelto por confirm() representa un pago aprobado. */
    public function isAuthorized(array $confirmation): bool;

    /** Alias de confirm() para introspección. */
    public function statusInfo(string $transactionToken): array;
}
