<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Repositories\MasterData\EnterprisePersonRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Enterprise person index (EPI) service.
 *
 * The EPI links a person's patient/staff identities into a single cross-role
 * row. It is a plain single-table resource: a tenant-scoped create with an
 * optional date-of-birth, audited like the entity resources. Patient/staff
 * aggregates reference this row by id.
 */
final class EnterprisePersonService extends BaseMasterDataService
{
    public function __construct(
        EnterprisePersonRepository $repository,
        \App\Audit\AuditRecorder $audit,
    ) {
        parent::__construct($repository, $audit);
    }

    protected function eventPrefix(): string
    {
        return 'md.master_record';
    }

    protected function entityName(): string
    {
        return 'enterprise_person';
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $row = $this->repository->create([
                'tenant_id' => (string) TenantContext::tenantId(),
                'name' => $data['name'] ?? null,
                'dob' => $data['dob'] ?? null,
            ]);

            $this->audit->record(
                'md.master_record.created',
                $this->entityName(),
                (string) $row->getKey(),
            );

            return $row;
        });
    }
}
