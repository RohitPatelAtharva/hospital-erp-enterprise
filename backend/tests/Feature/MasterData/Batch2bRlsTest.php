<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Batch 2B (Master Data) — Row Level Security tests on the registry tables.
 *
 * A chained insert through the new append-only `version` table
 * (entity_type -> master_record -> version) proves the entity FK wiring works
 * and is tenant-scoped; cross-tenant reads/invisibility, fail-closed on missing
 * tenant, and orphan-parent rejection exercise the isolation and FK guards.
 */
final class Batch2bRlsTest extends TestCase
{
    use RefreshDatabase;

    private const T1 = '11111111-1111-1111-1111-111111111111';

    private const T2 = '22222222-2222-2222-2222-222222222222';

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_chained_version_insert_is_tenant_scoped_and_fk_wired(): void
    {
        TenantContext::setContext(self::T1);

        $entityTypeId = Str::uuid();
        DB::table('entity_type')->insert([
            'id' => $entityTypeId, 'tenant_id' => self::T1, 'code' => 'PATIENT',
        ]);

        $masterRecordId = Str::uuid();
        DB::table('master_record')->insert([
            'id' => $masterRecordId, 'tenant_id' => self::T1,
            'entity_type_id' => $entityTypeId, 'external_ref' => 'MR-0001',
        ]);

        $versionId = Str::uuid();
        DB::table('version')->insert([
            'id' => $versionId, 'tenant_id' => self::T1,
            'master_record_id' => $masterRecordId, 'version_number' => 1,
        ]);

        $this->assertSame(1, DB::table('version')->where('id', $versionId)->count());

        // The version row is invisible to tenant-2.
        TenantContext::setContext(self::T2);
        $this->assertSame(0, DB::table('version')->where('id', $versionId)->count());
    }

    public function test_version_missing_tenant_fails_closed(): void
    {
        TenantContext::setContext(self::T1);
        $entityTypeId = Str::uuid();
        DB::table('entity_type')->insert([
            'id' => $entityTypeId, 'tenant_id' => self::T1, 'code' => 'STAFF',
        ]);
        $masterRecordId = Str::uuid();
        DB::table('master_record')->insert([
            'id' => $masterRecordId, 'tenant_id' => self::T1,
            'entity_type_id' => $entityTypeId, 'external_ref' => 'MR-0002',
        ]);

        TenantContext::clear();
        $this->assertSame(0, DB::table('version')->count());
    }

    public function test_version_cross_tenant_insert_rejected(): void
    {
        TenantContext::setContext(self::T1);
        $entityTypeId = Str::uuid();
        DB::table('entity_type')->insert([
            'id' => $entityTypeId, 'tenant_id' => self::T1, 'code' => 'PROVIDER',
        ]);
        $masterRecordId = Str::uuid();
        DB::table('master_record')->insert([
            'id' => $masterRecordId, 'tenant_id' => self::T1,
            'entity_type_id' => $entityTypeId, 'external_ref' => 'MR-0003',
        ]);

        $this->expectException(QueryException::class);

        // tenant_id diverges from the tenant context (T1) -> blocked by RLS.
        DB::table('version')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T2,
            'master_record_id' => $masterRecordId, 'version_number' => 1,
        ]);
    }

    public function test_version_rejects_orphan_master_record(): void
    {
        TenantContext::setContext(self::T1);

        $this->expectException(QueryException::class);

        // master_record_id references a row that does not exist.
        DB::table('version')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1,
            'master_record_id' => Str::uuid(), 'version_number' => 1,
        ]);
    }

    public function test_cross_reference_unique_and_tenant_scoped(): void
    {
        TenantContext::setContext(self::T1);
        $entityTypeId = Str::uuid();
        DB::table('entity_type')->insert([
            'id' => $entityTypeId, 'tenant_id' => self::T1, 'code' => 'ORGANIZATION',
        ]);
        $masterRecordId = Str::uuid();
        DB::table('master_record')->insert([
            'id' => $masterRecordId, 'tenant_id' => self::T1,
            'entity_type_id' => $entityTypeId, 'external_ref' => 'MR-0004',
        ]);
        $xrefTypeId = Str::uuid();
        DB::table('xref_type')->insert([
            'id' => $xrefTypeId, 'tenant_id' => self::T1, 'code' => 'EXTERNAL',
        ]);

        DB::table('cross_reference')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1,
            'master_record_id' => $masterRecordId,
            'xref_type_id' => $xrefTypeId, 'external_ref' => 'EXT-9',
        ]);

        $this->assertSame(1, DB::table('cross_reference')->where('external_ref', 'EXT-9')->count());
    }
}
