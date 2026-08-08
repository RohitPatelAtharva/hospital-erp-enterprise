# Master Data Module — Changelog

> **Document ID:** `master-data/23-Changelog`
> **Owner:** Engineering / Documentation Lead
> **Status:** ✅ Approved / Living
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Updated on every change; reviewed at phase gates.
>
> **Relationship:** This document is the **change log** for the Master Data Management module. It records document versions and key decisions, consistent with the platform ADR practice ([02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) §18).

---

## Table of Contents

1. [Changelog Purpose](#1-changelog-purpose)
2. [Versioning Convention](#2-versioning-convention)
3. [Change Categories](#3-change-categories)
4. [Document Versions](#4-document-versions)
5. [Module Changelog](#5-module-changelog)
6. [ADR Index](#6-adr-index)
7. [Review Log](#7-review-log)

---

## 1. Changelog Purpose

This document records **what changed** in the Master Data module documentation, enabling traceability and review. Every document carries its own Appendix A — Change Log; this file is the consolidated view.

---

## 2. Versioning Convention

| Scheme | Usage |
| --- | --- |
| `MAJOR.MINOR.PATCH` | Document version |
| MAJOR | Breaking/schema or contract change |
| MINOR | Additive design change |
| PATCH | Correction |

---

## 3. Change Categories

| Category | Example |
| --- | --- |
| Added | New document/entity/endpoint |
| Changed | Design revision |
| Deprecated | Superseded item |
| Removed | Deleted item |
| Fixed | Correction |
| Security | Security control |

---

## 4. Document Versions

| Document | Version | Date |
| --- | --- | --- |
| [README](README.md) | 1.0.0 | 2026-08-06 |
| [01-Business-Requirements](01-Business-Requirements.md) | 1.0.0 | 2026-08-06 |
| [02-Workflow](02-Workflow.md) | 1.0.0 | 2026-08-06 |
| [03-Database](03-Database.md) | 1.0.0 | 2026-08-06 |
| [04-Database-Tables](04-Database-Tables.md) | 1.0.0 | 2026-08-06 |
| [05-Relationships](05-Relationships.md) | 1.0.0 | 2026-08-06 |
| [06-ERD](06-ERD.md) | 1.0.0 | 2026-08-06 |
| [07-Domain-Model](07-Domain-Model.md) | 1.0.0 | 2026-08-06 |
| [08-UI](08-UI.md) | 1.0.0 | 2026-08-06 |
| [09-UX](09-UX.md) | 1.0.0 | 2026-08-06 |
| [10-API](10-API.md) | 1.0.0 | 2026-08-06 |
| [11-Permissions](11-Permissions.md) | 1.0.0 | 2026-08-06 |
| [12-Security](12-Security.md) | 1.0.0 | 2026-08-06 |
| [13-Audit](13-Audit.md) | 1.0.0 | 2026-08-06 |
| [14-Notifications](14-Notifications.md) | 1.0.0 | 2026-08-06 |
| [15-Reports](15-Reports.md) | 1.0.0 | 2026-08-06 |
| [16-Dashboards](16-Dashboards.md) | 1.0.0 | 2026-08-06 |
| [17-Import-Export](17-Import-Export.md) | 1.0.0 | 2026-08-06 |
| [18-Integrations](18-Integrations.md) | 1.0.0 | 2026-08-06 |
| [19-Performance](19-Performance.md) | 1.0.0 | 2026-08-06 |
| [20-Testing](20-Testing.md) | 1.0.0 | 2026-08-06 |
| [21-Deployment](21-Deployment.md) | 1.0.0 | 2026-08-06 |
| [22-Risks](22-Risks.md) | 1.0.0 | 2026-08-06 |
| [23-Changelog](23-Changelog.md) | 1.0.0 | 2026-08-06 |
| [24-Future-Roadmap](24-Future-Roadmap.md) | 1.0.0 | 2026-08-06 |

---

## 5. Module Changelog

| Date | Category | Detail | Reference |
| --- | --- | --- | --- |
| 2026-08-06 | Added | Initial master-data documentation set (00–24) | this series |
| 2026-08-06 | Added | Canonical schema (04) grounded in Hospital Setup staff/facility | [06-ERD](06-ERD.md) §21 |

---

## 6. ADR Index

| ADR | Decision | Reference |
| --- | --- | --- |
| MD-01 | Single source of truth for master identity | [02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) |
| MD-02 | Staff master external to Hospital Setup | [06-ERD](06-ERD.md) §21 |
| MD-03 | No hard delete; deactivate + archive | [02-Workflow](02-Workflow.md) §16 |
| MD-04 | Merge/unmerge reversible + audited | [07-Domain-Model](07-Domain-Model.md) §21 |
| MD-05 | Interop (FHIR/HL7) deferred | [18-Integrations](18-Integrations.md) |

---

## 7. Review Log

| Review | Outcome | Date |
| --- | --- | --- |
| Phase 1 design review | Pending | — |

---

*End of `docs/modules/master-data/23-Changelog.md`.*
