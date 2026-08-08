<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL Row Level Security helper for tenant-scoped Master Data tables.
 *
 * Tenancy is enforced at the database layer (defense in depth over the
 * application layer) via the `app.tenant` session setting. See
 * docs/adr/ADR-P2-001-MasterData-Architecture-Lock.md and
 * docs/09-MULTI-TENANCY.md.
 *
 * The policy predicate is:
 *     tenant_id = NULLIF(current_setting('app.tenant', true), '')::uuid
 *
 * - When `app.tenant` is unset or empty, NULLIF yields NULL, so no row matches
 *   (fail closed) — a missing or wrong tenant never exposes another tenant's rows.
 * - `FORCE ROW LEVEL SECURITY` applies the policy to the table owner too, so a
 *   single application role cannot bypass isolation by ownership.
 */
final class RowLevelSecurity
{
    private const POLICY_PREFIX = 'p_';

    /**
     * Enable RLS on a tenant-scoped table: create the isolation policy and force it.
     */
    public static function enable(string $table): void
    {
        $predicate = "tenant_id = NULLIF(current_setting('app.tenant', true), '')::uuid";
        $policy = self::POLICY_PREFIX.$table.'_tenant_isolation';

        DB::statement(sprintf('ALTER TABLE %s ENABLE ROW LEVEL SECURITY', self::quote($table)));
        DB::statement(sprintf(
            'CREATE POLICY %s ON %s USING (%s) WITH CHECK (%s)',
            self::quote($policy),
            self::quote($table),
            $predicate,
            $predicate
        ));
        DB::statement(sprintf('ALTER TABLE %s FORCE ROW LEVEL SECURITY', self::quote($table)));
    }

    /**
     * Remove RLS from a table (rollback path): drop the policy and disable.
     */
    public static function disable(string $table): void
    {
        $policy = self::POLICY_PREFIX.$table.'_tenant_isolation';

        DB::statement(sprintf('DROP POLICY IF EXISTS %s ON %s', self::quote($policy), self::quote($table)));
        DB::statement(sprintf('ALTER TABLE %s DISABLE ROW LEVEL SECURITY', self::quote($table)));
    }

    private static function quote(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
