<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Repositories\MasterData\VersionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Version service.
 *
 * The version table is an append-only point-in-time history of master records
 * (workflow §8). This service exposes the documented read surface — the
 * version list for a master record — and records a version in the same
 * transaction as the underlying change so history is never orphaned.
 */
final class VersionService
{
    public function __construct(
        private readonly VersionRepository $repository,
        private readonly \App\Audit\AuditRecorder $audit,
    ) {
    }

    /**
     * Paginated version history for a master record (10-API §18).
     *
     * @return LengthAwarePaginator<int, \App\Models\MasterData\Version>
     */
    public function forMasterRecord(string $masterRecordId): LengthAwarePaginator
    {
        return $this->repository
            ->newQuery()
            ->where('master_record_id', $masterRecordId)
            ->orderByDesc('version_number')
            ->paginate(25);
    }

    /**
     * Record the next version for a master record. Returns the version_number
     * created. Uses the ambient transaction (joining the caller's if one is
     * active) so the version and the source change commit together.
     */
    public function recordVersion(string $masterRecordId, ?string $actorId = null): int
    {
        return DB::transaction(function () use ($masterRecordId, $actorId): int {
            $next = (int) $this->repository->newQuery()
                ->where('master_record_id', $masterRecordId)
                ->max('version_number') + 1;

            $this->repository->create([
                'tenant_id' => \App\Tenancy\TenantContext::tenantId(),
                'master_record_id' => $masterRecordId,
                'actor_id' => $actorId,
                'version_number' => $next,
            ]);

            return $next;
        });
    }

    /**
     * A single version by id (10-API §18 `{vid}`).
     */
    public function find(string $versionId): \Illuminate\Database\Eloquent\Model
    {
        return $this->repository->newQuery()->findOrFail($versionId);
    }

    /**
     * Compare a version against its immediate predecessor for the same master
     * record (10-API §18 `{vid}/diff`).
     *
     * The current schema stores version metadata only (no snapshot payload
     * column), so the diff reports the change envelope (version numbers, actor,
     * timestamps) rather than field-level payload differences.
     */
    public function diff(string $versionId): array
    {
        $version = $this->repository->newQuery()->findOrFail($versionId);

        $previous = $this->repository->newQuery()
            ->where('master_record_id', $version->master_record_id)
            ->where('version_number', '<', $version->version_number)
            ->orderByDesc('version_number')
            ->first();

        return [
            'current' => $version->only(['id', 'master_record_id', 'actor_id', 'version_number', 'occurred_at']),
            'previous' => $previous?->only(['id', 'actor_id', 'version_number', 'occurred_at']),
            'delta' => $previous === null
                ? ['type' => 'initial']
                : ['type' => 'revision', 'from' => $previous->version_number, 'to' => $version->version_number],
        ];
    }
}
