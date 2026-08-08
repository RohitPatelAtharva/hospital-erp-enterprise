<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Canonical role slugs for the Master Data module.
 *
 * Mirrors docs/modules/master-data/11-Permissions.md §4. Roles aggregate
 * permissions (see RoleRegistry); a principal's effective access is the union
 * over its roles intersected with its facility/tenant scope.
 */
final class Roles
{
    public const REGISTRAR = 'registrar';
    public const REGISTRY_ADMIN = 'registry_admin';
    public const DATA_STEWARD = 'data_steward';
    public const APPROVER = 'approver';
    public const FINANCE_OPS = 'finance_ops';
    public const INTEGRATION_OWNER = 'integration_owner';
    public const AUDITOR = 'auditor';
    public const EXECUTIVE = 'executive';

    public static function all(): array
    {
        return [
            self::REGISTRAR,
            self::REGISTRY_ADMIN,
            self::DATA_STEWARD,
            self::APPROVER,
            self::FINANCE_OPS,
            self::INTEGRATION_OWNER,
            self::AUDITOR,
            self::EXECUTIVE,
        ];
    }

    public static function assertValid(string $role): void
    {
        if (! in_array($role, self::all(), true)) {
            throw new InvalidArgumentException("Unknown role [{$role}].");
        }
    }
}
