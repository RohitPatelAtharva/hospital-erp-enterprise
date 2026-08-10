<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Models\MasterData\EntityType;
use App\Repositories\MasterData\EnterprisePersonRepository;
use App\Repositories\MasterData\MasterRecordRepository;
use App\Repositories\MasterData\PatientRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Patient aggregate service.
 *
 * A patient is the patient entity rooted at a master_record and linked to an
 * enterprise_person (the cross-role EPI index). Creating a patient therefore
 * orchestrates three rows in one transaction: enterprise_person -> master_record
 * -> patient, stamped with the active tenant.
 */
final class PatientService extends BaseMasterDataService
{
    public function __construct(
        PatientRepository $repository,
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
        return 'patient';
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
                'entity_type_id' => $this->entityTypeId('PATIENT'),
                'external_ref' => $data['external_ref'] ?? null,
            ]);

            $patient = $this->repository->create([
                'tenant_id' => $tenant,
                'master_record_id' => $master->getKey(),
                'enterprise_person_id' => $person->getKey(),
                'name' => $data['name'] ?? null,
                'dob' => $data['dob'] ?? null,
                'sex' => $data['sex'] ?? null,
            ]);

            $this->audit->record(
                'md.master_record.created',
                $this->entityName(),
                (string) $patient->getKey(),
            );

            return $patient->load(['masterRecord', 'enterprisePerson']);
        });
    }

    protected function childModelsForPurge(): array
    {
        return [
            \App\Models\MasterData\PatientIdentifier::class,
            \App\Models\MasterData\PatientDemographic::class,
            \App\Models\MasterData\PatientConsent::class,
            \App\Models\MasterData\PatientRelation::class,
            \App\Models\MasterData\PatientAlias::class,
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
