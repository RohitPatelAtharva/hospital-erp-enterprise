# Master Data Module — API Specification

> **Document ID:** `master-data/10-API`
> **Owner:** Architecture / Engineering Lead (API)
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate and when API standards change.
>
> **Relationship:** This document defines the **REST API** of the Master Data Management module — endpoints, contracts, security, and conventions. It follows [11-API-STANDARDS](../../11-API-STANDARDS.md) as authoritative, exposes only the business capabilities approved in [01-Business-Requirements](01-Business-Requirements.md) and [02-Workflow](02-Workflow.md), and enforces the permissions in [11-Permissions](11-Permissions.md).

---

## Table of Contents

1. [API Overview](#1-api-overview)
2. [API Principles](#2-api-principles)
3. [Versioning](#3-versioning)
4. [Authentication](#4-authentication)
5. [Authorization](#5-authorization)
6. [Tenant Scoping](#6-tenant-scoping)
7. [Patient APIs](#7-patient-apis)
8. [Staff APIs](#8-staff-apis)
9. [Provider APIs](#9-provider-apis)
10. [Organization APIs](#10-organization-apis)
11. [Reference Data APIs](#11-reference-data-apis)
12. [Search APIs](#12-search-apis)
13. [Duplicate APIs](#13-duplicate-apis)
14. [Golden Record APIs](#14-golden-record-apis)
15. [Merge APIs](#15-merge-apis)
16. [Unmerge APIs](#16-unmerge-apis)
17. [Approval APIs](#17-approval-apis)
18. [Version APIs](#18-version-apis)
19. [Stewardship APIs](#19-stewardship-apis)
20. [Import APIs](#20-import-apis)
21. [Export APIs](#21-export-apis)
22. [Integration APIs](#22-integration-apis)
23. [Audit APIs](#23-audit-apis)
24. [Error Model](#24-error-model)
25. [Pagination](#25-pagination)
26. [Filtering](#26-filtering)
27. [Sorting](#27-sorting)
28. [Idempotency](#28-idempotency)
29. [Rate Limiting](#29-rate-limiting)
30. [API Security](#30-api-security)
31. [API Observability](#31-api-observability)
32. [Cross References](#32-cross-references)

---

## 1. API Overview

The Master Data API exposes canonical master-data operations over REST under `/api/v1`. It is consumed by the web/mobile UI ([08-UI](08-UI.md)) and integrations ([18-Integrations](18-Integrations.md)). It follows the platform API standards ([11-API-STANDARDS](../../11-API-STANDARDS.md)) and the event-driven architecture ([02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) §12).

---

## 2. API Principles

| # | Principle | Application |
| --- | --- | --- |
| API-01 | Contract-first | OpenAPI-defined ([11-API-STANDARDS](../../11-API-STANDARDS.md) §4) |
| API-02 | Resource-oriented | Nouns + HTTP verbs |
| API-03 | Versioned | `/api/v1` ([11-API-STANDARDS](../../11-API-STANDARDS.md) §5) |
| API-04 | Tenant-scoped | All data isolated ([09-MULTI-TENANCY](../../09-MULTI-TENANCY.md)) |
| API-05 | Authorized | Enforcement at gateway + service ([07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md) §9) |
| API-06 | Audited | All mutations audited ([13-Audit](13-Audit.md)) |
| API-07 | Idempotent | Retryable writes ([11-API-STANDARDS](../../11-API-STANDARDS.md) §12) |

---

## 3. Versioning

| Aspect | Decision |
| --- | --- |
| Scheme | URL path versioning `/api/v1` |
| Evolution | Additive within version |
| Breaking | New major version |
| Contract | OpenAPI served per version |

---

## 4. Authentication

| Aspect | Decision |
| --- | --- |
| Standard | OAuth 2.0 / OIDC ([06-AUTHENTICATION](../../06-AUTHENTICATION.md)) |
| Public clients | Authorization Code + PKCE |
| Service clients | Client credentials / mTLS |
| Token | Short-lived access token validated at gateway |
| MFA | Required for elevated actions ([06-AUTHENTICATION](../../06-AUTHENTICATION.md) §5) |

---

## 5. Authorization

| Aspect | Decision |
| --- | --- |
| Model | RBAC + policy (ABAC) ([07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md)) |
| Enforcement | Gateway coarse; service fine-grained |
| Permissions | See [11-Permissions](11-Permissions.md) |
| Scope | Facility/department context enforced |

---

## 6. Tenant Scoping

| Aspect | Decision |
| --- | --- |
| Source | Tenant derived from authenticated token |
| Enforcement | RLS at data layer ([06-ERD](06-ERD.md) §22) |
| Cross-tenant | Blocked |
| Consistency | All related rows share tenant |

---

## 7. Patient APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/patients` | GET (list) | `patients:read` |
| `/patients` | POST (create) | `patients:create` |
| `/patients/{id}` | GET | `patients:read` |
| `/patients/{id}` | PATCH | `patients:update` |
| `/patients/{id}` | DELETE (deactivate) | `patients:update` + approval |
| `/patients/{id}/identifiers` | GET/POST | `patients:read`/`patients:update` |
| `/patients/{id}/demographics` | GET/PATCH | `patients:read`/`patients:update` |
| `/patients/{id}/consents` | GET/POST | `patients:read`/`patients:update` |
| `/patients/{id}/relations` | GET/POST | `patients:read`/`patients:update` |
| `/patients/{id}/aliases` | GET/POST | `patients:read`/`patients:update` |
| `/patients/{id}/identifiers/{identifierId}/rotate` | POST | `patients:update` |
| `/patients/{id}/archive` | POST | `patients:update` |
| `/patients/{id}/restore` | POST | `patients:update` |
| `/patients/{id}/reactivate` | POST | `patients:update` |
| `/patients/{id}/purge` | POST | `purge:execute` (elevated) |

---

## 8. Staff APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/staff` | GET (list) | `staff:read` |
| `/staff` | POST | `staff:create` |
| `/staff/{id}` | GET | `staff:read` |
| `/staff/{id}` | PATCH | `staff:update` |
| `/staff/{id}` | DELETE (deactivate) | `staff:update` + approval |
| `/staff/{id}/identifiers` | GET/POST | `staff:read`/`staff:update` |
| `/staff/{id}/credentials` | GET/POST | `staff:read`/`staff:update` |
| `/staff/{id}/consents` | GET/POST | `staff:read`/`staff:update` |
| `/staff/{id}/identifiers/{identifierId}/rotate` | POST | `staff:update` |
| `/staff/{id}/archive` | POST | `staff:update` |
| `/staff/{id}/restore` | POST | `staff:update` |
| `/staff/{id}/reactivate` | POST | `staff:update` |
| `/staff/{id}/purge` | POST | `purge:execute` (elevated) |

---

## 9. Provider APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/providers` | GET (list) | `providers:read` |
| `/providers` | POST | `providers:create` |
| `/providers/{id}` | GET | `providers:read` |
| `/providers/{id}` | PATCH | `providers:update` |
| `/providers/{id}` | DELETE (deactivate) | `providers:update` + approval |
| `/providers/{id}/credentials` | GET/POST | `providers:read`/`providers:update` |
| `/providers/{id}/networks` | GET/POST | `providers:read`/`providers:update` |
| `/providers/{id}/identifiers/{identifierId}/rotate` | POST | `providers:update` |
| `/providers/{id}/archive` | POST | `providers:update` |
| `/providers/{id}/restore` | POST | `providers:update` |
| `/providers/{id}/reactivate` | POST | `providers:update` |
| `/providers/{id}/purge` | POST | `purge:execute` (elevated) |

---

## 10. Organization APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/organizations` | GET (list) | `organizations:read` |
| `/organizations` | POST | `organizations:create` |
| `/organizations/{id}` | GET | `organizations:read` |
| `/organizations/{id}` | PATCH | `organizations:update` |
| `/organizations/{id}` | DELETE (deactivate) | `organizations:update` + approval |
| `/organizations/{id}/contacts` | GET/POST | `organizations:read`/`organizations:update` |
| `/organizations/{id}/identifiers` | GET/POST | `organizations:read`/`organizations:update` |
| `/organizations/{id}/identifiers/{identifierId}/rotate` | POST | `organizations:update` |
| `/organizations/{id}/archive` | POST | `organizations:update` |
| `/organizations/{id}/restore` | POST | `organizations:update` |
| `/organizations/{id}/reactivate` | POST | `organizations:update` |
| `/organizations/{id}/purge` | POST | `purge:execute` (elevated) |

---

## 11. Reference Data APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/reference-categories` | GET | `reference:read` |
| `/reference-categories` | POST | `reference:manage` |
| `/reference-values` | GET | `reference:read` |
| `/reference-values` | POST | `reference:manage` |
| `/reference-values/{id}` | GET/PATCH/DELETE | `reference:read`/`reference:manage` |
| `/lookups` | GET | `reference:read` |
| `/lookups` | POST | `reference:manage` |
| `/countries` | GET | `reference:read` |
| `/clinical-code-sets` | GET | `reference:read` |

---

## 12. Search APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/search/patients` | GET | `patients:read` |
| `/search/staff` | GET | `staff:read` |
| `/search/providers` | GET | `providers:read` |
| `/search/organizations` | GET | `organizations:read` |
| `/search/master` | GET | `masterdata:read` |
| `/enterprise-persons` | GET (list) | `masterdata:read` |
| `/enterprise-persons/{id}` | GET | `masterdata:read` |
| `/enterprise-persons` | POST (link/establish) | `merge:execute` |

| Aspect | Detail |
| --- | --- |
| Identifier | Exact match `identifierType` + `value` |
| Fuzzy | Name/demographic fuzzy ([06-ERD](06-ERD.md) §24) |
| Result | Ranked matches + confidence |
| Scope | Tenant-scoped |

> **Enterprise Person Index (EPI).** `/enterprise-persons` resolves the cross-entity person index (MD-BR-005, [07-Domain-Model](07-Domain-Model.md) §18 `enterprise_person`) that links a person's patient/staff identities; list and query are read-scoped, and link/establish is a merge-family consolidation operation.

---

## 13. Duplicate APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/duplicate-candidates` | GET (list) | `duplicates:read` |
| `/duplicate-candidates/{id}` | GET | `duplicates:read` |
| `/duplicate-candidates/{id}/review` | POST | `duplicates:review` |
| `/duplicate-candidates/{id}/resolve` | POST | `duplicates:review` |
| `/match-rules` | GET | `duplicates:read` |
| `/match-rules` | PATCH | `duplicates:review` |
| `/match-thresholds` | GET/PATCH | `duplicates:read`/`duplicates:review` |

---

## 14. Golden Record APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/golden-records` | GET (list) | `golden:read` |
| `/golden-records/{id}` | GET | `golden:read` |
| `/golden-records/{id}/links` | GET | `golden:read` |
| `/golden-records/{id}/sources` | GET | `golden:read` |
| `/golden-records/{id}` | PATCH | `golden:manage` |

---

## 15. Merge APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/merges` | POST (initiate) | `merge:execute` |
| `/merges/{id}` | GET | `merge:read` |
| `/merges/{id}/approve` | POST | `approval:review` |
| `/merges/{id}/reject` | POST | `approval:review` |
| `/merges/{id}/survivorship` | GET | `merge:read` |
| `/survivorship-rules` | GET | `merge:read` |
| `/survivorship-rules` | PATCH | `golden:manage` |
| `/survivorship-rules/{id}/priority` | PATCH | `golden:manage` |

---

## 16. Unmerge APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/merges/{id}/unmerge` | POST | `unmerge:execute` |
| `/merges/{id}/unmerge/approve` | POST | `approval:review` |

---

## 17. Approval APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/approvals` | GET (queue) | `approval:review` |
| `/approvals/{id}` | GET | `approval:review` |
| `/approvals/{id}/approve` | POST | `approval:review` |
| `/approvals/{id}/reject` | POST | `approval:review` |

---

## 18. Version APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/master-records/{id}/versions` | GET | `masterdata:read` |
| `/master-records/{id}/versions/{vid}` | GET | `masterdata:read` |
| `/master-records/{id}/versions/{vid}/diff` | GET | `masterdata:read` |

---

## 19. Stewardship APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/quality-issues` | GET (list) | `stewardship:manage` |
| `/quality-issues` | POST | `stewardship:manage` |
| `/quality-issues/{id}` | GET/PATCH | `stewardship:manage` |
| `/quality-issues/{id}/tasks` | GET/POST | `stewardship:manage` |
| `/steward-assignments` | GET/POST | `stewardship:manage` |

---

## 20. Import APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/imports` | POST (start) | `import:run` |
| `/imports/{id}` | GET | `import:run` |
| `/imports/{id}/validation` | GET | `import:run` |
| `/imports/{id}/apply` | POST | `import:run` |
| `/imports/{id}/rollback` | POST | `import:run` |

> Lifecycle follows [17-Import-Export](17-Import-Export.md) and [02-Workflow](02-Workflow.md).

---

## 21. Export APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/exports` | POST (start) | `export:run` |
| `/exports/{id}` | GET | `export:run` |
| `/exports/{id}/status` | GET | `export:run` |

---

## 22. Integration APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/integration-endpoints` | GET/POST | `integration:manage` |
| `/integration-maps` | GET/POST | `integration:manage` |
| `/integration-maps/{id}` | PATCH | `integration:manage` |
| `/mapping-fields` | GET/POST | `integration:manage` |
| `/cross-references` | GET/POST | `integration:manage` |

---

## 23. Audit APIs

| Endpoint | Method | Permission |
| --- | --- | --- |
| `/audit` | GET (list) | `audit:read` |
| `/audit/{id}` | GET | `audit:read` |
| `/audit/export` | POST | `audit:read` |

> Audit access is restricted per [07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md) and [13-Audit](13-Audit.md).

---

## 24. Error Model

Follows [11-API-STANDARDS](../../11-API-STANDARDS.md) §11.

| Field | Description |
| --- | --- |
| `code` | Machine-readable error code |
| `message` | Human-readable message |
| `correlationId` | Trace correlation id |
| `details` | Field-level validation errors |

| HTTP | Meaning |
| --- | --- |
| 400 | Validation error |
| 401 | Unauthenticated |
| 403 | Forbidden / out of scope |
| 404 | Not found |
| 409 | Conflict (e.g., duplicate identifier) |
| 422 | Unprocessable (invariant) |
| 429 | Rate limited |
| 5xx | Server error |

---

## 25. Pagination

| Aspect | Decision |
| --- | --- |
| Style | Cursor-based for large lists ([11-API-STANDARDS](../../11-API-STANDARDS.md) §9) |
| Default | Page size 25; max 100 |
| Response | Items + next cursor |
| Consistency | Stable ordering required |

---

## 26. Filtering

| Aspect | Decision |
| --- | --- |
| Fields | Status, entity type, identifier type, severity |
| Syntax | Query params per field |
| Range | Date ranges for audit/version/import |
| Scope | Tenant always applied |

---

## 27. Sorting

| Aspect | Decision |
| --- | --- |
| Default | Creation date desc |
| Allowed | Defined sortable columns |
| Stability | Tie-break by id |

---

## 28. Idempotency

| Aspect | Decision |
| --- | --- |
| Key | `Idempotency-Key` header on writes ([11-API-STANDARDS](../../11-API-STANDARDS.md) §12) |
| Scope | POST/PATCH create + merge/import |
| Replay | Same key returns same result |
| TTL | Bounded window |

---

## 29. Rate Limiting

| Aspect | Decision |
| --- | --- |
| Layer | API gateway ([02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) §6) |
| Basis | Per principal + endpoint |
| Response | `429` with retry-after |
| Exempt | Service-to-service with mTLS where configured |

---

## 30. API Security

| Aspect | Decision |
| --- | --- |
| Transport | TLS only |
| AuthZ | Gateway + service enforcement |
| Validation | Input validated at boundary ([11-API-STANDARDS](../../11-API-STANDARDS.md) §13) |
| PHI | No PHI in tokens/logs |
| CORS | Restricted origins |
| Secrets | Not exposed via API |

---

## 31. API Observability

| Aspect | Decision |
| --- | --- |
| Logging | Structured JSON with correlation id |
| Metrics | Latency, error rate, throughput ([14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md)) |
| Tracing | Distributed tracing ([03-TECHNOLOGY-STACK](../../03-TECHNOLOGY-STACK.md)) |
| Contract | OpenAPI served + monitored |

---

## 32. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [11-API-STANDARDS](../../11-API-STANDARDS.md) | API standard | Consumes |
| [01-Business-Requirements](01-Business-Requirements.md) | Capabilities | Consumes |
| [02-Workflow](02-Workflow.md) | Workflows | Consumes |
| [11-Permissions](11-Permissions.md) | Authorization | Consumes |
| [13-Audit](13-Audit.md) | Audit | Consumes |
| [17-Import-Export](17-Import-Export.md) | Import/export | Consumes |
| [18-Integrations](18-Integrations.md) | Integrations | Consumes |
| [06-AUTHENTICATION](../../06-AUTHENTICATION.md) | AuthN | Consumes |
| [07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md) | AuthZ | Consumes |
| [09-MULTI-TENANCY](../../09-MULTI-TENANCY.md) | Tenancy | Consumes |
| [Hospital Setup](../hospital-setup/README.md) | Shared API | Consumes |

---

*End of `docs/modules/master-data/10-API.md`.*
