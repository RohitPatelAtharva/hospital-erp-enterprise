# Master Data Module — Integrations

> **Document ID:** `master-data/18-Integrations`
> **Owner:** Integration / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate.
>
> **Relationship:** This document defines **integrations** for the Master Data Management module — internal (module-to-module) and external (standard-based) integration. It follows [18-INTEROPERABILITY](../../18-INTEROPERABILITY.md) and [02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) §12. Interoperability standards are marked **planned** unless existing documentation confirms implementation — nothing is claimed as implemented without support.

---

## Table of Contents

1. [Integration Overview](#1-integration-overview)
2. [Integration Principles](#2-integration-principles)
3. [Internal Integrations](#3-internal-integrations)
4. [Hospital Setup](#4-hospital-setup)
5. [IAM](#5-iam)
6. [Patient Registration](#6-patient-registration)
7. [EHR](#7-ehr)
8. [Scheduling](#8-scheduling)
9. [Billing](#9-billing)
10. [Pharmacy](#10-pharmacy)
11. [Laboratory](#11-laboratory)
12. [Radiology](#12-radiology)
13. [External Integrations](#13-external-integrations)
14. [API Gateway](#14-api-gateway)
15. [Event Bus](#15-event-bus)
16. [HL7 v2](#16-hl7-v2)
17. [FHIR](#17-fhir)
18. [DICOM applicability](#18-dicom-applicability)
19. [ICD applicability](#19-icd-applicability)
20. [SNOMED applicability](#20-snomed-applicability)
21. [LOINC applicability](#21-loinc-applicability)
22. [ABDM applicability](#22-abdm-applicability)
23. [NABH](#23-nabh)
24. [LDAP/OIDC](#24-ldapoidc)
25. [Email/SMS/WhatsApp](#25-emailsmswhatsapp)
26. [Queue/Retry](#26-queueretry)
27. [Error Handling](#27-error-handling)
28. [Security](#28-security)
29. [Audit](#29-audit)
30. [Monitoring](#30-monitoring)
31. [Cross References](#31-cross-references)

---

## 1. Integration Overview

The Master Data module integrates with other platform modules (providing identity) and with external systems (exchanging data). All integration is contract-first, event-driven, and audited.

---

## 2. Integration Principles

| # | Principle | Application |
| --- | --- | --- |
| IN-01 | Single source of truth | Master data is the source of identity |
| IN-02 | Event-driven | Asynchronous via event bus ([02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) §12) |
| IN-03 | Contract-first | OpenAPI-defined ([18-INTEROPERABILITY](../../18-INTEROPERABILITY.md)) |
| IN-04 | Tenant-scoped | No cross-tenant leakage |
| IN-05 | Idempotent | Retry-safe |
| IN-06 | Audited | All exchanges audited |

---

## 3. Internal Integrations

| Partner | Boundary | Direction |
| --- | --- | --- |
| Hospital Setup | Staff + facility reference | Consumes/Provides |
| IAM | Staff identity | Provides |
| Patient Registration | Patient identity | Provides |
| EHR | Patient identity, cross-ref | Provides |
| Scheduling | Patient identity | Provides |
| Billing | Patient/org identity | Provides |
| Pharmacy | Patient/org identity | Provides |
| Laboratory | Patient/org identity | Provides |
| Radiology | Patient/org identity | Provides |

---

## 4. Hospital Setup

Master data **references** — never duplicates — Hospital Setup's facility hierarchy ([06-ERD](06-ERD.md) §21). `staff` provides the master for Hospital Setup `staff_assignment`; `facility_reference`/`department_reference`/`unit_reference` mirror the hierarchy.

---

## 5. IAM

Staff master feeds IAM identity; IAM provides authentication/authorization ([06-AUTHENTICATION](../../06-AUTHENTICATION.md)). Identity data is not duplicated.

---

## 6. Patient Registration

Patient master provides canonical patient identity for registration workflows. Registration events synchronize via the event bus.

---

## 7. EHR

Patient identity and cross-references are shared with the EHR. Clinical data remains owned by the EHR module.

---

## 8. Scheduling

Patient identity is provided to scheduling for appointments ([06-ERD](06-ERD.md) §21).

---

## 9. Billing

Patient and organization identity are provided to billing/finance.

---

## 10. Pharmacy

Patient and organization identity are provided to pharmacy.

---

## 11. Laboratory

Patient and organization identity are provided to laboratory.

---

## 12. Radiology

Patient and organization identity are provided to radiology.

---

## 13. External Integrations

External integration is via the API gateway and event bus. **Interoperability standards are planned**, not yet implemented (per [18-INTEROPERABILITY](../../18-INTEROPERABILITY.md) and the master-data roadmap [24-Future-Roadmap](24-Future-Roadmap.md)).

---

## 14. API Gateway

All external access goes through the gateway ([02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) §6) with authN/Z, rate limiting, and routing ([10-API](10-API.md) §29–§30).

---

## 15. Event Bus

Kafka-based ([03-TECHNOLOGY-STACK](../../03-TECHNOLOGY-STACK.md)); master-data events (`md.*`) published for consumers ([07-Domain-Model](07-Domain-Model.md) §8).

---

## 16. HL7 v2

> **Status:** ⚠️ Planned. HL7 v2 interfaces for patient/admission messages are part of the interoperability roadmap ([18-INTEROPERABILITY](../../18-INTEROPERABILITY.md)); not implemented in Phase 1.

---

## 17. FHIR

> **Status:** ⚠️ Planned. FHIR-based patient/identity exchange is the interoperability direction ([18-INTEROPERABILITY](../../18-INTEROPERABILITY.md)); not implemented in Phase 1.

---

## 18. DICOM applicability

> **Status:** ⚠️ Not applicable to master data identity. DICOM belongs to imaging (Radiology module); master data provides patient identity to it.

---

## 19. ICD applicability

> **Status:** ⚠️ Applicability only. ICD is a clinical code system ([19-CLINICAL-STANDARDS](../../19-CLINICAL-STANDARDS.md)); master data models code sets/terminology ([06-ERD](06-ERD.md) §11) but does not claim ICD ingestion in Phase 1.

---

## 20. SNOMED applicability

> **Status:** ⚠️ Applicability only. SNOMED CT is a clinical terminology; master data terminology tables can host it as reference data, but ingestion is not claimed.

---

## 21. LOINC applicability

> **Status:** ⚠️ Applicability only. LOINC is a lab terminology; master data terminology tables can host it, but ingestion is not claimed.

---

## 22. ABDM applicability

> **Status:** ⚠️ Planned. ABDM national health interoperability (consent manager, ABHA linkage) is planned per [20-COMPLIANCE](../../20-COMPLIANCE.md) §6 and the roadmap ([24-Future-Roadmap](24-Future-Roadmap.md) §14); not implemented in Phase 1.

---

## 23. NABH

NABH accreditation is supported by governance/audit evidence ([20-COMPLIANCE](../../20-COMPLIANCE.md) §5); master data contributes documented structure.

---

## 24. LDAP/OIDC

Identity integration via OIDC/LDAP for staff directory ([06-AUTHENTICATION](../../06-AUTHENTICATION.md) §4).

---

## 25. Email/SMS/WhatsApp

Notification delivery via [14-Notifications](14-Notifications.md) channels.

---

## 26. Queue/Retry

| Aspect | Detail |
| --- | --- |
| Transport | Kafka ([03-TECHNOLOGY-STACK](../../03-TECHNOLOGY-STACK.md)) |
| Retry | Bounded, exponential ([17-Import-Export](17-Import-Export.md) §16) |
| Idempotency | Consumer-side dedupe |
| Dead-letter | DLQ for failed |

---

## 27. Error Handling

| Aspect | Detail |
| --- | --- |
| Non-blocking | Consumer failures isolated |
| Alert | Failures surfaced ([14-Notifications](14-Notifications.md)) |
| Replay | Event replay from outbox |

---

## 28. Security

| Aspect | Decision |
| --- | --- |
| Transport | TLS/mTLS |
| Auth | Service-to-service credentials ([12-Security](12-Security.md) §3) |
| Tenant | Message tenant-scoped |
| PHI | No PHI in event payloads where avoidable; encrypted exchange |

---

## 29. Audit

All integration exchanges are audited ([13-Audit](13-Audit.md) §12) — endpoint, payload summary, outcome.

---

## 30. Monitoring

| Metric | Detail |
| --- | --- |
| Health | Endpoint status ([16-Dashboards](16-Dashboards.md) §14) |
| Latency | Exchange time |
| Errors | Failure rate |
| Backlog | Queue depth |

---

## 31. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [18-INTEROPERABILITY](../../18-INTEROPERABILITY.md) | Interop standard | Consumes |
| [02-SYSTEM-ARCHITECTURE](../../02-SYSTEM-ARCHITECTURE.md) | Architecture | Consumes |
| [06-ERD](06-ERD.md) | Cross-module ERD | Consumes |
| [10-API](10-API.md) | API | Consumes |
| [17-Import-Export](17-Import-Export.md) | Data exchange | Consumes |
| [13-Audit](13-Audit.md) | Audit | Consumes |
| [12-Security](12-Security.md) | Security | Consumes |
| [14-Notifications](14-Notifications.md) | Notifications | Consumes |
| [20-COMPLIANCE](../../20-COMPLIANCE.md) | Compliance | Consumes |
| [Hospital Setup](../hospital-setup/README.md) | Staff/facility | Consumes |

---

*End of `docs/modules/master-data/18-Integrations.md`.*
