<?php

declare(strict_types=1);

namespace App\Services\MasterData;

use App\Models\MasterData\EntityType;
use App\Repositories\MasterData\MasterRecordRepository;
use App\Repositories\MasterData\ProviderRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Provider aggregate service.
 *
 * Providers (external organizations/individuals) are rooted at a master_record
 * but are NOT linked to an enterprise_person (no patient/staff cross-role index).
 * Creating one is: master_record -> provider, in a single tenant-scoped
 * transaction.
 */
final class ProviderService extends BaseMasterDataService
{
    public function __construct(
        ProviderRepository $repository,
        \App\Audit\AuditRecorder $audit,
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
        return 'provider';
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $tenant = (string) TenantContext::tenantId();

            $master = $this->masterRepository->create([
                'tenant_id' => $tenant,
                'entity_type_id' => $this->entityTypeId('PROVIDER'),
                'external_ref' => $data['external_ref'] ?? null,
            ]);

            $provider = $this->repository->create([
                'tenant_id' => $tenant,
                'master_record_id' => $master->getKey(),
                'name' => $data['name'] ?? null,
                'type' => $data['type'] ?? null,
            ]);

            $this->audit->record(
                'md.master_record.created',
                $this->entityName(),
                (string) $provider->getKey(),
            );

            return $provider->load('masterRecord');
        });
    }

    protected function childModelsForPurge(): array
    {
        return [
            \App\Models\MasterData\ProviderIdentifier::class,
            \App\Models\MasterData\ProviderCredential::class,
            \App\Models\MasterData\ProviderNetwork::class,
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
