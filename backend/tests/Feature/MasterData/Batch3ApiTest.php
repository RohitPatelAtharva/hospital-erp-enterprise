<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use App\Models\User;
use App\Support\Roles;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Batch 3 (Master Data) — API surface feature tests.
 *
 * Exercises the documented read surface (10-API.md §7, §12) and the patient
 * create path against the live PostgreSQL schema + RLS:
 *  - registrar can create a patient and list it back (envelope + pagination);
 *  - cross-tenant isolation holds at the API layer (list hides another tenant);
 *  - unknown ids return 404; a role without patients:read returns 403;
 *  - a principal without an assigned facility scope is denied (default-deny);
 *  - enterprise-person index read is tenant-scoped.
 *
 * Runs on PostgreSQL (requires the RLS infrastructure).
 */
final class Batch3ApiTest extends TestCase
{
    use RefreshDatabase;

    private const T1 = '11111111-1111-1111-1111-111111111111';

    private const T2 = '22222222-2222-2222-2222-222222222222';

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    private function makeUser(string $tenantId, string $facilityId, array $roles): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'facility_id' => $facilityId,
            'roles' => $roles,
        ]);
    }

    private function seedPatientEntityType(string $tenantId): void
    {
        TenantContext::setContext($tenantId);

        DB::table('entity_type')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'code' => 'PATIENT',
        ]);
    }

    public function test_registrar_can_create_and_list_a_patient(): void
    {
        $this->seedPatientEntityType(self::T1);
        $user = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::REGISTRAR]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/patients', [
                'name' => 'Ada Lovelace',
                'dob' => '1815-12-10',
                'sex' => 'female',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Ada Lovelace')
            ->assertJsonPath('data.dob', '1815-12-10')
            ->assertJsonStructure(['data' => ['id', 'name', 'dob', 'sex']]);

        $this->withToken($token)
            ->getJson('/api/v1/patients')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Ada Lovelace')
            ->assertJsonStructure(['data', 'meta' => ['page', 'pageSize', 'total']]);
    }

    public function test_patient_list_hides_other_tenants(): void
    {
        $this->seedPatientEntityType(self::T1);
        $userA = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::REGISTRAR]);
        $tokenA = $userA->createToken('test')->plainTextToken;

        $this->withToken($tokenA)
            ->postJson('/api/v1/patients', ['name' => 'Grace Hopper'])
            ->assertStatus(201);

        // A registrar in tenant-2 must not see tenant-1's patient.
        // Drop the auth guard so this request authenticates as userB, not the
        // userA cached by the shared test container.
        $this->app['auth']->forgetGuards();

        $userB = $this->makeUser(self::T2, (string) Str::uuid(), [Roles::REGISTRAR]);
        $tokenB = $userB->createToken('test')->plainTextToken;

        $this->withToken($tokenB)
            ->getJson('/api/v1/patients')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_unknown_patient_returns_404(): void
    {
        $user = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::REGISTRAR]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/patients/' . Str::uuid())
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_role_without_patients_read_gets_403(): void
    {
        // Auditor has no patients:read (11-Permissions.md §6), unlike Approver.
        $user = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::AUDITOR]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/patients')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_principal_without_facility_scope_is_denied(): void
    {
        $user = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::REGISTRAR]);
        $user->forceFill(['facility_id' => null])->save();

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/patients')
            ->assertStatus(403);
    }

    public function test_enterprise_person_index_is_tenant_scoped_and_readable(): void
    {
        TenantContext::setContext(self::T1);
        $personId = (string) Str::uuid();
        DB::table('enterprise_person')->insert([
            'id' => $personId,
            'tenant_id' => self::T1,
            'name' => 'Alan Turing',
        ]);

        $user = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::REGISTRAR]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/enterprise-persons')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $personId);

        // A different tenant sees nothing.
        $this->app['auth']->forgetGuards();

        $userB = $this->makeUser(self::T2, (string) Str::uuid(), [Roles::REGISTRAR]);
        $tokenB = $userB->createToken('test')->plainTextToken;

        $this->withToken($tokenB)
            ->getJson('/api/v1/enterprise-persons')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_reference_values_are_readable_by_reference_reader(): void
    {
        TenantContext::setContext(self::T1);
        $categoryId = (string) Str::uuid();
        DB::table('reference_category')->insert([
            'id' => $categoryId,
            'tenant_id' => self::T1,
            'code' => 'SPECIALTY',
        ]);
        // reference_value.reference_version_id is NOT NULL, so a version row is
        // required before the value can be inserted.
        $versionId = (string) Str::uuid();
        DB::table('reference_version')->insert([
            'id' => $versionId,
            'tenant_id' => self::T1,
            'code' => 'SPECIALTY',
            'version' => 'v1',
        ]);
        DB::table('reference_value')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => self::T1,
            'reference_category_id' => $categoryId,
            'reference_version_id' => $versionId,
            'code' => 'CARDIOLOGY',
        ]);

        $user = $this->makeUser(self::T1, (string) Str::uuid(), [Roles::REGISTRAR]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/reference-values')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'CARDIOLOGY');
    }
}
