<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Models\MasterData\EntityType;
use App\Models\MasterData\OrganizationType;
use App\Repositories\MasterData\MasterRecordRepository;
use App\Repositories\MasterData\OrganizationRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Organization aggregate service.
 *
 * Organizations are rooted at a master_record and typed by an organization_type
 * row. Creating one is: master_record -> organization, in a single tenant-scoped
 * transaction. The type is resolved either by explicit id or by its tenant-scoped
 * code (e.g. 'VENDOR').
 */
final class OrganizationService extends BaseMasterDataService
{
    public function __construct(
        OrganizationRepository $repository,
        \App\Audit\AuditRecorder $audit,
        private readonly MasterRecordRepository $masterRepository,
        private readonly EntityType $entityTypeModel,
        private readonly OrganizationType $organizationTypeModel,
    ) {
        parent::__construct($repository, $audit);
    }

    protected function eventPrefix(): string
    {
        return 'md.master_record';
    }

    protected function entityName(): string
    {
        return 'organization';
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $tenant = (string) TenantContext::tenantId();

            $master = $this->masterRepository->create([
                'tenant_id' => $tenant,
                'entity_type_id' => $this->entityTypeId('ORGANIZATION'),
                'external_ref' => $data['external_ref'] ?? null,
            ]);

            $organization = $this->repository->create([
                'tenant_id' => $tenant,
                'master_record_id' => $master->getKey(),
                'organization_type_id' => $this->organizationTypeId($data),
                'name' => $data['name'] ?? null,
            ]);

            $this->audit->record(
                'md.master_record.created',
                $this->entityName(),
                (string) $organization->getKey(),
            );

            return $organization->load(['masterRecord', 'organizationType']);
        });
    }

    private function entityTypeId(string $code): string
    {
        return (string) $this->entityTypeModel
            ->withoutGlobalScopes()
            ->where('code', $code)
            ->where('tenant_id', TenantContext::tenantId())
            ->value('id');
    }

    protected function childModelsForPurge(): array
    {
        return [
            \App\Models\MasterData\OrganizationIdentifier::class,
            \App\Models\MasterData\OrganizationContact::class,
            \App\Models\MasterData\OrganizationRelationship::class,
        ];
    }

    private function organizationTypeId(array $data): string
    {
        if (! empty($data['organization_type_id'])) {
            return (string) $data['organization_type_id'];
        }

        $code = $data['organization_type_code'] ?? 'VENDOR';

        return (string) $this->organizationTypeModel
            ->withoutGlobalScopes()
            ->where('code', $code)
            ->where('tenant_id', TenantContext::tenantId())
            ->value('id');
    }
}
