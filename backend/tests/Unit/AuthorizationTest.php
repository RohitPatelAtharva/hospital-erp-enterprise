<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_principal_with_permission_is_authorized(): void
    {
        $user = User::factory()->withRoles(['registry_admin'])->create();
        Auth::login($user);

        $this->assertTrue(Gate::allows(Permissions::PURGE_EXECUTE));
        $this->assertTrue(Gate::allows(Permissions::PATIENTS_CREATE));
        $this->assertTrue(Gate::allows(Permissions::AUDIT_READ) === false); // registry admin has no audit read
    }

    public function test_principal_without_permission_is_denied(): void
    {
        $user = User::factory()->withRoles(['auditor'])->create();
        Auth::login($user);

        $this->assertFalse(Gate::allows(Permissions::PURGE_EXECUTE));
        $this->assertFalse(Gate::allows(Permissions::PATIENTS_CREATE));
        $this->assertTrue(Gate::allows(Permissions::AUDIT_READ));
    }

    public function test_authorize_throws_when_permission_is_missing(): void
    {
        $user = User::factory()->withRoles(['auditor'])->create();
        Auth::login($user);

        $this->expectException(AuthorizationException::class);

        Gate::authorize(Permissions::PURGE_EXECUTE);
    }
}
