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
 * Batch 2A (Master Data) — Row Level Security tests on the new entity tables.
 *
 * enterprise_person (tenant-only, no FKs) exercises the isolation policy on a
 * table without the complexity of parent rows; a chained insert proves the
 * entity FK wiring (entity_type -> master_record -> patient) works and is
 * tenant-scoped.
 */
final class Batch2aRlsTest extends TestCase
{
    use RefreshDatabase;

    private const T1 = '11111111-1111-1111-1111-111111111111';

    private const T2 = '22222222-2222-2222-2222-222222222222';

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_enterprise_person_same_tenant_read_works(): void
    {
        TenantContext::setContext(self::T1);

        DB::table('enterprise_person')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'name' => 'Ada Lovelace',
        ]);

        $this->assertSame(1, DB::table('enterprise_person')->where('name', 'Ada Lovelace')->count());
    }

    public function test_enterprise_person_cross_tenant_invisible(): void
    {
        TenantContext::setContext(self::T1);
        DB::table('enterprise_person')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'name' => 'Ada Lovelace',
        ]);

        TenantContext::setContext(self::T2);
        $this->assertSame(0, DB::table('enterprise_person')->where('name', 'Ada Lovelace')->count());
    }

    public function test_enterprise_person_missing_tenant_fails_closed(): void
    {
        TenantContext::setContext(self::T1);
        DB::table('enterprise_person')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'name' => 'Ada Lovelace',
        ]);

        TenantContext::clear();
        $this->assertSame(0, DB::table('enterprise_person')->count());
    }

    public function test_enterprise_person_cross_tenant_insert_rejected(): void
    {
        TenantContext::setContext(self::T1);

        $this->expectException(QueryException::class);

        DB::table('enterprise_person')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T2, 'name' => 'INTRUDER',
        ]);
    }

    public function test_chained_entity_insert_is_tenant_scoped_and_fk_wired(): void
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

        $personId = Str::uuid();
        DB::table('enterprise_person')->insert([
            'id' => $personId, 'tenant_id' => self::T1, 'name' => 'Grace Hopper',
        ]);

        $patientId = Str::uuid();
        DB::table('patient')->insert([
            'id' => $patientId, 'tenant_id' => self::T1,
            'master_record_id' => $masterRecordId,
            'enterprise_person_id' => $personId,
            'name' => 'Grace Hopper', 'dob' => '1906-12-09', 'sex' => 'female',
        ]);

        $this->assertSame(1, DB::table('patient')->where('id', $patientId)->count());

        // The parent master_record is invisible to tenant-2.
        TenantContext::setContext(self::T2);
        $this->assertSame(0, DB::table('master_record')->where('id', $masterRecordId)->count());
    }

    public function test_patient_rejects_orphan_master_record(): void
    {
        TenantContext::setContext(self::T1);
        $personId = Str::uuid();
        DB::table('enterprise_person')->insert([
            'id' => $personId, 'tenant_id' => self::T1, 'name' => 'Alan Turing',
        ]);

        $this->expectException(QueryException::class);

        // master_record_id references a row that does not exist.
        DB::table('patient')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1,
            'master_record_id' => Str::uuid(),
            'enterprise_person_id' => $personId,
        ]);
    }
}
