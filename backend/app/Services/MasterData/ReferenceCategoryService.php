<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Repositories\MasterData\ReferenceCategoryRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Reference category service.
 *
 * Reference categories are the DGB-governed controlled vocabularies that group
 * reference values. They are a plain single-table resource (no aggregate
 * orchestration) but require an explicit tenant stamp and are audited like the
 * entity resources.
 */
final class ReferenceCategoryService extends BaseMasterDataService
{
    public function __construct(
        ReferenceCategoryRepository $repository,
        \App\Audit\AuditRecorder $audit,
    ) {
        parent::__construct($repository, $audit);
    }

    protected function eventPrefix(): string
    {
        return 'md.reference';
    }

    protected function entityName(): string
    {
        return 'reference_category';
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $row = $this->repository->create([
                'tenant_id' => (string) TenantContext::tenantId(),
                'code' => $data['code'],
            ]);

            $this->audit->record(
                'md.reference.category_created',
                $this->entityName(),
                (string) $row->getKey(),
            );

            return $row;
        });
    }
}
