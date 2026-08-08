<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Baseline role → permission matrix.
 *
 * Mirrors docs/modules/master-data/11-Permissions.md §6. This is configuration
 * data (single source of truth for the foundation); the authoritative matrix is
 * finalized at the IAM gate and may be moved to the database when roles are
 * data-defined in a later phase.
 */
final class RoleRegistry
{
    private const MATRIX = [
        Roles::REGISTRAR => [
            Permissions::PATIENTS_READ, Permissions::PATIENTS_CREATE, Permissions::PATIENTS_UPDATE,
            Permissions::STAFF_READ, Permissions::STAFF_CREATE, Permissions::STAFF_UPDATE,
            Permissions::PROVIDERS_READ, Permissions::PROVIDERS_CREATE, Permissions::PROVIDERS_UPDATE,
            Permissions::ORGANIZATIONS_READ, Permissions::ORGANIZATIONS_CREATE, Permissions::ORGANIZATIONS_UPDATE,
            Permissions::REFERENCE_READ,
            Permissions::DUPLICATES_READ,
            Permissions::GOLDEN_READ,
            Permissions::MASTERDATA_READ,
        ],
        Roles::REGISTRY_ADMIN => [
            Permissions::PATIENTS_READ, Permissions::PATIENTS_CREATE, Permissions::PATIENTS_UPDATE,
            Permissions::STAFF_READ, Permissions::STAFF_CREATE, Permissions::STAFF_UPDATE,
            Permissions::PROVIDERS_READ, Permissions::PROVIDERS_CREATE, Permissions::PROVIDERS_UPDATE,
            Permissions::ORGANIZATIONS_READ, Permissions::ORGANIZATIONS_CREATE, Permissions::ORGANIZATIONS_UPDATE,
            Permissions::REFERENCE_READ, Permissions::REFERENCE_MANAGE,
            Permissions::DUPLICATES_READ, Permissions::DUPLICATES_REVIEW,
            Permissions::GOLDEN_READ, Permissions::GOLDEN_MANAGE,
            Permissions::MERGE_READ, Permissions::MERGE_EXECUTE, Permissions::UNMERGE_EXECUTE,
            Permissions::STEWARDSHIP_MANAGE,
            Permissions::IMPORT_RUN, Permissions::EXPORT_RUN,
            Permissions::MASTERDATA_READ,
            Permissions::PURGE_EXECUTE,
        ],
        Roles::DATA_STEWARD => [
            Permissions::PATIENTS_READ, Permissions::PATIENTS_UPDATE,
            Permissions::STAFF_READ, Permissions::STAFF_UPDATE,
            Permissions::PROVIDERS_READ, Permissions::PROVIDERS_UPDATE,
            Permissions::ORGANIZATIONS_READ, Permissions::ORGANIZATIONS_UPDATE,
            Permissions::REFERENCE_READ, Permissions::REFERENCE_MANAGE,
            Permissions::DUPLICATES_READ, Permissions::DUPLICATES_REVIEW,
            Permissions::GOLDEN_READ, Permissions::GOLDEN_MANAGE,
            Permissions::MERGE_READ,
            Permissions::STEWARDSHIP_MANAGE,
            Permissions::IMPORT_RUN, Permissions::EXPORT_RUN,
            Permissions::MASTERDATA_READ,
        ],
        Roles::APPROVER => [
            Permissions::PATIENTS_READ,
            Permissions::STAFF_READ,
            Permissions::PROVIDERS_READ,
            Permissions::ORGANIZATIONS_READ,
            Permissions::REFERENCE_READ,
            Permissions::DUPLICATES_READ,
            Permissions::GOLDEN_READ,
            Permissions::MERGE_READ,
            Permissions::APPROVAL_REVIEW,
            Permissions::MASTERDATA_READ,
        ],
        Roles::FINANCE_OPS => [
            Permissions::STAFF_READ,
            Permissions::PROVIDERS_READ,
            Permissions::ORGANIZATIONS_READ,
            Permissions::REFERENCE_READ,
            Permissions::EXPORT_RUN,
            Permissions::MASTERDATA_READ,
        ],
        Roles::INTEGRATION_OWNER => [
            Permissions::PROVIDERS_READ,
            Permissions::ORGANIZATIONS_READ,
            Permissions::REFERENCE_READ,
            Permissions::IMPORT_RUN,
            Permissions::EXPORT_RUN,
            Permissions::INTEGRATION_MANAGE,
            Permissions::MASTERDATA_READ,
        ],
        Roles::AUDITOR => [
            Permissions::MASTERDATA_READ,
            Permissions::AUDIT_READ,
        ],
        Roles::EXECUTIVE => [
            Permissions::PATIENTS_READ,
            Permissions::STAFF_READ,
            Permissions::PROVIDERS_READ,
            Permissions::ORGANIZATIONS_READ,
            Permissions::REFERENCE_READ,
            Permissions::GOLDEN_READ,
            Permissions::MASTERDATA_READ,
        ],
    ];

    /** Permissions granted by the given roles (union). */
    public static function permissionsForRoles(array $roles): array
    {
        $permissions = [];

        foreach ($roles as $role) {
            Roles::assertValid($role);

            foreach (self::MATRIX[$role] as $permission) {
                $permissions[$permission] = true;
            }
        }

        return array_keys($permissions);
    }
}
