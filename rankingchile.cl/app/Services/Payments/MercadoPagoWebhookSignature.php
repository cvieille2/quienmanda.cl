<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

/**
 * BLOCKER-005 (TASK-PROD-003): verificación de firma HMAC-SHA256 de webhooks de Mercado Pago.
 *
 * Mercado Pago firma el webhook con el header x-signature=ts=...,v1=... usando
 * el "Secret" de la aplicación (config webhook_secret). El manifest firmado es:
 *   id:{data.id};request-id:{x-request-id};ts:{ts};
 *
 * Fail-closed: si no hay secret configurado en producción el webhook se rechaza,
 * porque un webhook sin firma NO es seguro como fuente de verdad de pagos reales.
 * En local/testing (simulación) la ausencia de secret se permite.
 */
class MercadoPagoWebhookSignature
{
    public function __construct(private readonly string $secret) {}

    public function verify(Request $request, string $dataId): bool
    {
        if ($this->secret === '') {
            return app()->environment('production') ? false : true;
        }

        $parts = $this->parseSignature((string) $request->header('x-signature', ''));
        $ts = $parts['ts'] ?? '';
        $provided = $parts['v1'] ?? '';

        if ($ts === '' || $provided === '') {
            return false;
        }

        $requestId = (string) $request->header('x-request-id', '');
        $manifest = 'id:'.$dataId.';request-id:'.$requestId.';ts:'.$ts.';';
        $expected = hash_hmac('sha256', $manifest, $this->secret);

        return hash_equals($expected, $provided);
    }

    /** @return array<string,string> */
    private function parseSignature(string $signature): array
    {
        $parts = [];

        foreach (explode(',', $signature) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $parts[trim($key)] = trim($value);
        }

        return $parts;
    }
}
