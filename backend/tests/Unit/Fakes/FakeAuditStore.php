<?php

namespace Tests\Unit\Fakes;

use App\Audit\AuditRecord;
use App\Audit\Contracts\AuditStore;

/**
 * In-memory audit store for exercising the audit recorder in tests.
 */
class FakeAuditStore implements AuditStore
{
    /** @var AuditRecord[] */
    public array $records = [];

    public function record(AuditRecord $record): void
    {
        $this->records[] = $record;
    }
}
