<?php

declare(strict_types=1);

namespace App\Audit\Stores;

use App\Audit\AuditRecord;
use App\Audit\Contracts\AuditStore;
use Illuminate\Support\Facades\Log;

/**
 * Phase-1 audit store: writes records to the structured `audit` log channel.
 *
 * Provides an observable, PHI-safe audit trail without introducing a database
 * table this phase. The database phase replaces this with a DatabaseAuditStore
 * targeting the canonical `audit_reference` table (04-Database-Tables.md §26).
 */
final class LogAuditStore implements AuditStore
{
    public function record(AuditRecord $record): void
    {
        Log::channel('audit')->info('audit.event', $record->toArray());
    }
}
