<?php

declare(strict_types=1);

use App\Support\RowLevelSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch 1 (Master Data) — leaf foundation tables.
 *
 * These are the dependency-safe reference / vocabulary / registry tables of the
 * Master Data module: none of them has a foreign key to a table outside this
 * batch, so they form the base layer the dependent tables (migration
 * 2026_08_09_000002) build on.
 *
 * Schema fidelity follows docs/modules/master-data/04-Database-Tables.md:
 * - universal columns: id (uuid PK), tenant_id, status, created_at/updated_at,
 *   created_by/updated_by, deleted_at (soft delete), version (optimistic lock)
 * - exact documented Foreign Keys, Candidate Keys, Unique Constraints, Indexes
 * - PostgreSQL Row Level Security (app.tenant) enabled and FORCED on every table
 *
 * `tenant_id` is a cross-module reference (tenancy/IAM owns the tenant table) and
 * therefore carries no FK constraint within this schema.
 */
return new class extends Migration
{
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

    public function up(): void
    {
        // --- Core Master ---------------------------------------------------
        Schema::create('master_domain', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('code', 50);
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->softDeletes();
            $t->unsignedInteger('version')->default(1);
            $t->unique(['tenant_id', 'code'], 'uq_master_domain_tenant_code');
        });

        Schema::create('entity_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_entity_type_tenant_code');
            $t->index('code', 'ix_entity_type_code');
        });

        Schema::create('record_status', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_record_status_tenant_code');
        });

        // --- Geographic Reference ------------------------------------------
        Schema::create('country', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_country_tenant_code');
            $t->index('code', 'ix_country_code');
        });

        // --- Clinical Reference --------------------------------------------
        Schema::create('clinical_code_set', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_clinical_code_set_tenant_code');
        });

        Schema::create('clinical_vocabulary', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_vocabulary_tenant_code');
        });

        // --- Identity Management -------------------------------------------
        Schema::create('identity_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_identity_type_tenant_code');
        });

        // --- Contact ---------------------------------------------------------
        Schema::create('contact_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_contact_type_tenant_code');
        });

        Schema::create('contact_use', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_contact_use_tenant_code');
        });

        // --- Address ---------------------------------------------------------
        Schema::create('address_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_address_type_tenant_code');
        });

        // --- Document --------------------------------------------------------
        Schema::create('document_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_doc_type_tenant_code');
        });

        // document_storage: lifecycle active -> archived, NOT soft-deleted, NOT versioned
        Schema::create('document_storage', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('storage_ref', 255);
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->unique(['storage_ref'], 'uq_doc_storage_ref');
        });

        // --- Language --------------------------------------------------------
        Schema::create('language', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_language_tenant_code');
        });

        // --- Lookup -----------------------------------------------------------
        Schema::create('lookup_category', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_lookup_category_tenant_code');
        });

        Schema::create('enum_definition', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_enum_def_tenant_code');
        });

        // --- Duplicate Detection ---------------------------------------------
        Schema::create('match_rule', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_match_rule_tenant_code');
        });

        // --- Survivorship -----------------------------------------------------
        // attribute_priority: candidate key attribute + source
        Schema::create('attribute_priority', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('attribute', 100);
            $t->string('source', 50);
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->softDeletes();
            $t->unsignedInteger('version')->default(1);
            $t->unique(['attribute', 'source'], 'uq_attribute_priority_source');
        });

        // --- Reference Data ----------------------------------------------------
        Schema::create('reference_category', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_reference_cat_tenant_code');
        });

        // reference_version: candidate key code + version; `version` is the data
        // (edition) column per the documented candidate key, so no separate
        // optimistic-lock integer is added for this table.
        Schema::create('reference_version', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('code', 50);
            $t->string('version', 50);
            $t->string('status', 20)->default('active');
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->softDeletes();
            $t->unique(['code', 'version'], 'uq_reference_version_edition');
        });

        Schema::create('consent_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_consent_type_tenant_code');
            $t->index('code', 'ix_consent_type_code');
        });

        Schema::create('credential_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_credential_type_tenant_code');
            $t->index('code', 'ix_credential_type_code');
        });

        Schema::create('relation_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_relation_type_tenant_code');
            $t->index('code', 'ix_relation_type_code');
        });

        // --- Terminology -------------------------------------------------------
        Schema::create('terminology_service', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_terminology_service_tenant_code');
        });

        // --- Audit Reference ---------------------------------------------------
        Schema::create('audit_action', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_audit_action_tenant_code');
        });

        // audit_actor: append-only, no status/soft-delete/version
        Schema::create('audit_actor', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('actor_key', 255);
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->unique(['actor_key'], 'uq_audit_actor_key');
        });

        Schema::create('audit_retention', function (Blueprint $t) {
            $this->base($t);
            $t->string('category', 50);
            $t->unique(['category'], 'uq_audit_retention_category');
        });

        // --- Integration Mapping ----------------------------------------------
        Schema::create('integration_endpoint', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_integration_endpoint_tenant_code');
        });

        // --- Cross Reference ---------------------------------------------------
        Schema::create('xref_type', function (Blueprint $t) {
            $this->base($t);
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_xref_type_tenant_code');
        });

        // --- Metadata ----------------------------------------------------------
        Schema::create('metadata_catalog', function (Blueprint $t) {
            $this->base($t);
            $t->string('entity', 100);
            $t->unique(['entity'], 'uq_metadata_catalog_entity');
        });

        // --- Archive -----------------------------------------------------------
        Schema::create('archive_table', function (Blueprint $t) {
            $this->base($t);
            $t->string('table_name', 100);
            $t->unique(['table_name'], 'uq_archive_table_name');
        });

        // archive_manifest: append-only, no status/soft-delete/version
        Schema::create('archive_manifest', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('archive_ref', 255);
            $t->timestamp('archived_at')->nullable();
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->unique(['archive_ref'], 'uq_archive_manifest_ref');
            $t->index('archived_at', 'ix_archive_manifest_time');
        });

        // Enable + force Row Level Security on every tenant-scoped table.
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

    private const TABLES = [
        'master_domain',
        'entity_type',
        'record_status',
        'country',
        'clinical_code_set',
        'clinical_vocabulary',
        'identity_type',
        'contact_type',
        'contact_use',
        'address_type',
        'document_type',
        'document_storage',
        'language',
        'lookup_category',
        'enum_definition',
        'match_rule',
        'attribute_priority',
        'reference_category',
        'reference_version',
        'consent_type',
        'credential_type',
        'relation_type',
        'terminology_service',
        'audit_action',
        'audit_actor',
        'audit_retention',
        'integration_endpoint',
        'xref_type',
        'metadata_catalog',
        'archive_table',
        'archive_manifest',
    ];
};
