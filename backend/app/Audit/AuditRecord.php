<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * A single audit record (value object).
 *
 * Mirrors the canonical `audit_reference` shape from
 * docs/modules/master-data/04-Database-Tables.md §26: event identity, actor,
 * entity, tenant, correlation, context, and timestamp. Persistence is handled
 * by an AuditStore; the DB store (Phase DB) writes to `audit_reference`.
 */
final class AuditRecord
{
    public function __construct(
        public readonly string $event,
        public readonly string $entity,
        public readonly ?string $entityId = null,
        public readonly ?string $action = null,
        public readonly ?string $actorId = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $correlationId = null,
        public readonly array $context = [],
        public readonly ?string $occurredAt = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'event' => $this->event,
            'entity' => $this->entity,
            'entity_id' => $this->entityId,
            'action' => $this->action,
            'actor_id' => $this->actorId,
            'tenant_id' => $this->tenantId,
            'correlation_id' => $this->correlationId,
            'context' => $this->context,
            'occurred_at' => $this->occurredAt ?? now()->toIso8601String(),
        ];
    }
}
