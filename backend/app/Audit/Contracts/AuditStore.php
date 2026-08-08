<?php

declare(strict_types=1);

namespace App\Audit\Contracts;

use App\Audit\AuditRecord;

/**
 * Persistence seam for audit records.
 *
 * Phase 1 ships the LogAuditStore (structured log). The database phase adds a
 * DatabaseAuditStore writing to the canonical `audit_reference` table
 * (04-Database-Tables.md §26) in the same transaction as the source change.
 */
interface AuditStore
{
    public function record(AuditRecord $record): void;
}
