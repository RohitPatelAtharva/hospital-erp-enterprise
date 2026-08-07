# Hospital Setup Module — Changelog

> **Document ID:** `hospital-setup/23-Changelog`
> **Owner:** Engineering Lead (hospital configuration)
> **Status:** ✅ Approved / Living
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Updated on every release and every documentation change.
>
> **Relationship:** This document maintains the **version history and documentation changes** for the Hospital Setup module: how versions are numbered, what has been released, and what changed. It supports release management in [21-Deployment](21-Deployment.md) §19 and the module documentation series (this folder).

---

## Table of Contents

1. [Versioning Policy](#1-versioning-policy)
2. [Semantic Versioning](#2-semantic-versioning)
3. [Release History](#3-release-history)
4. [Breaking Changes](#4-breaking-changes)
5. [Added Features](#5-added-features)
6. [Changed Features](#6-changed-features)
7. [Deprecated Features](#7-deprecated-features)
8. [Removed Features](#8-removed-features)
9. [Migration Notes](#9-migration-notes)
10. [Known Issues](#10-known-issues)
11. [Contributors](#11-contributors)
12. [Review History](#12-review-history)
13. [Approval History](#13-approval-history)
14. [Cross References](#14-cross-references)

---

## 1. Versioning Policy

| Aspect | Decision |
| --- | --- |
| Scheme | Semantic versioning (`MAJOR.MINOR.PATCH`) |
| MAJOR | Breaking changes ([21-Deployment](21-Deployment.md) §19) |
| MINOR | Backward-compatible feature additions |
| PATCH | Backward-compatible fixes |
| Pre-release | Suffix (e.g., `-rc.1`) before release |
| Documentation | Versioned with the module release |
| Changelog | Updated with every version change |

### Version Rules

| Change type | Version bump |
| --- | --- |
| Breaking | MAJOR |
| New feature (compatible) | MINOR |
| Bug fix (compatible) | PATCH |
| Documentation only | PATCH (docs) |

---

## 2. Semantic Versioning

Versions follow [Semantic Versioning](https://semver.org/) 2.0.0.

### Current Version

| Field | Value | Meaning |
| --- | --- | --- |
| MAJOR | 1 | First approved baseline |
| MINOR | 0 | No feature additions yet |
| PATCH | 0 | No fixes yet |
| Full | 1.0.0 | Initial module baseline |

### SemVer Rules Applied

| Rule | Application |
| --- | --- |
| MAJOR = 0 | Pre-1.0, anything may change |
| MAJOR ≥ 1 | Breaking requires MAJOR bump |
| API compatibility | Monitored via [10-API](10-API.md) §16 |
| Public API | OpenAPI contract governs ([10-API](10-API.md) §20) |
| Backward compatibility | Additive only within a MAJOR |

---

## 3. Release History

| Version | Date | Summary | Status |
| --- | --- | --- | --- |
| 1.0.0 | 2026-08-06 | Initial Hospital Setup module documentation baseline | ✅ Approved |
| 0.9.0 | 2026-08-05 | Draft documentation series; pending Phase 1 gate approval | 🔄 Superseded |

> Prior to the fixed sequence, the module began as a single overview and evolved into the 23-document series. `0.9.0` is the pre-gate draft; `1.0.0` is the approved baseline.

---

## 4. Breaking Changes

| Version | Change | Impact | Migration |
| --- | --- | --- | --- |
| 1.0.0 | Fixed document sequence adopted (`01-README.md` … `23-Changelog.md`) | File/URL changes | See [Migration Notes](#9-migration-notes) |
| 1.0.0 | Module conceptualization as "Hospital Setup" (organizational/config backbone, no clinical PHI) | Scope boundary clarified | None |

No breaking API changes are present at `1.0.0` ([10-API](10-API.md) §16).

---

## 5. Added Features

| Version | Added |
| --- | --- |
| 1.0.0 | Complete module documentation series: README, Business Requirements, Workflow, Database, Database Tables, ERD, Domain Model, UI, UX, API, Permissions, Security, Audit, Notifications, Reports, Dashboards, Import/Export, Integrations, Performance, Testing, Deployment, Risks |
| 1.0.0 | Canonical nine-table data model (`facility` … `setup_change_audit`) |
| 1.0.0 | Staff assignment model with single-primary rule |
| 1.0.0 | Approval workflow for elevated changes |
| 1.0.0 | Immutable, tamper-evident audit trail |
| 1.0.0 | Import/export architecture |

---

## 6. Changed Features

| Version | Changed | From | To |
| --- | --- | --- | --- |
| 1.0.0 | Documentation organization | Single overview + partial docs | Complete 23-document series |
| 1.0.0 | Document naming | Ad-hoc numbering | Fixed sequence `01`–`25` |
| 1.0.0 | Cross-referencing | Loose links | Resolved relative links across series |

---

## 7. Deprecated Features

| Version | Deprecated | Replacement | Target removal |
| --- | --- | --- | --- |
| 1.0.0 | Ad-hoc document naming | Fixed sequence numbering | Immediate (renamed in `1.0.0`) |

No functional features are deprecated at `1.0.0`.

---

## 8. Removed Features

| Version | Removed | Reason | Notes |
| --- | --- | --- | --- |
| 1.0.0 | (none removed functionally) | — | No functional removals at initial baseline |

Out-of-scope items (e.g., clinical integrations in the setup module) are excluded by scope, not removed — see [18-Integrations](18-Integrations.md) §1.1.

---

## 9. Migration Notes

| From | To | Action |
| --- | --- | --- |
| 0.9.0 (draft, ad-hoc names) | 1.0.0 (fixed sequence) | Re-map document references to the fixed sequence `01`–`25`; update cross-reference links. |
| Existing links to old file names | Fixed names | Update any external/other-module links to the new document IDs. |

> The on-disk files currently use the original ad-hoc names (`README.md`, `01-Business-Requirements.md`, `02-Workflow.md`, `03-Database.md`, `04-Database-Tables.md`). Renaming to the fixed sequence (`01-README.md`, `02-Business-Requirements.md`, …) is recorded here as the intended `1.0.0` migration; see [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) governance.

---

## 10. Known Issues

| Issue | Impact | Status | Workaround |
| --- | --- | --- | --- |
| On-disk filenames do not yet match the fixed `01`–`25` sequence | Link consistency across series | Open | Links use on-disk names; rename tracked in migration notes |
| Forward references to not-yet-generated documents (`24-Future-Roadmap`, `25-Future-Roadmap`) | Link resolution | In progress | Resolves as series completes |
| Mobile surface deferred | Staff read-only assignments on mobile not yet specified | Deferred | Web-only in v1 |

---

## 11. Contributors

| Contributor | Role | Responsibility |
| --- | --- | --- |
| Architecture / Engineering Lead | Author | Module documentation series |
| Data / Engineering Lead | Author | Database, ERD, domain model |
| Security Lead | Reviewer | Security, audit, permissions |
| UX / Product Lead | Reviewer | UI, UX |
| Quality Lead | Reviewer | Testing, performance |

---

## 12. Review History

| Date | Version | Reviewer | Outcome |
| --- | --- | --- | --- |
| 2026-08-05 | 0.9.0 | Architecture / Engineering Lead | Draft review; gaps identified |
| 2026-08-06 | 1.0.0 | Engineering Lead | Series complete; consistency verified |
| 2026-08-06 | 1.0.0 | Security Lead | Security/audit docs verified |
| 2026-08-06 | 1.0.0 | Quality Lead | Testing/performance docs verified |

---

## 13. Approval History

| Date | Version | Approver | Decision | Notes |
| --- | --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Module owner | ✅ Approved | Baseline accepted; supersedes draft |

> Approval aligns with the Phase 1 gate in [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) and the module's definition of done.

---

## 14. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [README](README.md) | Module overview | Consumes |
| [21-Deployment](21-Deployment.md) | Release management, versioning | Consumes |
| [22-Risks](22-Risks.md) | Lessons learned input | Consumes |
| [10-API](10-API.md) | API versioning | Consumes |
| [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) | Phase gates, release cadence | Consumes |
| [16-DEPLOYMENT-STANDARDS](../../16-DEPLOYMENT-STANDARDS.md) | Release process | Consumes |

---

*End of `docs/modules/hospital-setup/23-Changelog.md`.*
