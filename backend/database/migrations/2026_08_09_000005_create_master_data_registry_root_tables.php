<?php

declare(strict_types=1);

use App\Support\RowLevelSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch 2B (Master Data) — registry root tables.
 *
 * The first half of the 30 remaining tables (§19-§32 of 04-Database-Tables.md):
 * duplicate detection, golden-record links, merge events, stewardship,
 * import/export batches, integration mapping, cross-reference, metadata and
 * version roots.
 *
 * Every table here references only Batch 1/2A foundation tables or earlier
 * tables in this file, so ordering satisfies each foreign-key dependency. No
 * table in this migration references a Batch 2B child table — those live in
 * migration 2026_08_09_000006.
 *
 * Column set follows the documented surface in 04-Database-Tables.md §19-§32:
 * the shared base columns plus FK, candidate-key, and index-referenced columns
 * named by each table's spec. Append-only tables (Soft Delete: No, Versioned:
 * No) omit the status / soft-delete / version columns; import/export batches
 * retain a documented status column and the established `occurred_at` time
 * column (precedent: audit_reference). Cross-module actor references (actor_id,
 * approver_id, reported_by, source_system_id) carry no local FK. `tenant_id`
 * remains a cross-module reference. Row Level Security is enabled and FORCED.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Duplicate candidate (role-played master_record pair) -------------
        Schema::create('duplicate_candidate', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('candidate_record_id')->constrained('master_record')->restrictOnDelete();
            $t->unique(['master_record_id', 'candidate_record_id'], 'uq_duplicate_pair');
            $t->index('master_record_id', 'ix_duplicate_master');
            $t->index('status', 'ix_duplicate_status');
        });

        // --- Match threshold (1:1 match_rule) ---------------------------------
        Schema::create('match_threshold', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('match_rule_id')->constrained('match_rule')->restrictOnDelete();
            $t->unique(['match_rule_id'], 'uq_match_threshold_rule');
        });

        // --- Golden record link (golden_record + master_record) ----------------
        Schema::create('golden_record_link', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('golden_record_id')->constrained('golden_record')->restrictOnDelete();
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->unique(['golden_record_id', 'master_record_id'], 'uq_golden_link_pair');
            $t->index('golden_record_id', 'ix_golden_link_golden');
            $t->index('master_record_id', 'ix_golden_link_master');
        });

        // --- Golden record audit (append-only) --------------------------------
        Schema::create('golden_record_audit', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('golden_record_id')->constrained('golden_record')->restrictOnDelete();
            // actor_id is a cross-module IAM reference (04 §3) — no local FK.
            $t->uuid('actor_id')->nullable();
            $t->index('golden_record_id', 'ix_golden_audit_golden');
        });

        // --- Merge event (append-only) ----------------------------------------
        Schema::create('merge_event', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('golden_record_id')->constrained('golden_record')->restrictOnDelete();
            $t->uuid('actor_id')->nullable();
            $t->timestamp('occurred_at')->useCurrent();
            $t->index('golden_record_id', 'ix_merge_event_golden');
            $t->index('occurred_at', 'ix_merge_event_time');
        });

        // --- Steward assignment (master_domain + staff) ------------------------
        Schema::create('steward_assignment', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_domain_id')->constrained('master_domain')->restrictOnDelete();
            $t->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $t->index('master_domain_id', 'ix_steward_assignment_domain');
        });

        // --- Quality issue (master_record) -------------------------------------
        Schema::create('quality_issue', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            // reported_by is a cross-module IAM reference (04 §3) — no local FK.
            $t->uuid('reported_by')->nullable();
            $t->string('severity', 20)->nullable();
            $t->index('master_record_id', 'ix_quality_issue_master');
            $t->index('severity', 'ix_quality_issue_severity');
        });

        // --- Import batch (append-only; status + time via documented indexes) --
        Schema::create('import_batch', function (Blueprint $t) {
            $this->appendBase($t);
            $t->uuid('actor_id')->nullable();
            $t->string('status', 20)->nullable();
            $t->string('batch_ref', 255);
            $t->timestamp('occurred_at')->useCurrent();
            $t->unique(['batch_ref'], 'uq_import_batch_ref');
            $t->index('status', 'ix_import_batch_status');
            $t->index('occurred_at', 'ix_import_batch_time');
        });

        // --- Export batch (append-only; status via documented index) -----------
        Schema::create('export_batch', function (Blueprint $t) {
            $this->appendBase($t);
            $t->uuid('actor_id')->nullable();
            $t->string('status', 20)->nullable();
            $t->string('batch_ref', 255);
            $t->unique(['batch_ref'], 'uq_export_batch_ref');
            $t->index('status', 'ix_export_batch_status');
        });

        // --- Integration map (integration_endpoint) ----------------------------
        Schema::create('integration_map', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('integration_endpoint_id')->constrained('integration_endpoint')->restrictOnDelete();
            // candidate key member (endpoint_id + resource_type); FK column is
            // integration_endpoint_id (04 §3 drift resolution).
            $t->string('resource_type', 100)->nullable();
            $t->index('integration_endpoint_id', 'ix_integration_map_endpoint');
        });

        // --- Cross reference (master_record + xref_type) -----------------------
        Schema::create('cross_reference', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('xref_type_id')->constrained('xref_type')->restrictOnDelete();
            $t->string('external_ref', 255);
            $t->unique(['master_record_id', 'xref_type_id', 'external_ref'], 'uq_cross_reference_external');
            $t->index('master_record_id', 'ix_cross_reference_master');
            $t->index('external_ref', 'ix_cross_reference_external');
        });

        // --- Schema metadata (metadata_catalog) --------------------------------
        Schema::create('schema_metadata', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('metadata_catalog_id')->constrained('metadata_catalog')->restrictOnDelete();
            // candidate key member (catalog_id + column); FK column is
            // metadata_catalog_id (04 §3 drift resolution).
            $t->string('column', 100)->nullable();
            $t->index('metadata_catalog_id', 'ix_schema_metadata_catalog');
        });

        // --- Data dictionary (metadata_catalog) --------------------------------
        Schema::create('data_dictionary', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('metadata_catalog_id')->constrained('metadata_catalog')->restrictOnDelete();
            $t->string('term', 255);
            $t->unique(['term'], 'uq_data_dictionary_term');
        });

        // --- Version (master_record; append-only) ------------------------------
        Schema::create('version', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->uuid('actor_id')->nullable();
            $t->unsignedInteger('version_number');
            $t->timestamp('occurred_at')->useCurrent();
            $t->unique(['master_record_id', 'version_number'], 'uq_version_record_number');
            $t->index('master_record_id', 'ix_version_master');
            $t->index('occurred_at', 'ix_version_time');
        });

        foreach (self::TABLES as $table) {
            RowLevelSecurity::enable($table);
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $table) {
            RowLevelSecurity::disable($table);
            Schema::dropIfExists($table);
        }
    }

    /**
     * Stateful base: lifecycle + soft-delete + versioned.
     */
    private function base(Blueprint $t): void
    {
        $t->uuid('id')->primary();
        $t->uuid('tenant_id');
        $t->string('status', 20)->default('active');
        $t->timestamps();
        $t->uuid('created_by')->nullable();
        $t->uuid('updated_by')->nullable();
        $t->softDeletes();
        $t->unsignedInteger('version')->default(1);
    }

    /**
     * Append-only base: no status / soft-delete / version columns.
     */
    private function appendBase(Blueprint $t): void
    {
        $t->uuid('id')->primary();
        $t->uuid('tenant_id');
        $t->timestamps();
        $t->uuid('created_by')->nullable();
        $t->uuid('updated_by')->nullable();
    }

    private const TABLES = [
        'duplicate_candidate',
        'match_threshold',
        'golden_record_link',
        'golden_record_audit',
        'merge_event',
        'steward_assignment',
        'quality_issue',
        'import_batch',
        'export_batch',
        'integration_map',
        'cross_reference',
        'schema_metadata',
        'data_dictionary',
        'version',
    ];
};
