# Master Data Module — Risks & Mitigations

> **Document ID:** `master-data/22-Risks`
> **Owner:** Program / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate and when risks change.
>
> **Relationship:** This document defines the **risk register** for the Master Data Management module. It feeds the platform risk register in [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §14.

---

## Table of Contents

1. [Risk Management Approach](#1-risk-management-approach)
2. [Risk Definitions](#2-risk-definitions)
3. [Risk Scoring](#3-risk-scoring)
4. [Risk Register](#4-risk-register)
5. [Data Quality Risks](#5-data-quality-risks)
6. [Duplicate/Merge Risks](#6-duplicatemerge-risks)
7. [Security Risks](#7-security-risks)
8. [Compliance Risks](#8-compliance-risks)
9. [Integration Risks](#9-integration-risks)
10. [Performance Risks](#10-performance-risks)
11. [Operational Risks](#11-operational-risks)
12. [Migration Risks](#12-migration-risks)
13. [Mitigation Strategies](#13-mitigation-strategies)
14. [Contingency](#14-contingency)
15. [Assumptions & Dependencies](#15-assumptions--dependencies)
16. [Risk Owners](#16-risk-owners)
17. [Risk Reviews](#17-risk-reviews)
18. [Cross References](#18-cross-references)

---

## 1. Risk Management Approach

The module identifies, scores, and mitigates risks using the platform approach ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §14). Each risk has an owner, mitigation, and review cadence.

---

## 2. Risk Definitions

| Term | Definition |
| --- | --- |
| Likelihood | Probability of occurrence |
| Impact | Severity of consequence |
| Exposure | Likelihood × Impact |
| Mitigation | Planned control |
| Contingency | Response if realized |

---

## 3. Risk Scoring

| Score | Likelihood / Impact |
| --- | --- |
| Low | Unlikely / minor |
| Medium | Possible / moderate |
| High | Likely / major |
| Critical | Almost certain / severe |

---

## 4. Risk Register

| ID | Risk | Likelihood | Impact | Mitigation | Owner |
| --- | --- | --- | --- | --- | --- |
| RK-01 | Duplicate records go undetected | Medium | High | Detection pipeline + thresholds ([06-ERD](06-ERD.md) §12) | Registry Admin |
| RK-02 | Incorrect merge causes data loss | Low | Critical | Approval + audit + reversible ([11-Permissions](11-Permissions.md) §20) | Registry Admin |
| RK-03 | Cross-tenant data leak | Low | Critical | RLS + tenant scope ([12-Security](12-Security.md) §5) | Security |
| RK-04 | PHI exposure via export | Medium | High | Export authZ + de-identify ([17-Import-Export](17-Import-Export.md) §21) | Security |
| RK-05 | Identifier collision | Medium | High | Unique constraints + MPI ([06-ERD](06-ERD.md) §13) | Registry Admin |
| RK-06 | Golden record drift | Medium | Medium | Survivorship + audit ([13-Audit](13-Audit.md) §9) | Data Steward |
| RK-07 | Import corrupts canonical data | Medium | High | Staging + validation + approval ([17-Import-Export](17-Import-Export.md)) | Engineering |
| RK-08 | Integration inconsistency | Medium | Medium | Event-driven sync + monitoring ([18-Integrations](18-Integrations.md)) | Integration |
| RK-09 | Search performance degradation | Medium | Medium | Indexing + caching ([19-Performance](19-Performance.md)) | Engineering |
| RK-10 | Unauthorized elevation | Low | High | MFA + SoD ([11-Permissions](11-Permissions.md) §20–§21) | Security |
| RK-11 | Compliance/retention non-compliance | Medium | High | Retention + audit ([13-Audit](13-Audit.md) §17) | Compliance |
| RK-12 | Migration data loss | Medium | Critical | Migration tests + backup ([21-Deployment](21-Deployment.md) §12) | Engineering |

---

## 5. Data Quality Risks

| Risk | Mitigation |
| --- | --- |
| Incomplete records | Validation + completeness reports ([15-Reports](15-Reports.md) §5) |
| Inconsistent values | Reference data + normalization |
| Stale data | Timeliness metrics ([16-Dashboards](16-Dashboards.md) §13) |
| Duplicate drift | Duplicate pipeline |

---

## 6. Duplicate/Merge Risks

| Risk | Mitigation |
| --- | --- |
| False positives | Threshold tuning + review |
| False negatives | Detection breadth |
| Data loss on merge | Survivorship + reversible + audit |
| Approval bypass | SoD + MFA ([11-Permissions](11-Permissions.md) §20) |

---

## 7. Security Risks

| Risk | Mitigation |
| --- | --- |
| Unauthorized access | AuthZ + RLS ([12-Security](12-Security.md)) |
| Injection | Parameterized + validation |
| Token theft | Short-lived + rotation |
| Insider misuse | SoD + MFA + audit |

---

## 8. Compliance Risks

| Risk | Mitigation |
| --- | --- |
| PHI retention violation | Retention schedule ([13-Audit](13-Audit.md) §17) |
| Audit gap | Completeness testing ([20-Testing](20-Testing.md) §18) |
| Consent non-compliance | Consent gating |
| Regulatory change | Gate reviews ([20-COMPLIANCE](../../20-COMPLIANCE.md)) |

---

## 9. Integration Risks

| Risk | Mitigation |
| --- | --- |
| Event loss | Outbox + DLQ ([18-Integrations](18-Integrations.md) §26) |
| Stale sync | Monitoring + replay |
| Contract drift | Contract-first + versioning |
| External outage | Retry + escalation |

---

## 10. Performance Risks

| Risk | Mitigation |
| --- | --- |
| Query degradation | Indexing + EXPLAIN ([19-Performance](19-Performance.md)) |
| Import backlog | Batching + backpressure |
| Search latency | OpenSearch + caching |
| Load spikes | Auto-scaling + capacity plan |

---

## 11. Operational Risks

| Risk | Mitigation |
| --- | --- |
| Deployment failure | Rollback + drills ([21-Deployment](21-Deployment.md) §13) |
| Backup failure | Verified restore tests |
| Outage | HA + runbooks |
| Staff turnover | Documentation + runbooks |

---

## 12. Migration Risks

| Risk | Mitigation |
| --- | --- |
| Schema drift | Migration CI gate ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §5) |
| Data mapping error | Transformation validation |
| Downtime | Rolling deploy + PITR |
| Lost data | Backup + restore drill |

---

## 13. Mitigation Strategies

| Strategy | Application |
| --- | --- |
| Prevention | Validation, authZ, constraints |
| Detection | Monitoring, alerts, audit |
| Correction | Reversible ops, rollback |
| Recovery | Backup, DR, runbooks |

---

## 14. Contingency

| Scenario | Response |
| --- | --- |
| Critical data loss | Restore + incident ([21-Deployment](21-Deployment.md) §24) |
| Security breach | IR + notification ([20-COMPLIANCE](../../20-COMPLIANCE.md)) |
| Regulatory finding | Remediation plan |
| Integration outage | Degrade + escalate ([14-Notifications](14-Notifications.md)) |

---

## 15. Assumptions & Dependencies

| Item | Detail |
| --- | --- |
| Single facility first | Multi-facility model-ready ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §3.2) |
| Interop planned | FHIR/HL7 not implemented Phase 1 ([18-Integrations](18-Integrations.md)) |
| IAM prerequisite | Identity ready before features ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md)) |
| Registry external | Staff master external ([06-ERD](06-ERD.md) §21) |

---

## 16. Risk Owners

| Risk class | Owner |
| --- | --- |
| Data quality | Data Steward lead |
| Security | Security lead |
| Compliance | Compliance lead |
| Integration | Integration lead |
| Performance | Engineering lead |
| Operations | DevOps lead |

---

## 17. Risk Reviews

| Aspect | Detail |
| --- | --- |
| Cadence | Every phase gate ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md)) |
| Trigger | New/changed risk |
| Update | Register maintained |
| Report | To program risk register |

---

## 18. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) | Platform risks | Consumes |
| [12-Security](12-Security.md) | Security | Consumes |
| [13-Audit](13-Audit.md) | Audit | Consumes |
| [17-Import-Export](17-Import-Export.md) | Data exchange | Consumes |
| [18-Integrations](18-Integrations.md) | Integrations | Consumes |
| [21-Deployment](21-Deployment.md) | Deployment | Consumes |
| [20-COMPLIANCE](../../20-COMPLIANCE.md) | Compliance | Consumes |
| [11-Permissions](11-Permissions.md) | AuthZ | Consumes |

---

*End of `docs/modules/master-data/22-Risks.md`.*
