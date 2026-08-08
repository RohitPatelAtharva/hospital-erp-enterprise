<?php

declare(strict_types=1);

namespace App\Services;

use App\Audit\AuditRecorder;
use Illuminate\Support\Facades\DB;

/**
 * Base service.
 *
 * Services orchestrate application use-cases: validate input, coordinate
 * repositories/actions, run transactions, and dispatch audit events. Business
 * decisions live in the domain; services sequence them.
 */
abstract class BaseService
{
    public function __construct(protected readonly AuditRecorder $audit)
    {
    }

    /** Runs the callback inside a database transaction (ACID for writes). */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    protected function audit(
        string $event,
        string $entity,
        ?string $entityId = null,
        ?string $action = null,
        array $context = [],
    ): void {
        $this->audit->record($event, $entity, $entityId, $action, $context);
    }
}
