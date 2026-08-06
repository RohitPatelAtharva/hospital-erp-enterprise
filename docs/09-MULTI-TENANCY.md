# Hospital ERP Enterprise — Multi-Tenancy

> **Document ID:** `09-MULTI-TENANCY.md`
> **Owner:** Architecture / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines how the platform isolates and scopes data across facilities/tenants. Builds on facility scoping in [06-AUTHENTICATION](06-AUTHENTICATION.md) and [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md); the organization model is in [10-HOSPITAL-HIERARCHY](10-HOSPITAL-HIERARCHY.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Tenancy Principles](#2-tenancy-principles)
3. [Tenancy Model](#3-tenancy-model)
4. [Isolation Strategy](#4-isolation-strategy)
5. [Data Model Implications](#5-data-model-implications)
6. [Tenancy & Authorization](#6-tenancy--authorization)
7. [Tenancy & Data Access](#7-tenancy--data-access)
8. [Operations & Migrations](#8-operations--migrations)
9. [Multi-Facility vs Multi-Tenant](#9-multi-facility-vs-multi-tenant)
10. [Open Decisions](#10-open-decisions)
11. [Document Map & Dependencies](#11-document-map--dependencies)
12. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **multi-tenancy model** for the Hospital ERP Enterprise platform: how data is scoped and isolated across facilities and (future) tenants, how authorization enforces tenant boundaries, and how the model evolves from single-facility to multi-facility operation.

**Scope:** tenancy, isolation, scoping. Out of scope: the facility/org hierarchy (see [10-HOSPITAL-HIERARCHY](10-HOSPITAL-HIERARCHY.md)) and the authorization role model (see [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)).

---

## 2. Tenancy Principles

1. **Data integrity first.** A user must never see, modify, or infer another tenant's data.
2. **Default isolated.** Access is denied unless explicitly scoped to the tenant.
3. **Single-facility first, multi-facility ready.** The model supports one facility now and multiple later without rework.
4. **Enforced everywhere.** Tenant scoping is enforced at the API, service, and data layers — not just in the UI.
5. **Logical isolation by default.** Physical isolation only where compliance demands.
6. **Auditable.** Tenant-boundary enforcement and any cross-tenant access are audited ([08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)).

---

## 3. Tenancy Model

| Concept | Definition |
| --- | --- |
| **Tenant / Facility** | The highest-level data boundary (a hospital or operating entity). |
| **Context** | The tenant/facility a request operates within. |
| **Scope** | The set of contexts a principal is authorized to act in. |
| **Organization** | The hierarchy (facilities → departments → units) defined in [10-HOSPITAL-HIERARCHY](10-HOSPITAL-HIERARCHY.md). |

- **v1:** single facility/tenant deployed; the model is present but only one active tenant.
- **v2+:** multiple facilities/tenants active; data remains isolated per tenant.

---

## 4. Isolation Strategy

- **Preferred: logical (shared-schema) isolation** — a shared database with an explicit `tenant_id` (or `facility_id`) column on tenant-scoped tables, enforced by authorization and row-level security.
- **Row-level security (RLS):** database policies enforce tenant scoping as a backstop for the most sensitive records ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- **Physical isolation** (separate DB/cluster) reserved for regulatory requirements or demonstrated performance need; recorded as an ADR.
- **No cross-tenant leakage:** queries, caches, search indexes, and exports are tenant-scoped.

---

## 5. Data Model Implications

- Tenant-scoped tables carry a `tenant_id` (facility) column and are indexed accordingly.
- Global (reference) data (e.g., clinical codes, config) is shared and immutable or tenant-overridable per policy.
- Keys are unique within a tenant; cross-tenant references are forbidden.
- Migrations must remain valid and deterministic across tenants.

---

## 6. Tenancy & Authorization

- The authenticated principal carries **context scope** (tenant/facility) in its authorization (see [06-AUTHENTICATION](06-AUTHENTICATION.md), [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)).
- **Coarse check:** the API/gateway ensures the request context is within the principal's scope.
- **Fine-grained check:** services re-verify tenant scope and relationship (defense in depth).
- **Data backstop:** RLS enforces tenant scoping at the database.

---

## 7. Tenancy & Data Access

- All application reads/writes are tenant-aware; the ORM/data layer injects tenant context.
- **Search index** is partitioned by tenant; results are filtered by the principal's scope.
- **Cache keys** include tenant context to prevent cross-tenant reads.
- **Exports/analytics** are scoped per tenant and permission-controlled.
- **No shared secrets** or cross-tenant service credentials.

---

## 8. Operations & Migrations

- **Data migrations** are tenant-aware and idempotent across tenants.
- **Backup/restore** supports tenant-scoped restore where required.
- **Onboarding a new tenant** is a controlled process: provisioning, seeding reference data, config, and admin roles.
- **Offboarding** follows retention and deletion policy ([08-AUDIT-LOGGING](08-AUDIT-LOGGING.md), [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).

---

## 9. Multi-Facility vs Multi-Tenant

| Aspect | Multi-facility (same enterprise) | Multi-tenant (multiple enterprises) |
| --- | --- | --- |
| Data sharing | Shared reference data, controlled cross-facility | Isolated per tenant |
| User model | Users may span facilities | Users belong to one tenant |
| Reporting | Cross-facility consolidated | Per-tenant |
| Governance | One enterprise | Per-tenant |

The v1 target is **multi-facility (single enterprise)**; the isolation mechanisms here support extension to true multi-tenancy.

---

## 10. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| MT-1 | v1 tenancy | Single-facility only vs multi-facility ready | Multi-facility ready, single active |
| MT-2 | Isolation | Logical (shared schema + RLS) vs physical | Logical with RLS |
| MT-3 | True multi-tenant SaaS | Later vs out of scope for v1 | Out of scope for v1 |

*Confirmed at the Phase 1 gate.*

---

## 11. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | RLS backstop, schema, isolation |
| [06-AUTHENTICATION](06-AUTHENTICATION.md) | Identity & context scoping |
| [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md) | Authorization scoping |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Audit of tenant-boundary events |
| [10-HOSPITAL-HIERARCHY](10-HOSPITAL-HIERARCHY.md) | Organization hierarchy |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Multi-facility readiness sequencing |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created multi-tenancy: principles, tenancy model, isolation strategy, data-model implications, tenancy & authorization/data access, operations, multi-facility vs multi-tenant, and open decisions. |

---

*End of `09-MULTI-TENANCY.md`.*
