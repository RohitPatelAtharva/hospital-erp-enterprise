<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Batch 2B (Master Data) — schema fidelity tests.
 *
 * Verifies the 30 registry tables described in 04-Database-Tables.md §19-§32
 * exist with their canonical columns, that append-only tables omit the
 * lifecycle status/version/soft-delete columns, and that documented unique
 * constraints and secondary indexes are present with RESTRICT FKs. Runs on
 * PostgreSQL.
 */
final class Batch2bDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /** Append-only tables (Soft Delete: No, Versioned: No). */
    private const APPEND_ONLY = [
        'golden_record_audit',
        'merge_event',
        'merge_record',
        'merge_approval',
        'survivorship_decision',
        'stewardship_log',
        'match_score',
        'duplicate_review',
        'import_validation',
        'export_recipient',
        'xref_resolution',
        'version_snapshot',
        'version_audit',
        'version',
    ];

    /** Append-only tables that retain a documented status column. */
    private const APPEND_ONLY_WITH_STATUS = [
        'import_batch',
        'export_batch',
        'import_staging_row',
        'export_queue_item',
    ];

    /** Stateful tables (Soft Delete: Yes, Versioned: Yes). */
    private const STATEFUL = [
        'duplicate_candidate',
        'match_threshold',
        'golden_record_link',
        'golden_record_source',
        'steward_assignment',
        'quality_issue',
        'remediation_task',
        'integration_map',
        'mapping_field',
        'cross_reference',
        'schema_metadata',
        'data_dictionary',
    ];

    private const TABLES = [
        // §19 Duplicate Detection
        'duplicate_candidate', 'match_score', 'match_threshold', 'duplicate_review',
        // §20 Golden Record
        'golden_record_link', 'golden_record_source', 'golden_record_audit',
        // §21 Merge History
        'merge_event', 'merge_record', 'merge_approval',
        // §22 Survivorship
        'survivorship_decision',
        // §23 Data Stewardship
        'steward_assignment', 'quality_issue', 'remediation_task', 'stewardship_log',
        // §27 Import
        'import_batch', 'import_staging_row', 'import_validation',
        // §28 Export
        'export_batch', 'export_queue_item', 'export_recipient',
        // §29 Integration Mapping
        'integration_map', 'mapping_field',
        // §30 Cross Reference
        'cross_reference', 'xref_resolution',
        // §31 Metadata
        'schema_metadata', 'data_dictionary',
        // §32 Version
        'version', 'version_snapshot', 'version_audit',
    ];

    private const UNIQUES = [
        'duplicate_candidate' => 'uq_duplicate_pair',
        'match_threshold' => 'uq_match_threshold_rule',
        'golden_record_link' => 'uq_golden_link_pair',
        'duplicate_review' => 'uq_duplicate_review_candidate',
        'merge_approval' => 'uq_merge_approval_event',
        'import_batch' => 'uq_import_batch_ref',
        'import_validation' => 'uq_import_validation_row',
        'export_batch' => 'uq_export_batch_ref',
        'cross_reference' => 'uq_cross_reference_external',
        'data_dictionary' => 'uq_data_dictionary_term',
        'xref_resolution' => 'uq_xref_resolution_ref',
        'version' => 'uq_version_record_number',
        'version_snapshot' => 'uq_version_snapshot_version',
        'version_audit' => 'uq_version_audit_version',
    ];

    private const INDEXES = [
        'ix_duplicate_master', 'ix_duplicate_status',
        'ix_match_score_candidate', 'ix_match_score_value',
        'ix_duplicate_review_actor',
        'ix_golden_link_golden', 'ix_golden_link_master',
        'ix_golden_source_link', 'ix_golden_audit_golden',
        'ix_merge_event_golden', 'ix_merge_event_time',
        'ix_merge_record_event', 'ix_merge_record_master',
        'ix_survivorship_event', 'ix_survivorship_rule',
        'ix_steward_assignment_domain',
        'ix_quality_issue_master', 'ix_quality_issue_severity',
        'ix_remediation_issue', 'ix_remediation_assignee',
        'ix_stewardship_log_actor', 'ix_stewardship_log_time',
        'ix_import_batch_status', 'ix_import_batch_time',
        'ix_import_row_batch', 'ix_import_row_status', 'ix_import_validation_row',
        'ix_export_batch_status', 'ix_export_item_batch', 'ix_export_item_status',
        'ix_export_recipient_batch',
        'ix_integration_map_endpoint', 'ix_mapping_field_map',
        'ix_cross_reference_master', 'ix_cross_reference_external',
        'ix_schema_metadata_catalog',
        'ix_version_master', 'ix_version_time', 'ix_version_snapshot_version',
    ];

    public function test_all_30_batch2b_tables_exist(): void
    {
        $missing = array_values(array_filter(self::TABLES, fn (string $t) => ! Schema::hasTable($t)));

        $this->assertSame([], $missing, 'Missing Batch 2B tables.');
        $this->assertCount(30, self::TABLES);
    }

    public function test_stateful_tables_have_lifecycle_columns(): void
    {
        foreach (self::STATEFUL as $table) {
            $this->assertTrue(
                Schema::hasColumns($table, ['status', 'deleted_at', 'version']),
                "{$table} must carry status/deleted_at/version (stateful)."
            );
        }
    }

    public function test_append_only_tables_omit_lifecycle_columns(): void
    {
        foreach (self::APPEND_ONLY as $table) {
            foreach (['status', 'deleted_at', 'version'] as $column) {
                $this->assertFalse(
                    Schema::hasColumn($table, $column),
                    "{$table} must NOT have {$column} (append-only)."
                );
            }
        }
    }

    public function test_import_export_append_only_keep_status_but_not_softdelete_version(): void
    {
        foreach (self::APPEND_ONLY_WITH_STATUS as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'status'), "{$table} keeps documented status.");
            $this->assertFalse(Schema::hasColumn($table, 'deleted_at'), "{$table} must not soft-delete.");
            $this->assertFalse(Schema::hasColumn($table, 'version'), "{$table} must not be versioned.");
        }
    }

    public function test_tenant_actor_and_audit_base_columns(): void
    {
        foreach (self::TABLES as $table) {
            $this->assertTrue(
                Schema::hasColumns($table, ['id', 'tenant_id', 'created_at', 'updated_at', 'created_by', 'updated_by']),
                "{$table} missing tenant/audit base columns."
            );
        }
    }

    public function test_documented_unique_constraints_exist(): void
    {
        foreach (self::UNIQUES as $table => $name) {
            $found = DB::selectOne(
                'SELECT 1 AS x FROM pg_constraint WHERE conname = ? AND conrelid = ?::regclass',
                [$name, $table]
            );
            $this->assertNotNull($found, "Missing unique constraint {$name} on {$table}.");
        }
    }

    public function test_documented_secondary_indexes_exist(): void
    {
        $missing = array_values(array_filter(
            self::INDEXES,
            fn (string $i) => ! $this->indexExists($i)
        ));

        $this->assertSame([], $missing, 'Missing documented secondary indexes.');
    }

    public function test_foreign_keys_exist_with_restrict(): void
    {
        $tables = implode("','", self::TABLES);
        $rows = DB::select(
            "SELECT conrelid::regclass AS tbl, pg_get_constraintdef(oid) AS def
             FROM pg_constraint
             WHERE contype='f' AND connamespace='public'::regnamespace
             AND conrelid::regclass::text IN ('{$tables}')"
        );

        // 37 FK constraints across the 30 tables (verified canonical surface).
        $this->assertCount(37, $rows, 'Expected 37 foreign keys in Batch 2B.');
        foreach ($rows as $row) {
            $this->assertStringContainsString('ON DELETE RESTRICT', (string) $row->def);
        }
    }

    public function test_rls_is_forced_on_every_batch2b_table(): void
    {
        $tables = implode("','", self::TABLES);
        $violations = DB::select(
            "SELECT c.relname FROM pg_class c
             JOIN pg_tables t ON t.tablename = c.relname AND t.schemaname = 'public'
             WHERE c.relname IN ('{$tables}') AND c.relforcerowsecurity <> true"
        );

        $this->assertSame([], array_column($violations, 'relname'), 'Every Batch 2B table must FORCE RLS.');
    }

    private function indexExists(string $name): bool
    {
        return DB::selectOne(
            'SELECT 1 AS x FROM pg_indexes WHERE schemaname = ? AND indexname = ?',
            ['public', $name]
        ) !== null;
    }
}
