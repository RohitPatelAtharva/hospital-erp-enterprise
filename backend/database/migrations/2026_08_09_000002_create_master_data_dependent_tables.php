<?php

declare(strict_types=1);

use App\Support\RowLevelSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch 1 (Master Data) — dependent foundation tables.
 *
 * These tables reference only other Batch 1 tables (from migration
 * 2026_08_09_000001 and earlier tables in this file), so they form the second
 * layer of the dependency-safe foundation. Ordering within this migration
 * satisfies each foreign-key dependency.
 *
 * Foreign keys here point at tables within the same module schema and are
 * enforced as real FK constraints. `tenant_id` remains a cross-module reference
 * (no constraint). Row Level Security is enabled and FORCED on each table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Geographic Reference (depend on country) ------------------------
        Schema::create('region', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('country_id')->constrained('country')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['country_id', 'code'], 'uq_region_country_code');
            $t->index('country_id', 'ix_region_country');
        });

        Schema::create('city', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('region_id')->constrained('region')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['region_id', 'code'], 'uq_city_region_code');
            $t->index('region_id', 'ix_city_region');
        });

        Schema::create('postal_code', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('city_id')->constrained('city')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_postal_code_tenant_code');
            $t->index('city_id', 'ix_postal_code_city');
        });

        // --- Lookup (depend on lookup_category) -------------------------------
        Schema::create('lookup', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('lookup_category_id')->constrained('lookup_category')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['lookup_category_id', 'code'], 'uq_lookup_cat_code');
            $t->index('lookup_category_id', 'ix_lookup_category');
        });

        Schema::create('lookup_value', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('lookup_category_id')->constrained('lookup_category')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['lookup_category_id', 'code'], 'uq_lookup_value_cat_code');
            $t->index('lookup_category_id', 'ix_lookup_value_category');
        });

        // --- Clinical Reference (clinical_code -> clinical_code_set) ----------
        Schema::create('clinical_code', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('clinical_code_set_id')->constrained('clinical_code_set')->restrictOnDelete();
            $t->string('code', 50);
            $t->string('edition', 50);
            $t->string('value', 255);
            $t->unique(['clinical_code_set_id', 'code', 'edition'], 'uq_clinical_code_set_edition');
            $t->index('value', 'ix_clinical_code_value');
            $t->index('clinical_code_set_id', 'ix_clinical_code_set');
        });

        Schema::create('clinical_mapping', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('source_code_id')->constrained('clinical_code')->restrictOnDelete();
            $t->foreignUuid('target_code_id')->constrained('clinical_code')->restrictOnDelete();
            $t->unique(['source_code_id', 'target_code_id'], 'uq_clinical_mapping_pair');
            $t->index('source_code_id', 'ix_clinical_mapping_source');
        });

        // --- Contact (depend on contact_type / contact_use) -------------------
        Schema::create('contact', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('contact_type_id')->constrained('contact_type')->restrictOnDelete();
            $t->foreignUuid('contact_use_id')->constrained('contact_use')->restrictOnDelete();
            $t->string('value', 255);
            $t->index('value', 'ix_contact_value');
        });

        // --- Address (depend on address_type + postal_code) -------------------
        Schema::create('address', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('address_type_id')->constrained('address_type')->restrictOnDelete();
            $t->foreignUuid('postal_code_id')->constrained('postal_code')->restrictOnDelete();
            // ix_address_entity is documented for the reusable address table but
            // refers to the entity-address link table (later batch); see ADR
            // deferral note — not created on the base table to avoid an invented
            // entity column.
            $t->index('postal_code_id', 'ix_address_postal');
        });

        // --- Reference Data (depend on reference_category / reference_version) -
        Schema::create('reference_value', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('reference_category_id')->constrained('reference_category')->restrictOnDelete();
            $t->foreignUuid('reference_version_id')->constrained('reference_version')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['reference_category_id', 'code'], 'uq_reference_value_cat_code');
            $t->index('reference_category_id', 'ix_reference_value_category');
            $t->index('code', 'ix_reference_value_code');
        });

        // --- Survivorship (depend on attribute_priority) ----------------------
        Schema::create('survivorship_rule', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('attribute_priority_id')->constrained('attribute_priority')->restrictOnDelete();
            $t->string('code', 50);
            $t->unique(['tenant_id', 'code'], 'uq_survivorship_rule_tenant_code');
        });

        // --- Terminology (edition -> service; entry -> edition + vocabulary) --
        Schema::create('terminology_edition', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('terminology_service_id')->constrained('terminology_service')->restrictOnDelete();
            $t->string('edition', 50);
            $t->unique(['terminology_service_id', 'edition'], 'uq_terminology_edition');
            $t->index('terminology_service_id', 'ix_terminology_edition_service');
        });

        Schema::create('terminology_entry', function (Blueprint $t) {
            $this->base($t);
            $t->foreignUuid('terminology_edition_id')->constrained('terminology_edition')->restrictOnDelete();
            $t->foreignUuid('clinical_vocabulary_id')->constrained('clinical_vocabulary')->restrictOnDelete();
            $t->string('term_code', 100);
            $t->string('display', 255);
            $t->unique(['terminology_edition_id', 'term_code'], 'uq_terminology_entry_edition_code');
            $t->index('term_code', 'ix_terminology_entry_code');
            $t->index('display', 'ix_terminology_entry_display');
        });

        // --- Audit Reference (depend on audit_action / audit_actor) -----------
        // append-only: no status, no soft-delete, no version column
        Schema::create('audit_reference', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id');
            $t->string('event_id', 255);
            $t->foreignUuid('audit_action_id')->constrained('audit_action')->restrictOnDelete();
            $t->foreignUuid('audit_actor_id')->constrained('audit_actor')->restrictOnDelete();
            $t->string('entity', 100);
            $t->uuid('entity_id')->nullable();
            $t->timestamp('occurred_at')->useCurrent();
            $t->timestamps();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->unique(['event_id'], 'uq_audit_reference_event');
            $t->index('entity', 'ix_audit_reference_entity');
            $t->index('occurred_at', 'ix_audit_reference_time');
            $t->index('audit_actor_id', 'ix_audit_reference_actor');
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
        'region',
        'city',
        'postal_code',
        'lookup',
        'lookup_value',
        'clinical_code',
        'clinical_mapping',
        'contact',
        'address',
        'reference_value',
        'survivorship_rule',
        'terminology_edition',
        'terminology_entry',
        'audit_reference',
    ];
};
