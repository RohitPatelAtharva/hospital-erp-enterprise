<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Audit\AuditRecorder;
use App\Repositories\BaseRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Shared read/list/lifecycle behaviour for Master Data aggregate services.
 *
 * Provides the generic list / find / update / deactivate paths that operate on
 * a single entity repository, and forces each concrete service to supply the
 * event prefix + entity name used when recording audit events. Concrete create()
 * implementations orchestrate the full aggregate (master_record, enterprise_person
 * where applicable) inside a transaction and assign the active tenant.
 *
 * Tenant scoping is delegated to the model's global scope (BaseModel), so reads
 * never cross tenants; writes always stamp the active tenant explicitly.
 */
abstract class BaseMasterDataService
{
    public function __construct(
        protected BaseRepository $repository,
        protected AuditRecorder $audit,
    ) {
    }

    /**
     * Canonical `md.*` event prefix for this entity (e.g. 'md.master_record').
     */
    abstract protected function eventPrefix(): string;

    /**
     * Canonical entity/resource name used in audit context (e.g. 'patient').
     */
    abstract protected function entityName(): string;

    /**
     * Expose the underlying repository for read queries (list/find).
     */
    public function repository(): BaseRepository
    {
        return $this->repository;
    }

    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->newQuery()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data): Model {
            unset($data['tenant_id']); // tenant identity is immutable per row.

            $fresh = $this->repository->update($model, $data);
            $this->audit->record(
                $this->eventPrefix().'.updated',
                $this->entityName(),
                (string) $model->getKey(),
            );

            return $fresh;
        });
    }

    /**
     * Transition the record between lifecycle states (02-Workflow.md §4).
     *
     * Lifecycle states are stored in the canonical `status` column. The current
     * persisted state is validated against the documented set of allowed source
     * states and every change is audited under the canonical event prefix.
     *
     * @param  list<string>  $allowedSources  source states from which this transition is legal
     */
    protected function transition(Model $model, string $to, array $allowedSources, string $suffix): Model
    {
        $current = (string) ($model->status ?? $model->getRawOriginal('status'));

        if (! in_array($current, $allowedSources, true)) {
            throw new \InvalidArgumentException("Cannot transition [{$current}] to [{$to}].");
        }

        return DB::transaction(function () use ($model, $to, $suffix): Model {
            $model->update(['status' => $to]);
            $this->audit->record(
                $this->eventPrefix().'.'.$suffix,
                $this->entityName(),
                (string) $model->getKey(),
                $suffix,
            );

            return $model->fresh();
        });
    }

    public function deactivate(Model $model): Model
    {
        return $this->transition($model, 'inactive', ['active'], 'deactivated');
    }

    public function reactivate(Model $model): Model
    {
        return $this->transition($model, 'active', ['inactive'], 'reactivated');
    }

    public function archive(Model $model): Model
    {
        return $this->transition($model, 'archived', ['active', 'inactive'], 'archived');
    }

    public function restore(Model $model): Model
    {
        return $this->transition($model, 'active', ['archived'], 'restored');
    }

    public function purge(Model $model): void
    {
        $this->purgeAggregate($model, $this->childModelsForPurge());
    }

    /**
     * Entity child models removed before the entity + master record are purged.
     * Overridden by aggregates that own child rows.
     *
     * @return list<class-string>
     */
    protected function childModelsForPurge(): array
    {
        return [];
    }

    /**
     * Governed hard delete (02-Workflow.md §16). Deactivation is the default;
     * purge is an elevated, audited exception. The aggregate (child records,
     * the entity row, and its master record) is removed in one transaction.
     *
     * @param  list<class-string>  $childModels  entity child model classes to purge first
     */
    protected function purgeAggregate(Model $model, array $childModels = []): void
    {
        DB::transaction(function () use ($model, $childModels): void {
            foreach ($childModels as $childClass) {
                $childClass::withoutGlobalScopes()
                    ->where($model->getForeignKey(), $model->getKey())
                    ->forceDelete();
            }

            $masterId = $model->master_record_id ?? null;

            $model->forceDelete();

            if ($masterId !== null) {
                \App\Models\MasterData\MasterRecord::withoutGlobalScopes()
                    ->whereKey($masterId)
                    ->forceDelete();
            }

            $this->audit->record(
                $this->eventPrefix().'.purged',
                $this->entityName(),
                (string) $model->getKey(),
                'purge',
            );
        });
    }

    protected function tenantId(): string
    {
        return (string) TenantContext::tenantId();
    }
}
