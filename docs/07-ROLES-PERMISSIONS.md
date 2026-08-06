# Hospital ERP Enterprise — Roles & Permissions

> **Document ID:** `07-ROLES-PERMISSIONS.md`
> **Owner:** Architecture / Engineering Lead (security)
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Details the **authorization model** introduced in [06-AUTHENTICATION](06-AUTHENTICATION.md). Defines the role catalog, permission matrix, scoping, and management workflow delivered in Phase 2 (IAM).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Principles](#2-principles)
3. [Key Concepts](#3-key-concepts)
4. [Role Catalog](#4-role-catalog)
5. [Permission Model](#5-permission-model)
6. [Permission Matrix](#6-permission-matrix)
7. [Scoping & Context](#7-scoping--context)
8. [Separation of Duties](#8-separation-of-duties)
9. [Enforcement Points](#9-enforcement-points)
10. [Role & Permission Management](#10-role--permission-management)
11. [Authorization Testing](#11-authorization-testing)
12. [Open Decisions](#12-open-decisions)
13. [Document Map & Dependencies](#13-document-map--dependencies)
14. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines **roles, permissions, and authorization policies** for the Hospital ERP Enterprise platform: the catalog of roles, how permissions are modeled and mapped to operations, how access is scoped to facilities/departments/context, and how roles are managed and governed.

It operationalizes the authorization model in [06-AUTHENTICATION](06-AUTHENTICATION.md) and is delivered in **Phase 2 (IAM)**.

**Scope:** authorization (who can do what, where), roles/permissions catalog, scoping, governance. Out of scope: authentication mechanics (see [06-AUTHENTICATION](06-AUTHENTICATION.md)) and broader security controls (see [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)).

---

## 2. Principles

1. **Least privilege.** Every role grants the minimum permissions needed for its duties.
2. **Default deny.** Access is denied unless a permission explicitly grants it.
3. **Separation of duties.** No single role should be able to execute conflicting steps in critical processes.
4. **Context matters.** Permissions are scoped to facility/department and, where relevant, patient relationship and consent.
5. **Centrally governed.** The role catalog and permission matrix are defined data, reviewed at gates, and versioned.
6. **Testable.** Authorization rules are automated-tested, not inferred from UI.

---

## 3. Key Concepts

| Concept | Definition |
| --- | --- |
| **Subject** | A user or service identity ([06-AUTHENTICATION](06-AUTHENTICATION.md)). |
| **Principal** | The authenticated, enriched identity used for authorization. |
| **Role** | A named set of permissions granted to a subject in a scope. |
| **Permission** | A granular capability: action + resource (e.g., `orders:create`). |
| **Scope / context** | The facility, department, or patient context in which a role applies. |
| **Policy** | Additional rules refining authorization (e.g., ABAC conditions). |

---

## 4. Role Catalog

Baseline roles (extensible; exact catalog maintained as data, not code):

| Role | Representative permissions | Scope |
| --- | --- | --- |
| **System administrator** | User/role/config administration, audit view, integrations | Global |
| **Facility administrator** | Facility config, staff assignment, non-clinical operations | Per facility |
| **Front-desk / Admissions** | Patient registration, appointment booking, basic billing intake | Facility |
| **Clinician (physician)** | EHR notes, orders, results review, prescribing | Facility / patient |
| **Clinician (nurse)** | Vitals, medication administration, documentation, result acknowledgment | Facility / patient |
| **Laboratory** | Specimens, test ordering, results entry | Facility |
| **Pharmacy** | Formulary, dispensing, medication review | Facility |
| **Finance / Accounts** | Charges, claims, payments, GL | Facility / global reporting |
| **Operations / Procurement** | Inventory, purchase orders, vendors, assets | Facility |
| **Executive** | Dashboards, analytics (read), compliance indicators | Global read |
| **Patient / family** | Self-service: appointments, results, billing, messaging, consent | Self (own record) |
| **Auditor** | Read-only audit access | Global read |

> Role catalog is **initial**; it is refined during Phase 2 with domain SME review and finalized at the gate.

---

## 5. Permission Model

- **Permissions** follow the form `resource:action` (e.g., `orders:create`, `orders:read`, `billing:release`), possibly with finer conditions.
- **Roles** aggregate permissions; a subject's effective access = union over roles **intersected with scope**.
- **Granularity is intentional:** permission granularity matches risk (clinical/financial actions are finer-grained and MFA-gated; see [06-AUTHENTICATION](06-AUTHENTICATION.md)).
- **Model is data-defined and versioned** (schema in [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)); no hard-coded permissions in UI or services.

---

## 6. Permission Matrix

Maintained as the single source of truth for role × permission mapping. It is:

- **Reviewed and signed off** at the Phase 2 gate (roadmap exit criterion).
- **Tested** by automated authorization tests (see §11).
- **Versioned** and audited; every change is traceable.

| Permission | Admin | Front-desk | Clinician | Nurse | Lab | Pharmacy | Finance | Ops | Executive |
| --- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| `patients:create` | ✓ | ✓ | ✓ | ✓ | · | · | ✓ | · | · |
| `patients:read` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | · | read-only |
| `orders:create` | · | · | ✓ | · | ✓ | · | · | · | · |
| `orders:release` | · | · | ✓ | · | · | · | · | · | · |
| `billing:release` | ✓ | · | · | · | · | · | ✓ | · | · |
| `admin:roles` | ✓ | · | · | · | · | · | · | · | · |
| `audit:read` | ✓ | · | · | · | · | · | · | · | ✓ |

> Representative excerpt — the full matrix lives as governed data and is finalized at the gate.

---

## 7. Scoping & Context

- **Facility scope:** most roles apply within a facility or set of facilities; a user's effective scope is the intersection of their assignments.
- **Department scope:** clinical roles may be further scoped to departments.
- **Patient-relationship scope:** clinical access is limited to patients within the user's care team/relationship, reinforced by policy (ABAC) where needed.
- **Consent:** patient consent further restricts access to sensitive records; enforced at the API/service layer.
- **Multi-facility readiness:** the scoping model supports single-facility first and multi-facility later (see [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).

---

## 8. Separation of Duties

- **MUST** enforce separation of duties where clinical/financial integrity demands it (e.g., requester ≠ dispenser for controlled substances; billing releaser ≠ approver).
- Modeled as a **policy rule** evaluated at the enforcement point, not just a role constraint.
- Identified by domain SME review during Phase 2 and validated at the gate.

---

## 9. Enforcement Points

- **API gateway** — coarse authorization (is the principal allowed this route?).
- **Service / domain layer** — fine-grained checks: scope, patient relationship, consent, separation-of-duties policy (defense in depth).
- **Data layer** — row-level security / constraints as a backstop for the most sensitive records (see [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- **UI** — reflects permissions for UX only; the API remains the authority ([04-CODING-STANDARDS](04-CODING-STANDARDS.md)).
- **Audit** — authorization decisions and denials are logged ([06-AUTHENTICATION](06-AUTHENTICATION.md)).

---

## 10. Role & Permission Management

- **Lifecycle:** request → review/approval → assignment (scoped) → change → revocation. All audited.
- **Approval flow:** role changes and elevated access require authorized approvers; sensitive roles require additional review.
- **Deprovisioning:** immediate on offboarding; cascades to sessions/tokens (see [06-AUTHENTICATION](06-AUTHENTICATION.md)).
- **Periodic review:** role assignments and the permission matrix are re-reviewed at gates and on schedule; unused/over-privileged access is removed.
- **Self-service:** users may request access; approvers act on defined rules.

---

## 11. Authorization Testing

- **MUST** maintain automated authorization tests covering the permission matrix (each role × sensitive action).
- **MUST** test negative cases (default deny) and scope boundaries (facility/department/patient).
- **MUST** test separation-of-duties violations are blocked.
- Authorization tests are part of the CI gate and the Phase 2 exit criteria.

---

## 12. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| RP-1 | Policy engine | Custom rules vs OPA/CASL-like | Evaluate a mature engine for ABAC |
| RP-2 | Role granularity | Coarse vs fine | Fine for clinical/financial; coarse elsewhere |
| RP-3 | Patient consent enforcement | Attribute-based only vs explicit consent flags | Explicit consent flags in schema |

*Confirmed at the Phase 1/2 gate.*

---

## 13. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) | Architecture principles |
| [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md) | Identity/authorization technology |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | Row-level security backstop |
| [06-AUTHENTICATION](06-AUTHENTICATION.md) | Identity, tokens, authZ model |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | Roles/permissions data schema |
| [11-API-STANDARDS](11-API-STANDARDS.md) | Enforcement at API layer |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Broader security & compliance |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created roles & permissions: concepts, role catalog, permission model & matrix, scoping, separation of duties, enforcement points, management workflow, authorization testing, and open decisions. |

---

*End of `07-ROLES-PERMISSIONS.md`.*
