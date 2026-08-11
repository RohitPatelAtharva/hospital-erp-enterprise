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
 * Batch 3 — lifecycle, aggregate sub-resources, search, and versioning.
 *
 * Exercises 10-API.md §7-§12, §18 against the live schema + RLS: PATCH, the
 * lifecycle state machine, identifier rotation, aggregate child resources,
 * tenant-scoped search, and version list/detail/diff.
 */
final class Batch3LifecycleTest extends TestCase
{
    use RefreshDatabase;

    private const T1 = '11111111-1111-1111-1111-111111111111';

    private const T2 = '22222222-2222-2222-2222-222222222222';

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    private function makeUser(string $tenantId, array $roles): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'facility_id' => (string) Str::uuid(),
            'roles' => $roles,
        ]);
    }

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    private function seedVocabulary(string $tenantId): void
    {
        TenantContext::setContext($tenantId);

        foreach (['PATIENT', 'STAFF', 'PROVIDER', 'ORGANIZATION'] as $code) {
            DB::table('entity_type')->insertOrIgnore([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'code' => $code,
            ]);
        }

        DB::table('identity_type')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'code' => 'NATIONAL_ID',
        ]);
    }

    private function createPatient(string $token): string
    {
        $this->withToken($token)
            ->postJson('/api/v1/patients', ['name' => 'Ada Lovelace', 'dob' => '1815-12-10'])
            ->assertStatus(201);

        return (string) $this->withToken($token)
            ->getJson('/api/v1/patients?page=1')
            ->json('data.0.id');
    }

    // --- Lifecycle --------------------------------------------------------

    public function test_registrar_can_patch_update_patient(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $id = $this->createPatient($token);

        $this->withToken($token)
            ->patchJson('/api/v1/patients/'.$id, ['name' => 'Ada King'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada King');
    }

    public function test_patient_deactivate_reactivate(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $id = $this->createPatient($token);

        $this->withToken($token)->postJson("/api/v1/patients/$id/deactivate")
            ->assertOk()->assertJsonPath('data.status', 'inactive');

        $this->withToken($token)->postJson("/api/v1/patients/$id/reactivate")
            ->assertOk()->assertJsonPath('data.status', 'active');
    }

    public function test_patient_archive_restore(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $id = $this->createPatient($token);

        $this->withToken($token)->postJson("/api/v1/patients/$id/archive")
            ->assertOk()->assertJsonPath('data.status', 'archived');

        $this->withToken($token)->postJson("/api/v1/patients/$id/restore")
            ->assertOk()->assertJsonPath('data.status', 'active');
    }

    public function test_purge_requires_elevated_permission(): void
    {
        $this->seedVocabulary(self::T1);
        $registrar = $this->makeUser(self::T1, [Roles::REGISTRAR]);
        $id = $this->createPatient($this->token($registrar));

        // Registrar lacks purge:execute -> 403.
        $this->withToken($this->token($registrar))->postJson("/api/v1/patients/$id/purge")
            ->assertStatus(403);

        // Registry admin purges -> 204, then the patient is gone.
        $this->app['auth']->forgetGuards();
        $admin = $this->makeUser(self::T1, [Roles::REGISTRY_ADMIN]);
        $this->withToken($this->token($admin))->postJson("/api/v1/patients/$id/purge")
            ->assertStatus(204);

        $this->withToken($this->token($admin))->getJson("/api/v1/patients/$id")
            ->assertStatus(404);
    }

    public function test_lifecycle_transition_is_404_for_unknown(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));

        $this->withToken($token)->postJson('/api/v1/patients/'.Str::uuid().'/deactivate')
            ->assertStatus(404);
    }

    public function test_lifecycle_requires_update_permission(): void
    {
        $this->seedVocabulary(self::T1);
        $registrar = $this->makeUser(self::T1, [Roles::REGISTRAR]);
        $id = $this->createPatient($this->token($registrar));

        // A user with only read access cannot deactivate.
        $this->app['auth']->forgetGuards();
        $reader = $this->makeUser(self::T1, [Roles::APPROVER]);
        $this->withToken($this->token($reader))->postJson("/api/v1/patients/$id/deactivate")
            ->assertStatus(403);
    }

    // --- Aggregate sub-resources -----------------------------------------

    public function test_identifier_create_list_rotate(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $id = $this->createPatient($token);

        TenantContext::setContext(self::T1);
        $identityTypeId = (string) DB::table('identity_type')->where('tenant_id', self::T1)->value('id');

        $this->withToken($token)->postJson("/api/v1/patients/$id/identifiers", [
            'identity_type_id' => $identityTypeId,
            'value' => 'EMP-001',
        ])->assertStatus(201)->assertJsonPath('data.value', 'EMP-001');

        $identifierId = (string) $this->withToken($token)
            ->getJson("/api/v1/patients/$id/identifiers")
            ->json('data.0.id');

        // Rotate: deactivates the old row and creates a replacement.
        $this->withToken($token)->postJson("/api/v1/patients/$id/identifiers/$identifierId/rotate", [
            'value' => 'EMP-002',
        ])->assertOk();

        $identifiers = $this->withToken($token)->getJson("/api/v1/patients/$id/identifiers")->json('data');
        $this->assertCount(2, $identifiers);
    }

    public function test_consent_create_and_list(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $id = $this->createPatient($token);

        $consentTypeId = (string) Str::uuid();
        TenantContext::setContext(self::T1);
        DB::table('consent_type')->insertOrIgnore([
            'id' => $consentTypeId,
            'tenant_id' => self::T1,
            'code' => 'SURGERY',
        ]);

        $this->withToken($token)->postJson("/api/v1/patients/$id/consents", [
            'consent_type_id' => $consentTypeId,
        ])->assertStatus(201);

        $this->withToken($token)->getJson("/api/v1/patients/$id/consents")->assertOk();
    }

    public function test_child_resource_validation_failure(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $id = $this->createPatient($token);

        $this->withToken($token)->postJson("/api/v1/patients/$id/identifiers", [])
            ->assertStatus(422);
    }

    // --- Search (10-API §12) ---------------------------------------------

    public function test_patient_search_is_tenant_scoped(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $this->createPatient($token);

        $this->withToken($token)->getJson('/api/v1/search/patients?q=Ada')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Ada Lovelace');

        // Tenant-2 must not see tenant-1's patient.
        $this->app['auth']->forgetGuards();
        $other = $this->token($this->makeUser(self::T2, [Roles::REGISTRAR]));
        $this->withToken($other)->getJson('/api/v1/search/patients?q=Ada')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    // --- Versioning (10-API §18) -----------------------------------------

    public function test_version_list_detail_diff(): void
    {
        $this->seedVocabulary(self::T1);
        $token = $this->token($this->makeUser(self::T1, [Roles::REGISTRAR]));
        $this->createPatient($token);

        // Re-establish the tenant context (the last HTTP request cleared it).
        TenantContext::setContext(self::T1);

        $masterRecordId = (string) DB::table('master_record')->where('tenant_id', self::T1)->value('id');

        foreach ([1, 2] as $n) {
            DB::table('version')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => self::T1,
                'master_record_id' => $masterRecordId,
                'version_number' => $n,
                'occurred_at' => now(),
            ]);
        }

        $this->withToken($token)->getJson("/api/v1/master-records/$masterRecordId/versions")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        // Re-establish the tenant context (the list request cleared it) before
        // the RLS-protected lookup.
        TenantContext::setContext(self::T1);
        $versionId = (string) DB::table('version')->where('version_number', 2)->value('id');

        $this->withToken($token)->getJson("/api/v1/master-records/$masterRecordId/versions/$versionId")
            ->assertOk()->assertJsonPath('data.version_number', 2);

        $this->withToken($token)->getJson("/api/v1/master-records/$masterRecordId/versions/$versionId/diff")
            ->assertOk()->assertJsonPath('data.delta.type', 'revision');
    }
}
