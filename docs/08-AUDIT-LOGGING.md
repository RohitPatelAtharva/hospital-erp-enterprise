# Hospital ERP Enterprise — Audit Logging

> **Document ID:** `08-AUDIT-LOGGING.md`
> **Owner:** Security / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the immutable audit trail for the platform. Builds on the audit principles in [06-AUTHENTICATION](06-AUTHENTICATION.md) and [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md); operational hosting in [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Audit Principles](#2-audit-principles)
3. [What Must Be Audited](#3-what-must-be-audited)
4. [Audit Event Taxonomy](#4-audit-event-taxonomy)
5. [Audit Record Structure](#5-audit-record-structure)
6. [Immutability & Integrity](#6-immutability--integrity)
7. [Correlation & Traceability](#7-correlation--traceability)
8. [Data Protection in Audit Logs](#8-data-protection-in-audit-logs)
9. [Retention & Review](#9-retention--review)
10. [Audit Pipeline & Storage](#10-audit-pipeline--storage)
11. [Monitoring & Alerting](#11-monitoring--alerting)
12. [Open Decisions](#12-open-decisions)
13. [Document Map & Dependencies](#13-document-map--dependencies)
14. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **audit logging architecture and standards** for the Hospital ERP Enterprise platform: which events are audited, the structure and integrity guarantees of audit records, how they are stored and retained, and how they support security, clinical-safety, financial, and regulatory requirements.

**Scope:** audit event design, integrity, pipeline, retention, review. Out of scope: general application logging (structured ops logs — see [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)), and full security controls ([06-AUTHENTICATION](06-AUTHENTICATION.md), [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)).

---

## 2. Audit Principles

1. **Complete.** All security-relevant, clinical-safety, financial, and administrative events are audited — no gaps.
2. **Immutable.** Audit records cannot be altered, deleted, or forged by any actor (including admins).
3. **Attributable.** Every event is traceable to an actor, action, resource, and time.
4. **Tamper-evident.** Integrity is verifiable (hash chaining / WORM storage).
5. **No sensitive payloads.** Audit logs contain references, not passwords, secrets, tokens, or PHI content.
6. **Observable.** Audit integrity, pipeline health, and anomalies are monitored and alerted.
7. **Compliant.** Retention and review meet the compliance matrix in [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md).

---

## 3. What Must Be Audited

| Category | Examples |
| --- | --- |
| **Authentication** | Login success/failure, MFA events, token issuance/refresh/revocation, logout |
| **Authorization** | Access granted/denied, permission changes, role assignments |
| **Identity lifecycle** | User create/update/deactivate/offboard, profile changes, consent changes |
| **Clinical-safety** | Record create/edit (notes, orders, results, medications), order release, result review, medication administration |
| **Financial** | Charges, billing release, claims, payments, refunds, GL entries |
| **Data access** | Access to sensitive records (PHI), data export, bulk queries |
| **Administrative** | Config changes, role/permission changes, integration/API key changes, secret rotation |
| **System/security** | Failed validation, anomaly alerts, backup/restore, failover events |

---

## 4. Audit Event Taxonomy

Each audit event has a stable **type** identifying the action, e.g.:

- `auth.login`, `auth.login_failed`, `auth.mfa_required`, `auth.token_refreshed`
- `identity.created`, `identity.updated`, `identity.deactivated`
- `access.granted`, `access.denied`, `role.assigned`, `role.removed`
- `clinical.order_created`, `clinical.order_released`, `clinical.result_reviewed`
- `financial.charge`, `financial.claim_submitted`, `financial.payment`, `financial.generalledger`
- `data.patient_read`, `data.export`, `admin.config_changed`

Taxonomy is **versioned and extensible**; new event types are added by review, never ad hoc.

---

## 5. Audit Record Structure

A standard audit record (JSON) contains:

| Field | Description |
| --- | --- |
| `event_id` | Unique identifier |
| `event_type` | Stable taxonomy type (§4) |
| `timestamp` | UTC time of occurrence |
| `actor` | Subject id + type (user/service); system when applicable |
| `action` | Verb (create/read/update/delete/release/…) |
| `resource` | Resource type + id (e.g., `patient:123`) |
| `scope` | Facility/context the action applied in |
| `outcome` | Success / failure / denied |
| `source` | Surface: web / mobile / API / service / integration |
| `correlation_id` | Request/flow correlation id |
| `request` | Origin IP (masked), user agent, request id |
| `metadata` | Extensible domain-specific fields |
| `chain_hash` | Hash linking to prior record (integrity) |

---

## 6. Immutability & Integrity

- **Append-only:** audit records are only created; never updated or deleted in place.
- **Hash chaining:** each record carries a hash of the previous record, forming a verifiable chain; tampering breaks the chain.
- **WORM storage / replication:** writes are stored in write-once-read-many storage and replicated to protect integrity.
- **Access control:** audit write path is separate from read path; read requires `audit:read` (see [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md)); no actor may modify records.
- **Verification:** periodic integrity checks detect and report any break in the chain.

---

## 7. Correlation & Traceability

- Every audit record includes the **correlation_id** propagated across synchronous and asynchronous flows (see [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).
- A single business operation can be reconstructed end-to-end (e.g., order → result → billing → GL) by correlation.
- Audit records link to the underlying operational records by reference, not by copying sensitive data.

---

## 8. Data Protection in Audit Logs

- **MUST NOT** log passwords, secrets, tokens, or raw PHI content.
- Refer to records by identifier; store minimal derived context (e.g., masked values).
- **MUST NOT** write sensitive payloads to general application logs either; the same scrubbing rule applies ([04-CODING-STANDARDS](04-CODING-STANDARDS.md)).
- Encryption **at rest** and **in transit** applies to audit storage.
- Access to audit data is itself audited.

---

## 9. Retention & Review

- **Retention schedule** per data class and regulation; defined in the compliance matrix ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)) and [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md).
- Audit data is **archived** to object storage per policy and **destroyed** only by an audited, approved process.
- **Periodic review:** security and compliance review audit trails; anomalies trigger investigation.

---

## 10. Audit Pipeline & Storage

```
Application / service
      │  (structured audit event)
      ▼
Audit collector (validates & normalizes)
      │
      ▼
Immutable audit store (hash-chained, WORM)
      │                        │
      ▼                        ▼
Index/search (read model)   Archive (object storage)
```

- Writes are **synchronous to the audit store** for critical events (best-effort + retry for non-critical) so no security-critical event is lost.
- The audit store is the system of record for audit; search index is a projection for query.
- Pipeline health is monitored (§11).

---

## 11. Monitoring & Alerting

- **Metrics:** audit write latency, dropped/failed writes, backlog, integrity-check failures.
- **Alerts:** integrity break, audit pipeline failure, anomalous patterns (e.g., mass export, failed-login spikes, denied-access bursts).
- Audit anomalies integrate with the security operations view ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).

---

## 12. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| AL-1 | Audit store | PostgreSQL append-only table vs dedicated WORM store | Dedicated append-only store with hash chaining |
| AL-2 | Write guarantee | Synchronous vs async for non-critical events | Sync for security/clinical/financial; async elsewhere |
| AL-3 | Integrity scheme | Hash-chain vs signed (digital signature) | Hash-chain now; signing if compliance requires |

*Confirmed at the Phase 1 gate.*

---

## 13. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) | Observability & architecture view |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Structured logging & no-sensitive-data rules |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | Retention, archival, storage |
| [06-AUTHENTICATION](06-AUTHENTICATION.md) | Auth events & audit of identity |
| [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md) | `audit:read` role & authorization of access |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Compliance matrix & retention |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Security | Created audit logging: principles, what to audit, event taxonomy, record structure, immutability, correlation, data protection, retention, pipeline, monitoring, and open decisions. |

---

*End of `08-AUDIT-LOGGING.md`.*
