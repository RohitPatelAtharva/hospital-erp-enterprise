<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Batch 1 (Master Data) — Row Level Security tests.
 *
 * Verifies PostgreSQL RLS (via the `app.tenant` session setting wired through
 * TenantContext) enforces tenant isolation at the database layer: same-tenant
 * reads/writes work, cross-tenant and missing-tenant access fail closed.
 *
 * Runs as the md_app role, which owns the tables but is subject to
 * FORCE ROW LEVEL SECURITY, so ownership does not bypass the policy.
 */
final class Batch1RlsTest extends TestCase
{
    use RefreshDatabase;

    private const T1 = '11111111-1111-1111-1111-111111111111';
    private const T2 = '22222222-2222-2222-2222-222222222222';

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_same_tenant_insert_and_read_work(): void
    {
        TenantContext::setContext(self::T1);

        DB::table('entity_type')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'code' => 'PATIENT',
        ]);

        $count = DB::table('entity_type')->where('code', 'PATIENT')->count();

        $this->assertSame(1, $count);
    }

    public function test_cross_tenant_read_is_invisible(): void
    {
        TenantContext::setContext(self::T1);
        DB::table('entity_type')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'code' => 'PATIENT',
        ]);

        // A different tenant must not see tenant-1 rows.
        TenantContext::setContext(self::T2);
        $count = DB::table('entity_type')->where('code', 'PATIENT')->count();

        $this->assertSame(0, $count);
    }

    public function test_missing_tenant_fails_closed(): void
    {
        TenantContext::setContext(self::T1);
        DB::table('entity_type')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'code' => 'PATIENT',
        ]);

        TenantContext::clear();
        $count = DB::table('entity_type')->count();

        $this->assertSame(0, $count, 'Without a tenant context, no rows may be visible.');
    }

    public function test_cross_tenant_insert_is_rejected(): void
    {
        TenantContext::setContext(self::T1);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Attempt to insert a row owned by tenant-2 while acting as tenant-1.
        DB::table('entity_type')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T2, 'code' => 'INTRUDER',
        ]);
    }

    public function test_cross_tenant_update_is_a_noop(): void
    {
        TenantContext::setContext(self::T1);
        DB::table('entity_type')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'code' => 'PATIENT',
        ]);

        // Tenant-2 cannot see tenant-1 rows, so an update touching them matches 0 rows.
        TenantContext::setContext(self::T2);
        $affected = DB::table('entity_type')->where('tenant_id', self::T1)->update(['code' => 'HACKED']);

        $this->assertSame(0, $affected);

        // Tenant-1 data is unchanged.
        TenantContext::setContext(self::T1);
        $this->assertSame('PATIENT', DB::table('entity_type')->where('code', 'PATIENT')->value('code'));
    }

    public function test_update_reassigning_tenant_violates_check(): void
    {
        TenantContext::setContext(self::T2);
        $id = Str::uuid();
        DB::table('entity_type')->insert(['id' => $id, 'tenant_id' => self::T2, 'code' => 'STAFF']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Reassigning an owned row to another tenant violates the RLS WITH CHECK.
        DB::table('entity_type')->where('id', $id)->update(['tenant_id' => self::T1]);
    }

    public function test_tenant_isolation_holds_on_reference_value_with_fks(): void
    {
        TenantContext::setContext(self::T1);

        $categoryId = Str::uuid();
        $versionId = Str::uuid();
        DB::table('reference_category')->insert(['id' => $categoryId, 'tenant_id' => self::T1, 'code' => 'RELATION']);
        DB::table('reference_version')->insert(['id' => $versionId, 'tenant_id' => self::T1, 'code' => 'V1', 'version' => '2026']);
        DB::table('reference_value')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1,
            'reference_category_id' => $categoryId, 'reference_version_id' => $versionId,
            'code' => 'SPOUSE',
        ]);

        // Same tenant sees it.
        $this->assertSame(1, DB::table('reference_value')->where('code', 'SPOUSE')->count());

        // Cross tenant does not.
        TenantContext::setContext(self::T2);
        $this->assertSame(0, DB::table('reference_value')->where('code', 'SPOUSE')->count());
    }

    public function test_append_only_audit_reference_is_tenant_scoped(): void
    {
        TenantContext::setContext(self::T1);
        $actionId = Str::uuid();
        $actorId = Str::uuid();
        DB::table('audit_action')->insert(['id' => $actionId, 'tenant_id' => self::T1, 'code' => 'CREATE']);
        DB::table('audit_actor')->insert(['id' => $actorId, 'tenant_id' => self::T1, 'actor_key' => 'sys']);
        DB::table('audit_reference')->insert([
            'id' => Str::uuid(), 'tenant_id' => self::T1, 'event_id' => 'evt-1',
            'audit_action_id' => $actionId, 'audit_actor_id' => $actorId,
            'entity' => 'patient', 'occurred_at' => now(),
        ]);

        $this->assertSame(1, DB::table('audit_reference')->where('event_id', 'evt-1')->count());

        TenantContext::setContext(self::T2);
        $this->assertSame(0, DB::table('audit_reference')->where('event_id', 'evt-1')->count());
    }
}
