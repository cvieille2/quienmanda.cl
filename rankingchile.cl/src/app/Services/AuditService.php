<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    /**
     * Escribe un log de auditoría append-only.
     * Firma: audit(string $eventType, string $entityType, ?int $entityId, array $metadata = [],
     *                 string $actorType = 'system', ?int $actorId = null)
     */
    public function log(
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        array $metadata = [],
        string $actorType = AuditLog::ACTOR_SYSTEM,
        ?int $actorId = null
    ): AuditLog {
        return AuditLog::create([
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'event_type'  => $eventType,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'metadata'    => $metadata,
            'created_at'  => now(),
        ]);
    }
}
