<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Canonical permission catalog for the Master Data module.
 *
 * Mirrors docs/modules/master-data/11-Permissions.md §5 (resource:action form).
 * Permissions are data/gated here, never hard-coded inline in controllers —
 * authorization is enforced via Gate::authorize(Permissions::X, ...) so the
 * catalog remains a single source of truth.
 */
final class Permissions
{
    // Patient
    public const PATIENTS_READ = 'patients:read';
    public const PATIENTS_CREATE = 'patients:create';
    public const PATIENTS_UPDATE = 'patients:update';

    // Staff
    public const STAFF_READ = 'staff:read';
    public const STAFF_CREATE = 'staff:create';
    public const STAFF_UPDATE = 'staff:update';

    // Providers
    public const PROVIDERS_READ = 'providers:read';
    public const PROVIDERS_CREATE = 'providers:create';
    public const PROVIDERS_UPDATE = 'providers:update';

    // Organizations
    public const ORGANIZATIONS_READ = 'organizations:read';
    public const ORGANIZATIONS_CREATE = 'organizations:create';
    public const ORGANIZATIONS_UPDATE = 'organizations:update';

    // Reference data
    public const REFERENCE_READ = 'reference:read';
    public const REFERENCE_MANAGE = 'reference:manage';

    // Duplicates
    public const DUPLICATES_READ = 'duplicates:read';
    public const DUPLICATES_REVIEW = 'duplicates:review';

    // Golden records
    public const GOLDEN_READ = 'golden:read';
    public const GOLDEN_MANAGE = 'golden:manage';

    // Merge / unmerge
    public const MERGE_READ = 'merge:read';
    public const MERGE_EXECUTE = 'merge:execute';
    public const UNMERGE_EXECUTE = 'unmerge:execute';

    // Approvals
    public const APPROVAL_REVIEW = 'approval:review';

    // Stewardship
    public const STEWARDSHIP_MANAGE = 'stewardship:manage';

    // Import / export
    public const IMPORT_RUN = 'import:run';
    public const EXPORT_RUN = 'export:run';

    // Integrations
    public const INTEGRATION_MANAGE = 'integration:manage';

    // Registry / audit
    public const MASTERDATA_READ = 'masterdata:read';
    public const AUDIT_READ = 'audit:read';

    // Purge (governed, elevated)
    public const PURGE_EXECUTE = 'purge:execute';

    /** All canonical permissions. */
    public static function all(): array
    {
        return [
            self::PATIENTS_READ,
            self::PATIENTS_CREATE,
            self::PATIENTS_UPDATE,
            self::STAFF_READ,
            self::STAFF_CREATE,
            self::STAFF_UPDATE,
            self::PROVIDERS_READ,
            self::PROVIDERS_CREATE,
            self::PROVIDERS_UPDATE,
            self::ORGANIZATIONS_READ,
            self::ORGANIZATIONS_CREATE,
            self::ORGANIZATIONS_UPDATE,
            self::REFERENCE_READ,
            self::REFERENCE_MANAGE,
            self::DUPLICATES_READ,
            self::DUPLICATES_REVIEW,
            self::GOLDEN_READ,
            self::GOLDEN_MANAGE,
            self::MERGE_READ,
            self::MERGE_EXECUTE,
            self::UNMERGE_EXECUTE,
            self::APPROVAL_REVIEW,
            self::STEWARDSHIP_MANAGE,
            self::IMPORT_RUN,
            self::EXPORT_RUN,
            self::INTEGRATION_MANAGE,
            self::MASTERDATA_READ,
            self::AUDIT_READ,
            self::PURGE_EXECUTE,
        ];
    }

    /** Throws if the given permission is not part of the canonical catalog. */
    public static function assertValid(string $permission): void
    {
        if (! in_array($permission, self::all(), true)) {
            throw new InvalidArgumentException("Unknown permission [{$permission}].");
        }
    }
}
