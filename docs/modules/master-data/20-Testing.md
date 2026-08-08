# Master Data Module — Testing

> **Document ID:** `master-data/20-Testing`
> **Owner:** QA / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate.
>
> **Relationship:** This document defines **testing** for the Master Data Management module — levels, scenarios, automation, and gates. It follows [15-TESTING-STANDARDS](../../15-TESTING-STANDARDS.md) and [04-CODING-STANDARDS](../../04-CODING-STANDARDS.md) §8.

---

## Table of Contents

1. [Testing Overview](#1-testing-overview)
2. [Testing Objectives](#2-testing-objectives)
3. [Testing Levels](#3-testing-levels)
4. [Test Pyramid](#4-test-pyramid)
5. [Functional Scenarios](#5-functional-scenarios)
6. [Unit Testing](#6-unit-testing)
7. [Integration Testing](#7-integration-testing)
8. [Component Testing](#8-component-testing)
9. [System Testing](#9-system-testing)
10. [UAT](#10-uat)
11. [Registry Testing](#11-registry-testing)
12. [Duplicate Testing](#12-duplicate-testing)
13. [Golden Record Testing](#13-golden-record-testing)
14. [Merge / Unmerge Testing](#14-merge--unmerge-testing)
15. [Survivorship Testing](#15-survivorship-testing)
16. [Authorization Testing](#16-authorization-testing)
17. [Security Testing](#17-security-testing)
18. [Audit Testing](#18-audit-testing)
19. [Import Testing](#19-import-testing)
20. [Export Testing](#20-export-testing)
21. [API Testing](#21-api-testing)
22. [UI Testing](#22-ui-testing)
23. [Notifications Testing](#23-notifications-testing)
24. [Performance Testing](#24-performance-testing)
25. [Migration & Data](#25-migration--data)
26. [CI Testing Gates](#26-ci-testing-gates)
27. [Test Data](#27-test-data)
28. [Cross References](#28-cross-references)

---

## 1. Testing Overview

This document defines a comprehensive, automated testing strategy for the Master Data module, proportionate to risk. It covers all module capabilities and enforces quality gates before merge.

---

## 2. Testing Objectives

| Objective | Detail |
| --- | --- |
| Correctness | Capabilities behave as designed ([01-Business-Requirements](01-Business-Requirements.md)) |
| Safety | No silent data loss, reversible merge |
| Authorization | Permission enforcement verified |
| Compliance | Audit integrity verified |
| Confidence | Critical paths covered ≥ 80% |

---

## 3. Testing Levels

| Level | Scope |
| --- | --- |
| Unit | Single functions/services |
| Integration | Boundaries (DB, API, queue) |
| Component | Module end-to-end |
| System | Whole platform |
| UAT | Business acceptance |

---

## 4. Test Pyramid

Many unit → fewer integration → fewest E2E, proportionate to risk ([15-TESTING-STANDARDS](../../15-TESTING-STANDARDS.md) §3). Critical paths (duplicate, merge, audit) are integration-tested.

---

## 5. Functional Scenarios

Scenarios derive from [01-Business-Requirements](01-Business-Requirements.md) and [02-Workflow](02-Workflow.md).

| # | Scenario |
| --- | --- |
| F-01 | Create a patient with duplicate prevention |
| F-02 | Update a record and version it |
| F-03 | Deactivate with active references blocked |
| F-04 | Detect and triage a duplicate |
| F-05 | Establish a golden record |
| F-06 | Merge two records with approval |
| F-07 | Unmerge a merged pair |
| F-08 | Apply survivorship rules |
| F-09 | Import with validation + approval |
| F-10 | Export a scoped dataset |
| F-11 | Review an approval decision |
| F-12 | Archive and restore a record |

---

## 6. Unit Testing

| Aspect | Detail |
| --- | --- |
| Coverage | Domain services ([07-Domain-Model](07-Domain-Model.md) §7) |
| Deterministic | No wall-clock/network/PHI ([04-CODING-STANDARDS](../../04-CODING-STANDARDS.md) §8) |
| Naming | `Method_Condition_ExpectedResult` |
| Isolation | Mocks at boundaries |

---

## 7. Integration Testing

| Aspect | Detail |
| --- | --- |
| DB | Real schema, migrations ([15-TESTING-STANDARDS](../../15-TESTING-STANDARDS.md) §7) |
| API | Contract + authN/Z |
| Queue | Event publish/consume |
| Outbox | Transactional integrity |

---

## 8. Component Testing

| Aspect | Detail |
| --- | --- |
| Scope | Module end-to-end |
| Environment | Test DB + dependencies |
| Contract | OpenAPI verified |

---

## 9. System Testing

Cross-module flows (master data + Hospital Setup + IAM) against staging-like environment ([21-Deployment](21-Deployment.md) §5).

---

## 10. UAT

| Aspect | Detail |
| --- | --- |
| Participants | SME (registrars, stewards, approvers) |
| Data | Synthetic ([27](#27-test-data)) |
| Scenarios | Business journeys ([09-UX](09-UX.md) §3) |
| Sign-off | Phase gate exit |

---

## 11. Registry Testing

| Test | Detail |
| --- | --- |
| CRUD | Patient/staff/provider/org lifecycle |
| Validation | Field rules ([10-API](10-API.md) §24) |
| Versioning | History + diff |
| Search | Exact + fuzzy |

---

## 12. Duplicate Testing

| Test | Detail |
| --- | --- |
| Detection | True/false positives |
| Scoring | Rule confidence |
| Thresholds | Boundary behavior |
| Review | Resolve/dismiss paths |
| Batch | Candidate queue |

---

## 13. Golden Record Testing

| Test | Detail |
| --- | --- |
| Establish | Source selection |
| Link | Add/remove sources |
| Read | Authoritative view |
| Audit | Provenance captured |

---

## 14. Merge / Unmerge Testing

| Test | Detail |
| --- | --- |
| Merge | Records consolidated correctly |
| Approval | SoD + MFA enforced ([11-Permissions](11-Permissions.md) §20) |
| Reversible | Unmerge restores state |
| Idempotent | No double-apply |
| Audit | Full trail ([13-Audit](13-Audit.md) §8) |

---

## 15. Survivorship Testing

| Test | Detail |
| --- | --- |
| Rules | Winning attribute resolution |
| Priority | Attribute ordering ([06-ERD](06-ERD.md) §15) |
| Edge | Missing/equal values |
| Preview | Pre-merge accuracy |

---

## 16. Authorization Testing

| Test | Detail |
| --- | --- |
| Positive | Authorized roles succeed |
| Negative | Unauthorized denied |
| Scope | Out-of-scope denied |
| SoD | Requester ≠ approver |
| MFA | Elevated requires MFA |
| Matrix | Matches [11-Permissions](11-Permissions.md) §6 |

---

## 17. Security Testing

| Test | Detail |
| --- | --- |
| SAST | Static scan in CI |
| DAST | Dynamic pre-release |
| Injection | SQL/NoSQL/XSS |
| Tenant | Cross-tenant blocked ([12-Security](12-Security.md) §5) |
| PHI | No PHI in logs/tokens |
| Threat model | Re-check at gates |

---

## 18. Audit Testing

| Test | Detail |
| --- | --- |
| Completeness | Every mutation audited |
| Immutability | No tamper |
| Integrity | Hash chain verified ([13-Audit](13-Audit.md) §14) |
| Redaction | No secrets/PHI |
| Query | Filter/paginate correct |

---

## 19. Import Testing

| Test | Detail |
| --- | --- |
| Format | CSV/JSON valid + invalid |
| Validation | Format/schema/data ([17-Import-Export](17-Import-Export.md) §6–§8) |
| Staging | No premature canonical write |
| Approval | Elevated apply enforced |
| Rollback | Staging/apply rollback |
| Idempotency | Re-run safe |

---

## 20. Export Testing

| Test | Detail |
| --- | --- |
| Scope | Correct records |
| Format | Correct output |
| PHI | De-identification where required |
| Delivery | Recipient delivery + audit |
| Large | Streaming/batch correct |

---

## 21. API Testing

| Test | Detail |
| --- | --- |
| Contract | OpenAPI conformance ([10-API](10-API.md)) |
| Errors | Error envelope ([10-API](10-API.md) §24) |
| Pagination | Cursor correctness |
| Idempotency | Key replay |
| Rate limit | 429 behavior |
| AuthN/Z | All endpoints |

---

## 22. UI Testing

| Test | Detail |
| --- | --- |
| Component | Design-system components |
| Screen | Screen behavior ([08-UI](08-UI.md)) |
| E2E | Journeys ([09-UX](09-UX.md) §3) |
| Accessibility | WCAG AA ([12-UI-UX-GUIDELINES](../../12-UI-UX-GUIDELINES.md) §14) |
| Responsive | Breakpoints ([08-UI](08-UI.md) §26) |

---

## 23. Notifications Testing

| Test | Detail |
| --- | --- |
| Delivery | All channels ([14-Notifications](14-Notifications.md) §6) |
| Retry | Max attempts + backoff |
| Idempotent | No duplicates |
| Escalation | SLA-driven |
| PHI | No PHI in bodies |

---

## 24. Performance Testing

| Test | Detail |
| --- | --- |
| Load | Volume per [19-Performance](19-Performance.md) |
| Stress | Peak/saturation |
| Soak | Long-run stability |
| Search | Query latency |
| Import | Throughput ≥ 1000 rows/min |

---

## 25. Migration & Data

| Test | Detail |
| --- | --- |
| Migrations | Clean apply, idempotent ([15-TESTING-STANDARDS](../../15-TESTING-STANDARDS.md) §7) |
| Seed | Reference seed correct |
| Data quality | Completeness/validity checks |

---

## 26. CI Testing Gates

| Gate | Requirement |
| --- | --- |
| Unit | Pass + coverage threshold |
| Integration | Critical paths pass |
| Contract | OpenAPI valid |
| SAST | No high/critical |
| Coverage | Critical ≥ 80% ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §13) |

---

## 27. Test Data

| Aspect | Detail |
| --- | --- |
| Synthetic | No real PHI ([15-TESTING-STANDARDS](../../15-TESTING-STANDARDS.md) §8) |
| Deterministic | Stable fixtures |
| Volume | Representative for perf |
| Environments | Test/QA isolated ([21-Deployment](21-Deployment.md) §5) |

---

## 28. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [15-TESTING-STANDARDS](../../15-TESTING-STANDARDS.md) | Standard | Consumes |
| [04-CODING-STANDARDS](../../04-CODING-STANDARDS.md) | Coding | Consumes |
| [01-Business-Requirements](01-Business-Requirements.md) | Scenarios | Consumes |
| [02-Workflow](02-Workflow.md) | Workflows | Consumes |
| [10-API](10-API.md) | API | Consumes |
| [11-Permissions](11-Permissions.md) | AuthZ tests | Consumes |
| [13-Audit](13-Audit.md) | Audit tests | Consumes |
| [17-Import-Export](17-Import-Export.md) | Data exchange | Consumes |
| [19-Performance](19-Performance.md) | Performance | Consumes |
| [21-Deployment](21-Deployment.md) | Environments | Consumes |

---

*End of `docs/modules/master-data/20-Testing.md`.*
