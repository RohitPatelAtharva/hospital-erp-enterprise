<?php

declare(strict_types=1);

namespace App\Audit;

use App\Audit\Contracts\AuditStore;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Records audit events following docs/modules/master-data/13-Audit.md.
 *
 * The recorder fills actor, tenant, and correlation context automatically and
 * sanitizes context to prevent PHI/secrets reaching the audit store. It is the
 * single entry point for audit writes so the write path stays consistent and
 * testable.
 */
final class AuditRecorder
{
    public function __construct(private readonly AuditStore $store)
    {
    }

    public function record(
        string $event,
        string $entity,
        ?string $entityId = null,
        ?string $action = null,
        array $context = [],
        ?string $actorId = null,
    ): void {
        $record = new AuditRecord(
            event: $event,
            entity: $entity,
            entityId: $entityId,
            action: $action,
            actorId: $actorId ?? $this->currentActorId(),
            tenantId: TenantContext::tenantId(),
            correlationId: $this->correlationId(),
            context: AuditSanitizer::sanitize($context),
        );

        $this->store->record($record);
    }

    private function currentActorId(): ?string
    {
        return Auth::id() !== null ? (string) Auth::id() : null;
    }

    private function correlationId(): ?string
    {
        return app()->bound('request-correlation-id')
            ? (string) app('request-correlation-id')
            : null;
    }
}
