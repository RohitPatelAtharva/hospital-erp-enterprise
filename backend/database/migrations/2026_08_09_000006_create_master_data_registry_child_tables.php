<?php

declare(strict_types=1);

use App\Support\RowLevelSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch 2B (Master Data) — registry child tables.
 *
 * The second half of the 30 remaining tables (§19-§32 of 04-Database-Tables.md).
 * Every table references only Batch 1/2A foundation tables or the Batch 2B
 * registry roots (migration 2026_08_09_000005). Ordering within this file
 * satisfies every foreign-key dependency (import_staging_row precedes
 * import_validation; version roots already exist in migration 000005).
 *
 * Column set follows the documented surface in 04-Database-Tables.md §19-§32:
 * shared base columns plus FK, candidate-key, and index-referenced columns.
 * Append-only tables omit status / soft-delete / version columns; import and
 * export staging/queue items retain a documented status column and, where a
 * `_time` index exists, the established `occurred_at` column (precedent:
 * audit_reference). Cross-module references (actor_id, assignee_id,
 * approver_id, source_system_id) carry no local FK. Row Level Security is
 * enabled and FORCED on each table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Match score (duplicate_candidate + match_rule; append-only) ------
        Schema::create('match_score', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('duplicate_candidate_id')->constrained('duplicate_candidate')->restrictOnDelete();
            $t->foreignUuid('match_rule_id')->constrained('match_rule')->restrictOnDelete();
            $t->decimal('value', 10, 4)->nullable();
            $t->index('duplicate_candidate_id', 'ix_match_score_candidate');
            $t->index('value', 'ix_match_score_value');
        });

        // --- Duplicate review (1:1 duplicate_candidate; append-only) -----------
        Schema::create('duplicate_review', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('duplicate_candidate_id')->constrained('duplicate_candidate')->restrictOnDelete();
            // actor_id is a cross-module IAM reference (04 §3) — no local FK.
            $t->uuid('actor_id')->nullable();
            $t->unique(['duplicate_candidate_id'], 'uq_duplicate_review_candidate');
            $t->index('actor_id', 'ix_duplicate_review_actor');
        });

        // --- Golden record source (golden_record_link) -------------------------
        Schema::create('golden_record_source', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('golden_record_link_id')->constrained('golden_record_link')->restrictOnDelete();
            // source_system_id is a cross-module integration reference — no local FK.
            $t->uuid('source_system_id')->nullable();
            $t->index('golden_record_link_id', 'ix_golden_source_link');
        });

        // --- Merge record (merge_event + master_record; append-only) -----------
        Schema::create('merge_record', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('merge_event_id')->constrained('merge_event')->restrictOnDelete();
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->index('merge_event_id', 'ix_merge_record_event');
            $t->index('master_record_id', 'ix_merge_record_master');
        });

        // --- Merge approval (1:1 merge_event; append-only) ---------------------
        Schema::create('merge_approval', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('merge_event_id')->constrained('merge_event')->restrictOnDelete();
            // approver_id is a cross-module IAM reference (04 §3) — no local FK.
            $t->uuid('approver_id')->nullable();
            $t->unique(['merge_event_id'], 'uq_merge_approval_event');
        });

        // --- Survivorship decision (append-only) -------------------------------
        Schema::create('survivorship_decision', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('merge_event_id')->constrained('merge_event')->restrictOnDelete();
            $t->foreignUuid('survivorship_rule_id')->constrained('survivorship_rule')->restrictOnDelete();
            $t->index('merge_event_id', 'ix_survivorship_event');
            $t->index('survivorship_rule_id', 'ix_survivorship_rule');
        });

        // --- Remediation task (quality_issue) ----------------------------------
        Schema::create('remediation_task', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('quality_issue_id')->constrained('quality_issue')->restrictOnDelete();
            // assignee_id is a cross-module IAM reference (04 §3) — no local FK.
            $t->uuid('assignee_id')->nullable();
            $t->index('quality_issue_id', 'ix_remediation_issue');
            $t->index('assignee_id', 'ix_remediation_assignee');
        });

        // --- Stewardship log (append-only; time via documented index) ----------
        Schema::create('stewardship_log', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('quality_issue_id')->constrained('quality_issue')->restrictOnDelete();
            $t->uuid('actor_id')->nullable();
            $t->timestamp('occurred_at')->useCurrent();
            $t->index('actor_id', 'ix_stewardship_log_actor');
            $t->index('occurred_at', 'ix_stewardship_log_time');
        });

        // --- Import staging row (append-only; status + candidate-key members) --
        Schema::create('import_staging_row', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('import_batch_id')->constrained('import_batch')->restrictOnDelete();
            $t->string('status', 20)->nullable();
            // candidate key (batch_id + row_num); FK column is import_batch_id
            // (04 §3 drift resolution), row_num is the additional member.
            $t->unsignedInteger('row_num')->nullable();
            $t->index('import_batch_id', 'ix_import_row_batch');
            $t->index('status', 'ix_import_row_status');
        });

        // --- Import validation (1:1 import_staging_row; append-only) -----------
        Schema::create('import_validation', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('import_staging_row_id')->constrained('import_staging_row')->restrictOnDelete();
            $t->unique(['import_staging_row_id'], 'uq_import_validation_row');
            $t->index('import_staging_row_id', 'ix_import_validation_row');
        });

        // --- Export queue item (append-only; status + candidate-key members) ---
        Schema::create('export_queue_item', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('export_batch_id')->constrained('export_batch')->restrictOnDelete();
            $t->string('status', 20)->nullable();
            // candidate key (batch_id + item_ref); FK column is export_batch_id
            // (04 §3 drift resolution), item_ref is the additional member.
            $t->string('item_ref', 255)->nullable();
            $t->index('export_batch_id', 'ix_export_item_batch');
            $t->index('status', 'ix_export_item_status');
        });

        // --- Export recipient (append-only) ------------------------------------
        Schema::create('export_recipient', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('export_batch_id')->constrained('export_batch')->restrictOnDelete();
            $t->foreignUuid('integration_endpoint_id')->constrained('integration_endpoint')->restrictOnDelete();
            $t->index('export_batch_id', 'ix_export_recipient_batch');
        });

        // --- Mapping field (integration_map) ------------------------------------
        Schema::create('mapping_field', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('integration_map_id')->constrained('integration_map')->restrictOnDelete();
            // candidate key (map_id + source_field); FK column is integration_map_id
            // (04 §3 drift resolution), source_field is the additional member.
            $t->string('source_field', 255)->nullable();
            $t->index('integration_map_id', 'ix_mapping_field_map');
        });

        // --- Xref resolution (1:1 cross_reference; append-only) ----------------
        Schema::create('xref_resolution', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('cross_reference_id')->constrained('cross_reference')->restrictOnDelete();
            $t->unique(['cross_reference_id'], 'uq_xref_resolution_ref');
        });

        // --- Version snapshot (1:1 version; append-only) -----------------------
        Schema::create('version_snapshot', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('version_id')->constrained('version')->restrictOnDelete();
            $t->unique(['version_id'], 'uq_version_snapshot_version');
            $t->index('version_id', 'ix_version_snapshot_version');
        });

        // --- Version audit (1:1 version + audit_reference; append-only) --------
        Schema::create('version_audit', function (Blueprint $t) {
            $this->appendBase($t);
            $t->foreignUuid('version_id')->constrained('version')->restrictOnDelete();
            $t->foreignUuid('audit_reference_id')->constrained('audit_reference')->restrictOnDelete();
            $t->unique(['version_id'], 'uq_version_audit_version');
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
        'match_score',
        'duplicate_review',
        'golden_record_source',
        'merge_record',
        'merge_approval',
        'survivorship_decision',
        'remediation_task',
        'stewardship_log',
        'import_staging_row',
        'import_validation',
        'export_queue_item',
        'export_recipient',
        'mapping_field',
        'xref_resolution',
        'version_snapshot',
        'version_audit',
    ];
};
