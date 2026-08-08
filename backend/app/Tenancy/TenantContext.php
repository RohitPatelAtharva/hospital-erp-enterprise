<?php

declare(strict_types=1);

namespace App\Tenancy;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Holds the tenant context for the current request lifecycle.
 *
 * Tenant context is derived from the authenticated principal's facility scope
 * (see docs/09-MULTI-TENANCY.md) — never from a client-supplied value. It is
 * established by SetTenantContext middleware before any business operation runs
 * and is cleared at the end of the request.
 *
 * Business models scope every query through this context (see BaseModel).
 *
 * The active tenant is also pushed into the PostgreSQL session as the `app.tenant`
 * GUC so that database-level Row Level Security (see ADR-P2-001) enforces the same
 * boundary as the application layer. The value is reset when the context is cleared
 * so a reused connection can never inherit another request's tenant.
 */
final class TenantContext
{
    private static ?string $tenantId = null;

    private static ?string $facilityId = null;

    /** System/infrastructure context: no tenant scope. */
    public static function setContext(?string $tenantId, ?string $facilityId = null): void
    {
        static::$tenantId = $tenantId;
        static::$facilityId = $facilityId;
        static::applyDatabaseTenant();
    }

    public static function tenantId(): ?string
    {
        return static::$tenantId;
    }

    public static function facilityId(): ?string
    {
        return static::$facilityId;
    }

    public static function hasTenant(): bool
    {
        return static::$tenantId !== null;
    }

    /**
     * Returns the active tenant id, throwing when a tenant boundary is required
     * but none is established. Used by business code that MUST be tenant-scoped.
     */
    public static function requireTenant(): string
    {
        if (static::$tenantId === null) {
            throw new RuntimeException('No tenant context established for this request.');
        }

        return static::$tenantId;
    }

    public static function clear(): void
    {
        static::$tenantId = null;
        static::$facilityId = null;
        static::applyDatabaseTenant();
    }

    /**
     * Mirrors the current tenant onto the active PostgreSQL session (`app.tenant`),
     * which the database-level RLS policies read. A cleared context sets an empty
     * value so RLS fails closed (no row is visible) rather than leaking another
     * tenant's data across a reused connection.
     */
    private static function applyDatabaseTenant(): void
    {
        try {
            DB::statement("SELECT set_config('app.tenant', ?, false)", [static::$tenantId ?? '']);
        } catch (\Throwable $e) {
            // The store may be unavailable during bootstrap/migrations or in
            // non-database tests; the RLS context is (re)applied on the next
            // request connection. Never fail the application here.
        }
    }
}
