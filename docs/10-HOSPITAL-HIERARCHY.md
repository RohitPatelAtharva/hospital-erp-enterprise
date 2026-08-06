# Hospital ERP Enterprise — Hospital Hierarchy

> **Document ID:** `10-HOSPITAL-HIERARCHY.md`
> **Owner:** Architecture / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the organizational model (facilities, departments, units, locations) that scopes data and authorization. Complements the tenancy model in [09-MULTI-TENANCY](09-MULTI-TENANCY.md) and role scoping in [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Hierarchy Principles](#2-hierarchy-principles)
3. [Organization Model](#3-organization-model)
4. [Facilities & Locations](#4-facilities--locations)
5. [Departments & Units](#5-departments--units)
6. [Staff Assignment & Roles](#6-staff-assignment--roles)
7. [Hierarchy & Scoping](#7-hierarchy--scoping)
8. [Lifecycle & Administration](#8-lifecycle--administration)
9. [Data Model Implications](#9-data-model-implications)
10. [Open Decisions](#10-open-decisions)
11. [Document Map & Dependencies](#11-document-map--dependencies)
12. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **hospital organization hierarchy** for the platform: facilities, locations, departments, units, and how staff are assigned within them. The hierarchy determines where data lives and who can access it.

**Scope:** organizational structure, facilities/locations, departments/units, staff assignment. Out of scope: tenancy isolation ([09-MULTI-TENANCY](09-MULTI-TENANCY.md)) and the authorization role/permission model ([07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)).

---

## 2. Hierarchy Principles

1. **One canonical structure.** The organization model is a single source of truth referenced by all modules.
2. **Flexible and configurable.** Supports varied hospital structures without code changes.
3. **Scoping anchor.** The hierarchy drives data and authorization scoping.
4. **Auditable.** Structure changes are tracked and reviewed.
5. **Multi-facility ready.** The model supports a single facility now and a hierarchy of facilities later.

---

## 3. Organization Model

```
Facility (Tenant/Facility root)
└── Location(s)            e.g., campus, building
    └── Department(s)      e.g., Cardiology, Emergency, Pharmacy, Finance
        └── Unit(s)        e.g., ICU, Ward 3A, Lab 2
            └── Rooms / Beds (where applicable)
```

- A **Facility** is the top-level tenant boundary ([09-MULTI-TENANCY](09-MULTI-TENANCY.md)).
- **Locations, Departments, and Units** form a flexible hierarchy for scoping and reporting.
- **Rooms/Beds** support operational tracking (inventory, admissions).

---

## 4. Facilities & Locations

| Entity | Description |
| --- | --- |
| **Facility** | Root organizational/tenant unit; owns configuration and reference data. |
| **Location** | Physical/administrative grouping (campus, building, site). |
| Attributes | Code, name, address, time zone, status, contacts. |

---

## 5. Departments & Units

- **Department:** a functional area (clinical or administrative) with a head/owner.
- **Unit:** a sub-area of a department (ward, ICU, lab station, service desk).
- Departments/units are **typed** (clinical vs administrative) to support role and workflow rules.
- Departments carry configuration relevant to their function (e.g., billing dept GL settings).

---

## 6. Staff Assignment & Roles

- Staff are assigned to departments/units with a **primary assignment** and optional **secondary assignments**.
- Assignment drives the **scope** of the staff member's permissions ([07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)) — e.g., a nurse's access is scoped to their units.
- Assignment changes are audited ([08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)) and reflected in access immediately.

---

## 7. Hierarchy & Scoping

- **Data scoping:** records (patients, orders, results, inventory) reference the facility and department/unit where they apply.
- **Authorization scoping:** a principal's effective scope is derived from their facility/department/unit assignments (see [06-AUTHENTICATION](06-AUTHENTICATION.md), [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)).
- **Cross-facility access** is granted explicitly, never by default.
- **Reporting** can roll up along the hierarchy (unit → department → facility).

---

## 8. Lifecycle & Administration

- **Create/update/deactivate** of facilities, locations, departments, units is an admin function with approval.
- **Reorganization** (merges, moves) is versioned and audited to preserve historical accuracy.
- **No hard deletes** of hierarchy nodes that have data; deactivation + reassignment is the norm.
- Administration follows the role model in [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md) (facility admin, system admin).

---

## 9. Data Model Implications

- Core hierarchy entities (facility, location, department, unit) with stable codes and parent references.
- Tenant-scoped per [09-MULTI-TENANCY](09-MULTI-TENANCY.md); versioned via migrations in `database/` ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- Effective-dating where historical correctness matters (e.g., staff assignment history).

---

## 10. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| HH-1 | Hierarchy depth | Fixed (4 levels) vs configurable | Flexible, configurable depth |
| HH-2 | Bed tracking in v1 | Rooms/beds vs units only | Units now; beds in an operations phase |
| HH-3 | Cross-facility assignment | Supported vs single-facility | Supported, explicitly granted |

*Confirmed at the Phase 1 gate.*

---

## 11. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | Hierarchy persistence & migrations |
| [06-AUTHENTICATION](06-AUTHENTICATION.md) | Scope derivation |
| [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md) | Scoped role assignment |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Audit of structural changes |
| [09-MULTI-TENANCY](09-MULTI-TENANCY.md) | Facility as tenant boundary |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created hospital hierarchy: principles, organization model, facilities/locations, departments/units, staff assignment, scoping, lifecycle, data-model implications, and open decisions. |

---

*End of `10-HOSPITAL-HIERARCHY.md`.*
