<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Batch 1 (Master Data) — schema fidelity tests.
 *
 * Verifies the 45 dependency-safe foundation tables exist with the canonical
 * columns, primary keys, foreign keys, unique constraints and indexes described
 * in docs/modules/master-data/04-Database-Tables.md. Runs on PostgreSQL.
 */
final class Batch1DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const TABLES = [
        'master_domain', 'entity_type', 'record_status',
        'country', 'region', 'city', 'postal_code',
        'clinical_code_set', 'clinical_code', 'clinical_vocabulary', 'clinical_mapping',
        'identity_type',
        'contact', 'contact_type', 'contact_use',
        'address', 'address_type',
        'document_type', 'document_storage',
        'language',
        'lookup', 'lookup_category', 'lookup_value', 'enum_definition',
        'match_rule',
        'survivorship_rule', 'attribute_priority',
        'reference_value', 'reference_category', 'reference_version',
        'consent_type', 'credential_type', 'relation_type',
        'terminology_service', 'terminology_edition', 'terminology_entry',
        'audit_reference', 'audit_action', 'audit_actor', 'audit_retention',
        'integration_endpoint',
        'xref_type',
        'metadata_catalog',
        'archive_table', 'archive_manifest',
    ];

    public function test_all_45_batch1_tables_exist(): void
    {
        $missing = array_values(array_filter(self::TABLES, fn (string $t) => ! Schema::hasTable($t)));

        $this->assertSame([], $missing, 'Missing Batch 1 tables.');
        $this->assertCount(45, self::TABLES);
    }

    public function test_universal_columns_present(): void
    {
        foreach (self::TABLES as $table) {
            $this->assertTrue(
                Schema::hasColumns($table, ['id', 'tenant_id']),
                "{$table} missing id/tenant_id"
            );
        }
    }

    public function test_entity_type_columns_match_canonical(): void
    {
        $this->assertTrue(Schema::hasColumns('entity_type', [
            'id', 'tenant_id', 'code', 'status',
            'created_at', 'updated_at', 'created_by', 'updated_by',
            'deleted_at', 'version',
        ]));
    }

    public function test_geographic_dependent_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('region', ['id', 'tenant_id', 'country_id', 'code']));
        $this->assertTrue(Schema::hasColumns('city', ['id', 'tenant_id', 'region_id', 'code']));
        $this->assertTrue(Schema::hasColumns('postal_code', ['id', 'tenant_id', 'city_id', 'code']));
    }

    public function test_clinical_code_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('clinical_code', [
            'id', 'tenant_id', 'clinical_code_set_id', 'code', 'edition', 'value',
            'status', 'deleted_at', 'version',
        ]));
    }

    public function test_reference_value_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('reference_value', [
            'id', 'tenant_id', 'reference_category_id', 'reference_version_id', 'code',
            'status', 'deleted_at', 'version',
        ]));
    }

    public function test_append_only_tables_have_no_soft_delete_or_version(): void
    {
        foreach (['audit_reference', 'audit_actor', 'archive_manifest', 'document_storage'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'deleted_at'), "{$table} should not be soft-deleted");
            $this->assertFalse(Schema::hasColumn($table, 'version'), "{$table} should not have a version column");
        }
    }

    public function test_audit_reference_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('audit_reference', [
            'id', 'tenant_id', 'event_id', 'audit_action_id', 'audit_actor_id',
            'entity', 'entity_id', 'occurred_at',
        ]));
    }

    public function test_primary_key_on_every_table(): void
    {
        $tables = implode("','", self::TABLES);
        $missing = DB::select(
            "SELECT conrelid::regclass AS tbl FROM pg_constraint
             WHERE contype='p' AND connamespace='public'::regnamespace
             AND conrelid::regclass::text IN ('{$tables}')"
        );
        $withPk = array_column($missing, 'tbl');

        $this->assertCount(45, $withPk, 'Every Batch 1 table must have a primary key.');
    }

    public function test_foreign_keys_exist_with_restrict(): void
    {
        // Scoped to Batch 1's own tables so the count stays meaningful and
        // stable as later batches (Batch 2A and beyond) add their own FKs.
        $tables = implode("','", self::TABLES);
        $rows = DB::select(
            "SELECT conrelid::regclass AS tbl, pg_get_constraintdef(oid) AS def
             FROM pg_constraint WHERE contype='f' AND connamespace='public'::regnamespace
             AND conrelid::regclass::text IN ('{$tables}')"
        );
        $this->assertCount(20, $rows, 'Expected 20 foreign keys in Batch 1.');

        foreach ($rows as $row) {
            $this->assertStringContainsString('ON DELETE RESTRICT', (string) $row->def);
        }
    }

    public function test_unique_constraints_exist(): void
    {
        $count = DB::selectOne(
            "SELECT count(*) AS c FROM pg_constraint
             WHERE contype='u' AND connamespace='public'::regnamespace"
        );
        $this->assertGreaterThanOrEqual(43, (int) $count->c);
    }

    public function test_documented_secondary_indexes_exist(): void
    {
        $indexes = [
            'ix_entity_type_code', 'ix_country_code',
            'ix_region_country', 'ix_city_region', 'ix_postal_code_city',
            'ix_clinical_code_value', 'ix_clinical_code_set', 'ix_clinical_mapping_source',
            'ix_contact_value', 'ix_address_postal',
            'ix_lookup_category', 'ix_lookup_value_category',
            'ix_reference_value_category', 'ix_reference_value_code',
            'ix_consent_type_code', 'ix_credential_type_code', 'ix_relation_type_code',
            'ix_terminology_edition_service', 'ix_terminology_entry_code', 'ix_terminology_entry_display',
            'ix_audit_reference_entity', 'ix_audit_reference_time', 'ix_audit_reference_actor',
            'ix_archive_manifest_time',
        ];
        $missing = array_values(array_filter($indexes, fn (string $i) => ! $this->indexExists($i)));

        $this->assertSame([], $missing, 'Missing documented secondary indexes.');
    }

    public function test_rls_is_forced_on_every_batch1_table(): void
    {
        $tables = implode("','", self::TABLES);
        $rows = DB::select(
            "SELECT c.relname AS tbl FROM pg_class c
             JOIN pg_namespace n ON n.oid = c.relnamespace
             WHERE n.nspname='public'
               AND c.relname IN ('{$tables}')
               AND (c.relrowsecurity = false OR c.relforcerowsecurity = false)"
        );
        $this->assertSame([], array_column($rows, 'tbl'), 'Every Batch 1 table must have RLS enabled and forced.');
    }

    private function indexExists(string $name): bool
    {
        return (bool) DB::selectOne(
            "SELECT 1 FROM pg_indexes WHERE schemaname='public' AND indexname = ?",
            [$name]
        );
    }
}
