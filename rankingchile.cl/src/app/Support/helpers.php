<?php

use App\Services\AuditService;

if (! function_exists('audit')) {
    /**
     * Helper global para escribir logs de auditoría.
     * Firma flexible: audit($eventType, $entityType, $entityId, $metadata, $actorType, $actorId)
     */
    function audit(string $eventType, string $entityType, ?int $entityId = null, array $metadata = [], string $actorType = 'system', ?int $actorId = null)
    {
        return app(AuditService::class)->log($eventType, $entityType, $entityId, $metadata, $actorType, $actorId);
    }
}

if (! function_exists('analytics')) {
    /**
     * Helper global de analytics (backend, throttled).
     * D-046: la tabla analytics_events se ELIMINÓ del rebaseline. La traza funcional vive en
     * audit_logs; aquí solo se deja un no-op estable para futura emisión GA4/prospecto (v2).
     */
    function analytics(string $eventType, array $payload = [], ?int $profileId = null, ?string $sessionId = null): void
    {
        return; // no-op documentado (D-046): GA4 hook futuro mantiene firma estable.
    }
}
