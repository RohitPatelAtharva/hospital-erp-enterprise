<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Batch 2A (Master Data) — schema fidelity tests.
 *
 * Verifies the 34 entity master tables described in 04-Database-Tables.md §5-§18
 * exist with their canonical columns and that append-only tables omit the
 * lifecycle status/version/soft-delete columns. Runs on PostgreSQL.
 */
final class Batch2aDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const TABLES = [
        // §5 Core
        'master_record', 'golden_record', 'enterprise_person',
        // §6 Patient
        'patient', 'patient_identifier', 'patient_demographic',
        'patient_consent', 'patient_relation', 'patient_alias',
        // §7 Staff
        'staff', 'staff_identifier', 'staff_credential',
        'staff_demographic', 'staff_consent',
        // §8 Provider
        'provider', 'provider_credential', 'provider_network', 'provider_identifier',
        // §9 Organization
        'organization', 'organization_contact', 'organization_identifier',
        'organization_type', 'organization_relationship',
        // §10 Facility reference
        'facility_reference', 'department_reference', 'unit_reference',
        // §13 Identity
        'identity_issuer', 'identity_record', 'identity_assignment',
        // §14 Contact
        'contact_preference',
        // §15 Address
        'address_validation',
        // §16 Document
        'master_document',
        // §17 Language
        'language_preference', 'language_proficiency',
    ];

    public function test_all_34_batch2a_tables_exist(): void
    {
        $missing = array_values(array_filter(self::TABLES, fn (string $t) => ! Schema::hasTable($t)));

        $this->assertSame([], $missing, 'Missing Batch 2A tables.');
        $this->assertCount(34, self::TABLES);
    }

    public function test_master_record_has_canonical_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('master_record', [
            'id', 'tenant_id', 'status', 'entity_type_id', 'external_ref',
            'created_at', 'updated_at', 'created_by', 'updated_by',
            'deleted_at', 'version',
        ]));
        $this->assertTrue(Schema::hasIndex('master_record', ['tenant_id', 'external_ref'], 'unique'));
    }

    public function test_patient_has_indexed_columns(): void
    {
        // ix_patient_name / ix_patient_dob / ix_patient_sex name the columns.
        $this->assertTrue(Schema::hasColumns('patient', [
            'master_record_id', 'enterprise_person_id', 'name', 'dob', 'sex',
        ]));
        $this->assertTrue(Schema::hasIndex('patient', ['name']));
        $this->assertTrue(Schema::hasIndex('patient', ['dob']));
        $this->assertTrue(Schema::hasIndex('patient', ['sex']));
    }

    public function test_provider_network_references_organization(): void
    {
        // 04 §8: provider_network.network_id is an N:1 reference to organization.
        $this->assertTrue(Schema::hasColumns('provider_network', [
            'provider_id', 'network_id',
        ]));
        $this->assertTrue(Schema::hasIndex('provider_network', ['provider_id', 'network_id'], 'unique'));
    }

    public function test_identity_record_has_issuer_fk(): void
    {
        $this->assertTrue(Schema::hasColumns('identity_record', [
            'master_record_id', 'identity_type_id', 'identity_issuer_id', 'value',
        ]));
        $this->assertTrue(Schema::hasIndex('identity_record', [
            'master_record_id', 'identity_type_id', 'value',
        ], 'unique'));
    }

    public function test_append_only_tables_omit_lifecycle_columns(): void
    {
        // identity_assignment and address_validation are append-only
        // (Soft Delete: No, Versioned: No) — no status / deleted_at / version.
        foreach (['identity_assignment', 'address_validation'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'status'), "$table must not have status.");
            $this->assertFalse(Schema::hasColumn($table, 'deleted_at'), "$table must not have deleted_at.");
            $this->assertFalse(Schema::hasColumn($table, 'version'), "$table must not have version.");
        }
    }

    public function test_organization_relationship_has_relation_type_fk(): void
    {
        $this->assertTrue(Schema::hasColumns('organization_relationship', [
            'organization_id', 'related_org_id', 'relation_type_id',
        ]));
        $this->assertTrue(Schema::hasIndex('organization_relationship', [
            'organization_id', 'related_org_id', 'relation_type_id',
        ], 'unique'));
    }
}
