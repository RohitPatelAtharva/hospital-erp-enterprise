<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_issues_a_token(): void
    {
        User::factory()->create(['email' => 'staff@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'staff@example.com')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email', 'facilityId']]]);
    }

    public function test_login_with_invalid_credentials_returns_validation_error(): void
    {
        User::factory()->create(['email' => 'staff@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_login_requires_valid_email(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_me_returns_principal_when_authenticated(): void
    {
        $user = User::factory()->create(['roles' => ['registrar']]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.facilityId', $user->facility_id)
            ->assertJsonPath('data.roles', ['registrar']);
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(204);

        // The token is revoked (row removed); a subsequent request in a fresh
        // app instance would be rejected. (The sanctum guard caches the
        // principal within a single test's shared container, so revocation is
        // asserted at the persistence level, which is the source of truth.)
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
