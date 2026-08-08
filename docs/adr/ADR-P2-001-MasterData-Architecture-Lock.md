# ADR-P2-001 — Phase 2 Master Data Architecture Lock

> **Status:** ✅ Approved (Phase 2 gate)
> **Date:** 2026-08-08
> **Owner:** Architecture / Engineering Lead (data)
> **Applies to:** Phase 2 — Master Data database implementation
> **Scope of this lock:** engine, tenant isolation mechanism, schema-count safety, and module boundaries. Locks ADR-002 (PostgreSQL as canonical store) to **Approved**. Supersedes the Phase-1 local MySQL8 bootstrap for Master Data schema purposes (the Phase-1 bootstrap is not the Master Data target).

---

## 1. D1 — Database Engine: PostgreSQL 16+

| Decision | Value |
| --- | --- |
| Production | **PostgreSQL 16+** |
| Development | **PostgreSQL 16+** |
| Testing | **PostgreSQL 16+**-compatible test environment (same engine, single instance, synthetic data only) |
| Source of truth | `03-Database.md`, `05-DATABASE-ARCHITECTURE.md`, `03-TECHNOLOGY-STACK.md` §4.3 (ADR-002) |

Rules:
- PostgreSQL-specific features (RLS, constraints, indexes, JSONB, partitioning) are the target.
- **SQLite is not used** to validate Master Data schema where PostgreSQL-specific behavior (RLS, constraints, indexes) is under test.
- **No MySQL-specific syntax** is introduced into Master Data migrations.

## 2. D2 — Row Level Security (PostgreSQL)

Tenant isolation MUST exist at both the **application layer** and the **database layer**.

```
Authenticated Principal
        ↓
TenantContext
        ↓
Server-derived tenant identity   (NEVER a client-controlled header)
        ↓
PostgreSQL session transaction context  (app.tenant)
        ↓
RLS policy  (tenant_id = current_setting('app.tenant'))
        ↓
Tenant-scoped database operation
```

- Tenant is derived **only** from the authenticated principal (per Phase-1 `TenantContext`/`SetTenantContext`).
- No client override; no cross-tenant fallback; **fail closed** when tenant context is missing.
- Prevent tenant-context leakage between requests; correctly reset/replace on connection reuse.
- Transaction-safe; **background jobs must explicitly establish tenant context**; CLI/queue execution must not inherit stale tenant context.

## 3. Tenant Context Mechanism

- **Session setting:** `app.tenant` — the PostgreSQL session setting used by RLS.
- Set safely per request/transaction by the application data layer before any query.
- RLS policy predicate: `tenant_id = current_setting('app.tenant')::uuid`.
- Mechanism is a **database-layer backstop** over application-layer scoping (defense in depth), not a replacement.

## 4. R-79..R-87 Reconciliation

Documentation-only discrepancies (naming/R-ID notes) are **recorded, not engineered**:
- R-86 naming drift: FK is `network_id` (role-played `organization`); keep canonical column.
- R-87 R-ID note: catalog ID absent from the ERD projection; keep canonical relationship.
- R-79..R-87 cardinality/endpoints are consistent; **no new relationship is invented**.

Canonical sources: `04-Database-Tables.md`, `05-Relationships.md`, `06-ERD.md`. Where docs contain only naming commentary but the FK/entity definition is consistent, preserve the canonical schema.

## 5. 109-Table Safety Gate

- Canonical count: **exactly 109 tables** (107 + `archive_table` + `archive_manifest`).
- No extra Master Data tables may be created; no approved table may be silently omitted.
- All 109 accounted for before migration generation.

## 6. Hospital Setup Boundary

- `facility_reference`, `department_reference`, `unit_reference` remain **references/mirror views** (tenant_id only).
- **No duplicate Hospital Setup ownership models** are created.

## 7. Clinical Terminology Boundary

- The approved schema includes `clinical_code_set`, `clinical_code`, `clinical_vocabulary`, `clinical_mapping`, and `terminology_*` — their **schema may be created** (approved reference/terminology tables).
- **Phase 2 does NOT implement external clinical-standard runtime integrations** unless explicitly approved elsewhere. Prohibited this phase: HL7, FHIR, DICOM, ICD, SNOMED, LOINC, ABDM runtime integrations.

## 8. Migration Prerequisites

Before any migration is generated, all gate items below must be satisfied.

### Database Safety Gate — Checklist

- [x] PostgreSQL 16+ selected
- [x] PostgreSQL-specific architecture preserved
- [x] RLS strategy documented (this record)
- [x] `app.tenant` mechanism defined (this record)
- [x] tenant context is server-derived (never client header)
- [x] fail-closed behavior defined
- [x] background-job tenant handling defined
- [x] connection-reuse safety defined
- [x] exactly 109 canonical tables accounted for
- [x] R-79..R-87 reconciled
- [x] `identity_record` remains canonical R-44 target
- [x] no Hospital Setup duplication
- [x] no undocumented tables
- [x] no undocumented relationships
- [x] no external clinical-standard runtime implementation
- [x] no migration generated yet

---

## Decision Record

- **DATABASE ENGINE:** LOCKED (PostgreSQL 16+; ADR-002 → Approved)
- **RLS:** LOCKED (PostgreSQL RLS via `app.tenant`; dual-layer tenant isolation)
- **SCHEMA COUNT:** 109
- **IMPLEMENTATION:** NOT STARTED
