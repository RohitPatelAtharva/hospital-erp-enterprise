<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Models\MasterData\ReferenceCategory;
use App\Models\MasterData\ReferenceVersion;
use App\Repositories\MasterData\ReferenceValueRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Reference value service.
 *
 * A reference value is a typed, tenant-scoped controlled term grouped under a
 * reference_category and pinned to a reference_version edition. The service
 * resolves the category and version (by code, or by explicit id) and creates the
 * value in a single tenant-scoped transaction.
 */
final class ReferenceValueService extends BaseMasterDataService
{
    public function __construct(
        ReferenceValueRepository $repository,
        \App\Audit\AuditRecorder $audit,
        private readonly ReferenceCategory $categoryModel,
        private readonly ReferenceVersion $versionModel,
    ) {
        parent::__construct($repository, $audit);
    }

    protected function eventPrefix(): string
    {
        return 'md.reference';
    }

    protected function entityName(): string
    {
        return 'reference_value';
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $tenant = (string) TenantContext::tenantId();

            $value = $this->repository->create([
                'tenant_id' => $tenant,
                'reference_category_id' => $this->categoryId($data),
                'reference_version_id' => $this->versionId($data),
                'code' => $data['code'],
            ]);

            $this->audit->record(
                'md.reference.value_created',
                $this->entityName(),
                (string) $value->getKey(),
            );

            return $value->load(['category', 'referenceVersion']);
        });
    }

    private function categoryId(array $data): string
    {
        if (! empty($data['reference_category_id'])) {
            return (string) $data['reference_category_id'];
        }

        return (string) $this->categoryModel
            ->withoutGlobalScopes()
            ->where('code', $data['category_code'])
            ->where('tenant_id', TenantContext::tenantId())
            ->value('id');
    }

    private function versionId(array $data): string
    {
        if (! empty($data['reference_version_id'])) {
            return (string) $data['reference_version_id'];
        }

        return (string) $this->versionModel
            ->withoutGlobalScopes()
            ->where('code', $data['version_code'])
            ->where('tenant_id', TenantContext::tenantId())
            ->value('id');
    }
}
