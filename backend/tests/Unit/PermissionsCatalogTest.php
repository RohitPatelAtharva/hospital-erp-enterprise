<?php

namespace Tests\Unit;

use App\Support\Permissions;
use App\Support\RoleRegistry;
use App\Support\Roles;
use Tests\TestCase;

class PermissionsCatalogTest extends TestCase
{
    public function test_permission_catalog_has_no_duplicates(): void
    {
        $all = Permissions::all();

        $this->assertSame(count($all), count(array_unique($all)));
    }

    public function test_permission_catalog_is_non_empty(): void
    {
        $this->assertNotEmpty(Permissions::all());
    }

    public function test_registry_admin_gets_elevated_permissions(): void
    {
        $permissions = RoleRegistry::permissionsForRoles([Roles::REGISTRY_ADMIN]);

        $this->assertContains(Permissions::PURGE_EXECUTE, $permissions);
        $this->assertContains(Permissions::MERGE_EXECUTE, $permissions);
        $this->assertContains(Permissions::DUPLICATES_REVIEW, $permissions);
    }

    public function test_registrar_does_not_get_elevated_permissions(): void
    {
        $permissions = RoleRegistry::permissionsForRoles([Roles::REGISTRAR]);

        $this->assertNotContains(Permissions::PURGE_EXECUTE, $permissions);
        $this->assertNotContains(Permissions::AUDIT_READ, $permissions);
        $this->assertNotContains(Permissions::MERGE_EXECUTE, $permissions);
    }
}
