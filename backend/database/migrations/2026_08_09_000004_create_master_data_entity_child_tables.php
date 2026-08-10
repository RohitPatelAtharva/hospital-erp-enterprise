<?php

declare(strict_types=1);

use App\Support\RowLevelSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch 2A (Master Data) — entity child tables.
 *
 * These tables reference the Batch 2A entity roots (migration
 * 2026_08_09_000003) and Batch 1 foundation tables. Ordering within this file
 * satisfies every foreign-key dependency.
 *
 * Column set follows the documented surface in 04-Database-Tables.md §6-§17:
 * shared base columns plus FK, candidate-key, and index-referenced columns.
 * Append-only tables (identity_assignment, address_validation) omit the
 * status / soft-delete / version columns per their spec (Soft Delete: No,
 * Versioned: No). Row Level Security is enabled and FORCED on each table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Unit reference (depends on department_reference) -----------------
        Schema::create('unit_reference', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('department_reference_id')->constrained('department_reference')->restrictOnDelete();
            $t->string('external_ref', 255)->nullable();
            $t->unique(['external_ref'], 'uq_unit_ref_external');
            $t->index('department_reference_id', 'ix_unit_ref_department');
        });

        // --- Patient children ------------------------------------------------
        Schema::create('patient_identifier', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('patient_id')->constrained('patient')->restrictOnDelete();
            $t->foreignUuid('identity_type_id')->constrained('identity_type')->restrictOnDelete();
            $t->string('value', 255);
            $t->unique(['patient_id', 'identity_type_id', 'value'], 'uq_patient_identifier_value');
            $t->index('value', 'ix_patient_identifier_value');
            $t->index('identity_type_id', 'ix_patient_identifier_type');
        });

        Schema::create('patient_demographic', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('patient_id')->constrained('patient')->restrictOnDelete();
            $t->string('ethnicity', 100)->nullable();
            $t->unique(['patient_id'], 'uq_patient_demographic_patient');
            $t->index('ethnicity', 'ix_patient_demographic_ethnicity');
        });

        Schema::create('patient_consent', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('patient_id')->constrained('patient')->restrictOnDelete();
            $t->foreignUuid('consent_type_id')->constrained('consent_type')->restrictOnDelete();
            $t->index('patient_id', 'ix_patient_consent_patient');
            $t->index('status', 'ix_patient_consent_status');
        });

        Schema::create('patient_relation', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('patient_id')->constrained('patient')->restrictOnDelete();
            $t->foreignUuid('related_patient_id')->constrained('patient')->restrictOnDelete();
            $t->foreignUuid('relation_type_id')->constrained('relation_type')->restrictOnDelete();
            $t->unique(['patient_id', 'related_patient_id', 'relation_type_id'], 'uq_patient_relation_pair');
            $t->index('patient_id', 'ix_patient_relation_patient');
        });

        Schema::create('patient_alias', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('patient_id')->constrained('patient')->restrictOnDelete();
            $t->string('name', 255)->nullable();
            $t->index('name', 'ix_patient_alias_name');
        });

        // --- Staff children --------------------------------------------------
        Schema::create('staff_identifier', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $t->foreignUuid('identity_type_id')->constrained('identity_type')->restrictOnDelete();
            $t->string('value', 255);
            $t->unique(['staff_id', 'identity_type_id', 'value'], 'uq_staff_identifier_value');
            $t->index('value', 'ix_staff_identifier_value');
        });

        Schema::create('staff_credential', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $t->foreignUuid('credential_type_id')->constrained('credential_type')->restrictOnDelete();
            $t->string('number', 100)->nullable();
            $t->date('expiry')->nullable();
            $t->unique(['staff_id', 'credential_type_id', 'number'], 'uq_staff_credential_number');
            $t->index('staff_id', 'ix_staff_credential_staff');
            $t->index('expiry', 'ix_staff_credential_expiry');
        });

        Schema::create('staff_demographic', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $t->unique(['staff_id'], 'uq_staff_demographic_staff');
        });

        Schema::create('staff_consent', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('staff_id')->constrained('staff')->restrictOnDelete();
            $t->foreignUuid('consent_type_id')->constrained('consent_type')->restrictOnDelete();
            $t->index('staff_id', 'ix_staff_consent_staff');
        });

        // --- Provider children ----------------------------------------------
        Schema::create('provider_credential', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('provider_id')->constrained('provider')->restrictOnDelete();
            $t->foreignUuid('credential_type_id')->constrained('credential_type')->restrictOnDelete();
            $t->string('number', 100)->nullable();
            $t->unique(['provider_id', 'credential_type_id', 'number'], 'uq_provider_credential_number');
            $t->index('provider_id', 'ix_provider_credential_provider');
        });

        // provider_network.network_id references organization (per 04 §8:
        // "N:1 organization(network)") — network_id is the documented FK name.
        Schema::create('provider_network', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('provider_id')->constrained('provider')->restrictOnDelete();
            $t->foreignUuid('network_id')->constrained('organization')->restrictOnDelete();
            $t->unique(['provider_id', 'network_id'], 'uq_provider_network_pair');
        });

        Schema::create('provider_identifier', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('provider_id')->constrained('provider')->restrictOnDelete();
            $t->foreignUuid('identity_type_id')->constrained('identity_type')->restrictOnDelete();
            $t->string('value', 255);
            $t->unique(['provider_id', 'identity_type_id', 'value'], 'uq_provider_identifier_value');
            $t->index('value', 'ix_provider_identifier_value');
        });

        // --- Organization children ------------------------------------------
        Schema::create('organization_contact', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('organization_id')->constrained('organization')->restrictOnDelete();
            $t->foreignUuid('contact_id')->constrained('contact')->restrictOnDelete();
            $t->index('organization_id', 'ix_org_contact_org');
        });

        Schema::create('organization_identifier', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('organization_id')->constrained('organization')->restrictOnDelete();
            $t->foreignUuid('identity_type_id')->constrained('identity_type')->restrictOnDelete();
            $t->string('value', 255);
            $t->unique(['organization_id', 'identity_type_id', 'value'], 'uq_org_identifier_value');
            $t->index('value', 'ix_org_identifier_value');
        });

        Schema::create('organization_relationship', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('organization_id')->constrained('organization')->restrictOnDelete();
            $t->foreignUuid('related_org_id')->constrained('organization')->restrictOnDelete();
            $t->foreignUuid('relation_type_id')->constrained('relation_type')->restrictOnDelete();
            $t->unique(['organization_id', 'related_org_id', 'relation_type_id'], 'uq_org_relation_pair');
            $t->index('organization_id', 'ix_org_relation_org');
        });

        // --- Identity assignment (append-only; depends on identity_record) ----
        Schema::create('identity_assignment', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->foreignUuid('identity_record_id')->constrained('identity_record')->restrictOnDelete();
            // actor_id is a cross-module IAM reference (04 §3) — no local FK.
            $t->uuid('actor_id')->nullable();
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->index('identity_record_id', 'ix_identity_assignment_record');
        });

        // --- Contact preference (depends on master_record + contact) ----------
        Schema::create('contact_preference', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('contact_id')->constrained('contact')->restrictOnDelete();
            $t->unique(['master_record_id'], 'uq_contact_pref_master');
        });

        // --- Address validation (append-only; depends on address) ------------
        Schema::create('address_validation', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->foreignUuid('address_id')->constrained('address')->restrictOnDelete();
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->unique(['address_id'], 'uq_address_validation_address');
        });

        // --- Master document (depends on master_record + document types) -----
        Schema::create('master_document', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('document_type_id')->constrained('document_type')->restrictOnDelete();
            $t->foreignUuid('document_storage_id')->constrained('document_storage')->restrictOnDelete();
            $t->index('master_record_id', 'ix_master_document_master');
            $t->index('document_type_id', 'ix_master_document_type');
        });

        // --- Language preference / proficiency (depend on master_record) ------
        Schema::create('language_preference', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('language_id')->constrained('language')->restrictOnDelete();
            $t->unique(['master_record_id', 'language_id'], 'uq_lang_pref_master_lang');
        });

        Schema::create('language_proficiency', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('master_record_id')->constrained('master_record')->restrictOnDelete();
            $t->foreignUuid('language_id')->constrained('language')->restrictOnDelete();
            $t->unique(['master_record_id', 'language_id'], 'uq_lang_prof_master_lang');
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
        'unit_reference',
        'patient_identifier',
        'patient_demographic',
        'patient_consent',
        'patient_relation',
        'patient_alias',
        'staff_identifier',
        'staff_credential',
        'staff_demographic',
        'staff_consent',
        'provider_credential',
        'provider_network',
        'provider_identifier',
        'organization_contact',
        'organization_identifier',
        'organization_relationship',
        'identity_assignment',
        'contact_preference',
        'address_validation',
        'master_document',
        'language_preference',
        'language_proficiency',
    ];
};
