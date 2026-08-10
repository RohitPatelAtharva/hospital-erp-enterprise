<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Models\MasterData\EntityType;
use App\Repositories\MasterData\EnterprisePersonRepository;
use App\Repositories\MasterData\MasterRecordRepository;
use App\Repositories\MasterData\StaffRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Staff aggregate service.
 *
 * Staff mirror the patient shape: enterprise_person -> master_record -> staff,
 * all in one tenant-scoped transaction.
 */
final class StaffService extends BaseMasterDataService
{
    public function __construct(
        StaffRepository $repository,
        \App\Audit\AuditRecorder $audit,
        private readonly EnterprisePersonRepository $personRepository,
        private readonly MasterRecordRepository $masterRepository,
        private readonly EntityType $entityTypeModel,
    ) {
        parent::__construct($repository, $audit);
    }

    protected function eventPrefix(): string
    {
        return 'md.master_record';
    }

    protected function entityName(): string
    {
        return 'staff';
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $tenant = (string) TenantContext::tenantId();

            $person = $this->personRepository->create([
                'tenant_id' => $tenant,
                'name' => $data['name'] ?? null,
                'dob' => $data['dob'] ?? null,
            ]);

            $master = $this->masterRepository->create([
                'tenant_id' => $tenant,
                'entity_type_id' => $this->entityTypeId('STAFF'),
                'external_ref' => $data['external_ref'] ?? null,
            ]);

            $staff = $this->repository->create([
                'tenant_id' => $tenant,
                'master_record_id' => $master->getKey(),
                'enterprise_person_id' => $person->getKey(),
                'name' => $data['name'] ?? null,
            ]);

            $this->audit->record(
                'md.master_record.created',
                $this->entityName(),
                (string) $staff->getKey(),
            );

            return $staff->load(['masterRecord', 'enterprisePerson']);
        });
    }

    protected function childModelsForPurge(): array
    {
        return [
            \App\Models\MasterData\StaffIdentifier::class,
            \App\Models\MasterData\StaffCredential::class,
            \App\Models\MasterData\StaffConsent::class,
            \App\Models\MasterData\StaffDemographic::class,
        ];
    }

    private function entityTypeId(string $code): string
    {
        return (string) $this->entityTypeModel
            ->withoutGlobalScopes()
            ->where('code', $code)
            ->where('tenant_id', TenantContext::tenantId())
            ->value('id');
    }
}
