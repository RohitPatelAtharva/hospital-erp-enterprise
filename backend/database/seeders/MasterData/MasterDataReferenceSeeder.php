<?php

declare(strict_types=1);

namespace Database\Seeders\MasterData;

use App\Tenancy\TenantContext;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Governed reference / controlled-vocabulary rows for the Master Data module.
 *
 * Seeds the canonical type/catalog tables documented in
 * docs/modules/master-data/04-Database-Tables.md §24 and §5. These are
 * tenant-scoped reference rows (entity_type, master_domain, record_status,
 * identity_type, consent_type, credential_type, relation_type,
 * reference_category) owned by the Data Governance Board.
 *
 * Run within an explicit tenant scope so RLS FORCE permits the writes.
 */
final class MasterDataReferenceSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array<string, list<string>> Table -> governed code set. */
    private const REFERENCE_CODES = [
        'entity_type' => [
            'patient', 'staff', 'provider', 'organization',
        ],
        'master_domain' => [
            'patient', 'staff', 'provider', 'organization', 'reference',
        ],
        'record_status' => [
            'draft', 'active', 'inactive', 'archived', 'purged',
        ],
        'identity_type' => [
            'mrn', 'national_id', 'insurance', 'npi', 'employee_id',
        ],
        'consent_type' => [
            'treatment', 'disclosure', 'research', 'marketing',
        ],
        'credential_type' => [
            'license', 'certification', 'registration',
        ],
        'relation_type' => [
            'next_of_kin', 'guarantor', 'subsidiary',
        ],
        'reference_category' => [
            'identifier_type', 'relation_type', 'consent_type', 'credential_type',
        ],
    ];

    public function run(string $tenantId): void
    {
        $previous = TenantContext::tenantId();

        try {
            TenantContext::setContext($tenantId);

            foreach (self::REFERENCE_CODES as $table => $codes) {
                $this->seedCodes($table, $codes, $tenantId);
            }
        } finally {
            TenantContext::setContext($previous);
        }
    }

    /**
     * Insert any governed codes for a reference table not already present.
     *
     * @param  list<string>  $codes
     */
    private function seedCodes(string $table, array $codes, string $tenantId): void
    {
        $existing = DB::table($table)
            ->where('tenant_id', $tenantId)
            ->pluck('code')
            ->flip();

        foreach ($codes as $code) {
            if ($existing->has($code)) {
                continue;
            }

            DB::table($table)->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'code' => $code,
                'status' => 'active',
                'version' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
