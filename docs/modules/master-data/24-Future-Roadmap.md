# Master Data Module — Future Roadmap

> **Document ID:** `master-data/24-Future-Roadmap`
> **Owner:** Product / Program Lead
> **Status:** ✅ Approved / Living
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed every release cycle and at every phase gate.
>
> **Relationship:** This document defines the **future roadmap** for the Master Data Management module — deferred and planned capabilities beyond the initial scope. It extends the platform roadmap ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md)) and the module business requirements ([01-Business-Requirements](01-Business-Requirements.md)).

---

## Table of Contents

1. [Future Roadmap Overview](#1-future-roadmap-overview)
2. [Scope of This Document](#2-scope-of-this-document)
3. [Prioritization](#3-prioritization)
4. [Future Enhancements](#4-future-enhancements)
5. [Advanced Analytics](#5-advanced-analytics)
6. [Patient Portal & Self-Service](#6-patient-portal--self-service)
7. [Health Information Exchange](#7-health-information-exchange)
8. [Interoperability Standards](#8-interoperability-standards)
9. [Clinical Coding & Terminology](#9-clinical-coding--terminology)
10. [Multi-Facility / Multi-Tenant](#10-multi-facility--multi-tenant)
11. [AI & Automation](#11-ai--automation)
12. [Localization](#12-localization)
13. [A/B Testing](#13-ab-testing)
14. [ABDM & National Interoperability](#14-abdm--national-interoperability)
15. [Mobile Apps](#15-mobile-apps)
16. [Enhanced Security](#16-enhanced-security)
17. [Scale & Performance](#17-scale--performance)
18. [Backlog Governance](#18-backlog-governance)
19. [Cross References](#19-cross-references)

---

## 1. Future Roadmap Overview

The Master Data module is delivered incrementally. This document records **planned and deferred** capabilities that are out of initial scope but retained on the backlog, each with a trigger and dependency.

---

## 2. Scope of This Document

| In scope | Deferred |
| --- | --- |
| Planned future capabilities | Not-yet-committed ideas |
| Triggers + dependencies | Placeholders only |

> Capabilities here are **not** in the Phase 1 build and do not carry implementation claims.

---

## 3. Prioritization

| Priority | Definition |
| --- | --- |
| High | Near-term backlog, clear value |
| Medium | Valued, dependency-gated |
| Low | Long-horizon |

---

## 4. Future Enhancements

From [01-Business-Requirements](01-Business-Requirements.md) §18 (FR-01…FR-14).

| ID | Enhancement | Priority | Trigger |
| --- | --- | --- | --- |
| FR-01 | Advanced analytics & BI | High | Phase 7+ reporting maturity |
| FR-02 | Patient portal self-service | Medium | Phase 6 |
| FR-03 | HIE (health information exchange) | Medium | Interop maturity |
| FR-04 | FHIR-based exchange | Medium | Interop maturity |
| FR-05 | HL7 interfaces | Medium | Interop maturity |
| FR-06 | WHO-ART / clinical coding | Low | Clinical roadmap |
| FR-07 | Clinical data enhancements | Low | Clinical roadmap |
| FR-08 | DICOM / imaging integration | Low | Radiology roadmap |
| FR-09 | NLP / medical image analysis | Low | AI roadmap |
| FR-10 | Multi-facility / multi-tenant | High | Enterprise rollout |
| FR-11 | Localization (i18n) | Medium | Global rollout |
| FR-12 | A/B testing | Low | UX maturity |
| FR-13 | ICD / SNOMED / LOINC ingestion | Medium | Clinical roadmap |
| FR-14 | ABDM national interoperability | Medium | National compliance |

---

## 5. Advanced Analytics

| Capability | Detail |
| --- | --- |
| Predictive quality | Predict duplicate/quality risk |
| Trend analytics | Deep time-series ([16-Dashboards](16-Dashboards.md)) |
| BI export | Warehouse integration |
| Dependency | Phase 7+ |

---

## 6. Patient Portal & Self-Service

| Capability | Detail |
| --- | --- |
| Patient identity self-service | Portal-managed profile |
| Consent management | Self-service consent |
| Status | Medium, Phase 6 |

---

## 7. Health Information Exchange

| Capability | Detail |
| --- | --- |
| HIE connectivity | Regional data exchange |
| Identity resolution | Cross-org patient matching |
| Dependency | Interop maturity |

---

## 8. Interoperability Standards

| Capability | Detail |
| --- | --- |
| FHIR | Resource-based exchange ([18-Integrations](18-Integrations.md) §17) |
| HL7 v2 | Legacy ADT messages (§16) |
| Status | Planned, not Phase 1 |

---

## 9. Clinical Coding & Terminology

| Capability | Detail |
| --- | --- |
| ICD / SNOMED / LOINC | Terminology ingestion |
| WHO-ART | Adverse-reaction coding |
| Mapping | Reference/terminology tables ([06-ERD](06-ERD.md) §11) |

---

## 10. Multi-Facility / Multi-Tenant

| Capability | Detail |
| --- | --- |
| Active multi-facility | Beyond model-ready ([09-MULTI-TENANCY](../../09-MULTI-TENANCY.md)) |
| Cross-facility assignments | Extend [06-ERD](06-ERD.md) §21 |
| Priority | High at enterprise rollout |

---

## 11. AI & Automation

| Capability | Detail |
| --- | --- |
| AI-assisted dedup | ML duplicate scoring |
| NLP extraction | Auto-demographics |
| Image analysis | Medical image understanding |
| Dependency | Data + governance readiness |

---

## 12. Localization

| Capability | Detail |
| --- | --- |
| i18n | UI/notification localization ([14-Notifications](14-Notifications.md) §8) |
| Multi-currency/date | Formatting |
| Dependency | Global rollout |

---

## 13. A/B Testing

| Capability | Detail |
| --- | --- |
| UX experiments | Controlled UI variants ([09-UX](09-UX.md)) |
| Analytics | Measurement |
| Status | Low priority |

---

## 14. ABDM & National Interoperability

| Capability | Detail |
| --- | --- |
| ABHA linkage | National health ID ([20-COMPLIANCE](../../20-COMPLIANCE.md) §6) |
| Consent manager | National consent |
| Status | Planned, not Phase 1 ([18-Integrations](18-Integrations.md) §22) |

---

## 15. Mobile Apps

| Capability | Detail |
| --- | --- |
| Mobile approval | Approve on mobile ([08-UI](08-UI.md) §26) |
| Steward mobile | Review on the go |
| Status | Aligns to platform mobile roadmap |

---

## 16. Enhanced Security

| Capability | Detail |
| --- | --- |
| Advanced biometrics | Staff mobile auth ([06-AUTHENTICATION](../../06-AUTHENTICATION.md) §7) |
| Zero-trust hardening | Continuous ([12-Security](12-Security.md)) |

---

## 17. Scale & Performance

| Capability | Detail |
| --- | --- |
| Sharding | Horizontal scale ([19-Performance](19-Performance.md) §7) |
| Read replicas | Read scaling ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §4) |
| Partitioning | High-volume tables (§8) |
| Trigger | Growth + load tests (Phase 11) |

---

## 18. Backlog Governance

| Aspect | Detail |
| --- | --- |
| Source | BRS future enhancements + roadmap |
| Triaging | At phase gates |
| Approval | New scope is an ADR ([23-Changelog](23-Changelog.md) §6) |
| Traceability | Links to requirements ([README](README.md) §2) |

---

## 19. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) | Platform roadmap | Consumes |
| [01-Business-Requirements](01-Business-Requirements.md) | FR catalog | Consumes |
| [18-Integrations](18-Integrations.md) | Interop | Consumes |
| [20-COMPLIANCE](../../20-COMPLIANCE.md) | Compliance | Consumes |
| [09-MULTI-TENANCY](../../09-MULTI-TENANCY.md) | Tenancy | Consumes |
| [19-Performance](19-Performance.md) | Scale | Consumes |
| [23-Changelog](23-Changelog.md) | ADR | Consumes |

---

*End of `docs/modules/master-data/24-Future-Roadmap.md`.*
