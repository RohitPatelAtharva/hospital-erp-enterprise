<?php

declare(strict_types=1);

use App\Support\RowLevelSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch 2A (Master Data) — core entity master tables.
 *
 * These tables form the root layer of the entity-specific master records.
 * Every table references only Batch 1 foundation tables (entity_type,
 * identity_type) or tables earlier in this file, so ordering satisfies each
 * foreign-key dependency. No table in this migration references a Batch 2A
 * child table — those live in migration 2026_08_09_000004.
 *
 * Column set follows the documented surface in 04-Database-Tables.md §5-§18:
 * the shared base columns plus FK columns, candidate-key columns, and
 * index-referenced columns named by each table's spec. `tenant_id` remains a
 * cross-module reference (no constraint). Row Level Security is enabled and
 * FORCED on each table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Organization type (tenant-scoped vocabulary) ---------------------
        Schema::create('organization_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_org_type_tenant_code');
        });

        // --- Facility reference (tenant-scoped mirror of Hospital Setup) ------
        Schema::create('facility_reference', function (Blueprint $t) {
            $this->base($t);
            $t->string('external_ref', 255)->nullable();
            $t->string('code', 50)->nullable();
            $t->unique(['external_ref'], 'uq_facility_ref_external');
            $t->index(['tenant_id', 'code'], 'ix_facility_ref_tenant_code');
        });

        // --- Enterprise person (EPI cross-role index) -------------------------
        Schema::create('enterprise_person', function (Blueprint $t) {
            $this->base($t);
            $t->string('name', 255)->nullable();
            $t->date('dob')->nullable();
            $t->index('name', 'ix_enterprise_person_name');
            $t->index('dob', 'ix_enterprise_person_dob');
        });

        // --- Master record (supertype root) -----------------------------------
        Schema::create('master_record', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('entity_type_id')->constrained('entity_type')->restrictOnDelete();
            $t->string('external_ref', 255)->nullable();
            $t->unique(['tenant_id', 'external_ref'], 'uq_master_record_tenant_ext_ref');
            $t->index(['tenant_id', 'status'], 'ix_master_record_tenant_status');
        });

        // --- Organization (depends on master_record + organization_type) ------
        Schema::create('organization', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('organization_type_id')->constrained('organization_type')->restrictOnDelete();
            $t->string('name', 255)->nullable();
            $t->index('name', 'ix_organization_name');
            $t->index('organization_type_id', 'ix_organization_type');
        });

        // --- Golden record (depends on master_record) -------------------------
        Schema::create('golden_record', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->unique(['master_record_id'], 'uq_golden_record_master');
            $t->index(['tenant_id', 'status'], 'ix_golden_record_tenant_status');
        });

        // --- Patient (depends on master_record + enterprise_person) -----------
        Schema::create('patient', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('enterprise_person_id')->constrained('enterprise_person')->restrictOnDelete();
            $t->string('name', 255)->nullable();
            $t->date('dob')->nullable();
            $t->string('sex', 20)->nullable();
            $t->index('name', 'ix_patient_name');
            $t->index('dob', 'ix_patient_dob');
            $t->index('sex', 'ix_patient_sex');
        });

        // --- Staff (depends on master_record + enterprise_person) -------------
        Schema::create('staff', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('enterprise_person_id')->constrained('enterprise_person')->restrictOnDelete();
            $t->string('name', 255)->nullable();
            $t->index('name', 'ix_staff_name');
            $t->index('status', 'ix_staff_status');
        });

        // --- Provider (depends on master_record) ------------------------------
        Schema::create('provider', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->string('name', 255)->nullable();
            $t->string('type', 50)->nullable();
            $t->index('name', 'ix_provider_name');
            $t->index('type', 'ix_provider_type');
        });

        // --- Identity issuer (depends on organization) ------------------------
        Schema::create('identity_issuer', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('organization_id')->constrained('organization')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_identity_issuer_tenant_code');
        });

        // --- Department reference (depends on facility_reference) -------------
        Schema::create('department_reference', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('facility_reference_id')->constrained('facility_reference')->restrictOnDelete();
            $t->string('external_ref', 255)->nullable();
            $t->unique(['external_ref'], 'uq_department_ref_external');
            $t->index('facility_reference_id', 'ix_department_ref_facility');
        });

        // --- Identity record (depends on master_record + identity_issuer) -----
        Schema::create('identity_record', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('identity_type_id')->constrained('identity_type')->restrictOnDelete();
            $t->foreignUuid('identity_issuer_id')->constrained('identity_issuer')->restrictOnDelete();
            $t->string('value', 255);
            $t->unique(['master_record_id', 'identity_type_id', 'value'], 'uq_identity_record_value');
            $t->index('value', 'ix_identity_record_value');
            $t->index('master_record_id', 'ix_identity_record_master');
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

    private const TABLES = [
        'organization_type',
        'facility_reference',
        'enterprise_person',
        'master_record',
        'organization',
        'golden_record',
        'patient',
        'staff',
        'provider',
        'identity_issuer',
        'department_reference',
        'identity_record',
    ];
};
