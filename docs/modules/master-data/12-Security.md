# Master Data Module — Security

> **Document ID:** `master-data/12-Security`
> **Owner:** Security / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate and when security posture changes.
>
> **Relationship:** This document defines the **security controls** for the Master Data Management module — threat model, tenant isolation, PHI protection, encryption, privileged operations, and monitoring. It follows [06-AUTHENTICATION](../../06-AUTHENTICATION.md), [07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md), [17-DATA-GOVERNANCE](../../17-DATA-GOVERNANCE.md), and [08-AUDIT-LOGGING](../../08-AUDIT-LOGGING.md).

---

## Table of Contents

1. [Security Overview](#1-security-overview)
2. [Threat Model](#2-threat-model)
3. [Authentication](#3-authentication)
4. [Authorization](#4-authorization)
5. [Tenant Isolation](#5-tenant-isolation)
6. [RLS](#6-rls)
7. [PHI Protection](#7-phi-protection)
8. [Encryption](#8-encryption)
9. [Secrets](#9-secrets)
10. [Session Security](#10-session-security)
11. [API Security](#11-api-security)
12. [Input Validation](#12-input-validation)
13. [Output Encoding](#13-output-encoding)
14. [Duplicate/Merge Security](#14-duplicatemerge-security)
15. [Privileged Operations](#15-privileged-operations)
16. [MFA](#16-mfa)
17. [Audit](#17-audit)
18. [Logging](#18-logging)
19. [Data Retention](#19-data-retention)
20. [Privacy](#20-privacy)
21. [Incident Response](#21-incident-response)
22. [Security Monitoring](#22-security-monitoring)
23. [Vulnerability Management](#23-vulnerability-management)
24. [Security Testing](#24-security-testing)
25. [Security Controls Matrix](#25-security-controls-matrix)
26. [Cross References](#26-cross-references)

---

## 1. Security Overview

The Master Data module handles identity and demographic data, much of which is **PHI** ([17-DATA-GOVERNANCE](../../17-DATA-GOVERNANCE.md) §14). It therefore applies defense-in-depth: zero-trust authentication, least-privilege authorization, tenant isolation at the data layer, encryption at rest and in transit, and full auditability.

---

## 2. Threat Model

| Threat | Mitigation |
| --- | --- |
| Cross-tenant access | RLS + tenant scope ([§5](#5-tenant-isolation)) |
| Unauthorized PHI access | Least privilege + consent gating |
| Duplicate/merge tampering | Audit + approval + integrity |
| Identifier spoofing | Identity verification ([06-AUTHENTICATION](../../06-AUTHENTICATION.md)) |
| Injection | Parameterized queries + validation ([04-CODING-STANDARDS](../../04-CODING-STANDARDS.md) §9) |
| Token theft | Short-lived tokens + rotation ([06-AUTHENTICATION](../../06-AUTHENTICATION.md) §6) |
| Insider misuse | SoD + MFA + audit ([11-Permissions](11-Permissions.md) §20–§21) |
| Data exfiltration | Export controls + encryption |

---

## 3. Authentication

| Aspect | Decision |
| --- | --- |
| Standard | OAuth 2.0 / OIDC ([06-AUTHENTICATION](../../06-AUTHENTICATION.md)) |
| Public clients | Authorization Code + PKCE |
| Service | Client credentials / mTLS |
| MFA | Elevated + clinical roles ([11-Permissions](11-Permissions.md) §21) |

---

## 4. Authorization

| Aspect | Decision |
| --- | --- |
| Model | RBAC + ABAC ([07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md)) |
| Enforcement | Gateway coarse + service fine-grained |
| Default | Deny |
| SoD | Requester ≠ approver ([11-Permissions](11-Permissions.md) §20) |

---

## 5. Tenant Isolation

| Aspect | Decision |
| --- | --- |
| Model | Tenant-scoped data ([09-MULTI-TENANCY](../../09-MULTI-TENANCY.md)) |
| Enforcement | RLS + application scope ([§6](#6-rls)) |
| Cross-tenant | Blocked |
| Consistency | Related rows share tenant |

---

## 6. RLS

Row-Level Security applies tenant isolation at the data layer ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §11).

| Table class | RLS policy |
| --- | --- |
| Tenant-scoped tables | `tenant_id = current_setting('app.tenant')` |
| Reference / lookup | Tenant-scoped (same policy; no cross-tenant shared tables) |
| Audit | Restricted by tenant + role |

> Every table in this module is tenant-scoped ([04-Database-Tables](04-Database-Tables.md) §4); there are no cross-tenant "global reference" tables. The Auditor role reads within the enforced tenant scope ([07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md) §9).

---

## 7. PHI Protection

| Aspect | Decision |
| --- | --- |
| Classification | Per-table PHI classification ([04-Database-Tables](04-Database-Tables.md)) |
| Access | Consent-gated, least privilege |
| De-identification | Export/anonymization ([17-Import-Export](17-Import-Export.md) §21) |
| No PHI in logs | Logging redaction ([§18](#18-logging)) |
| No PHI in tokens | Identity-only tokens |

> **PHI column classes.** PHI is carried by these column classes and is classified per table in [04-Database-Tables](04-Database-Tables.md): **identity** (`patient_identifier`/`staff_identifier`/`provider_identifier`/`organization_identifier` values), **demographic** (name, DOB, sex, address, contact), **consent** records, and **credential/relation** data. The most sensitive fields are additionally field-encrypted per [§8](#8-encryption); PHI is never written to logs or tokens (above).

---

## 8. Encryption

| Aspect | Decision |
| --- | --- |
| In transit | TLS 1.2+ |
| At rest | Storage encryption (AES-256) |
| Field-level | Sensitive fields encrypted where required |
| Keys | Managed KMS, rotation ([09-MULTI-TENANCY](../../09-MULTI-TENANCY.md)) |

---

## 9. Secrets

| Aspect | Decision |
| --- | --- |
| Storage | Vault/KMS ([21-Deployment](21-Deployment.md) §10) |
| Rotation | Automated + on-use |
| Never in repo | No secrets in source ([04-CODING-STANDARDS](../../04-CODING-STANDARDS.md) §9) |

---

## 10. Session Security

| Aspect | Decision |
| --- | --- |
| Tokens | Short-lived access + rotating refresh |
| Storage | Web secure cookie / mobile keystore |
| Revocation | On logout, role change, offboarding |
| Binding | Token to client/device |

---

## 11. API Security

| Aspect | Decision |
| --- | --- |
| Transport | TLS only |
| AuthZ | Gateway + service ([10-API](10-API.md) §30) |
| Rate limit | Per principal ([10-API](10-API.md) §29) |
| CORS | Restricted origins |
| Validation | At boundary |

---

## 12. Input Validation

| Aspect | Decision |
| --- | --- |
| Standard | [11-API-STANDARDS](../../11-API-STANDARDS.md) §13 |
| Enforcement | Server-side always |
| Reject | Oversized, malformed, type-mismatched |
| Parameters | Parameterized queries (no SQL injection) |

---

## 13. Output Encoding

| Aspect | Decision |
| --- | --- |
| XSS | Output-encode all user data |
| Context | HTML/JSON/attribute-appropriate encoding |
| PHI | Redact in UI/logs where not authorized |

---

## 14. Duplicate/Merge Security

| Aspect | Decision |
| --- | --- |
| Review | Only authorized reviewers ([11-Permissions](11-Permissions.md) §12–§13) |
| Approval | Elevated, MFA, audited |
| Integrity | Survivorship + merge audited immutably ([13-Audit](13-Audit.md)) |
| Reversal | Unmerge audited + reversible |

---

## 15. Privileged Operations

| Operation | Control |
| --- | --- |
| Merge / unmerge | SoD + MFA + approval |
| Purge / hard delete | Legal/regulatory + board + MFA |
| Deactivate with refs | Approval + MFA |
| Import apply | Elevated + audit |
| Integration manage | Restricted role |

---

## 16. MFA

Elevated and clinical actions require MFA ([06-AUTHENTICATION](../../06-AUTHENTICATION.md) §5; [11-Permissions](11-Permissions.md) §21).

---

## 17. Audit

All security-relevant events are audited ([13-Audit](13-Audit.md); [08-AUDIT-LOGGING](../../08-AUDIT-LOGGING.md)).

---

## 18. Logging

| Aspect | Decision |
| --- | --- |
| Structured | JSON, correlation id |
| Redaction | No PHI or secrets |
| Retention | Per schedule ([08-AUDIT-LOGGING](../../08-AUDIT-LOGGING.md) §10) |
| Alerting | Anomalies to SOC ([22](#22-security-monitoring)) |

---

## 19. Data Retention

Retention follows [17-DATA-GOVERNANCE](../../17-DATA-GOVERNANCE.md) §12 and [05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §8. PHI retention is regulatory-gated; purge is governed and audited.

---

## 20. Privacy

| Aspect | Decision |
| --- | --- |
| Consent | Consent-gated access ([17-DATA-GOVERNANCE](../../17-DATA-GOVERNANCE.md) §15) |
| Minimal | Minimum necessary |
| Rights | Support access/rectification/erasure per regulation |
| DPIA | For high-risk processing |

---

## 21. Incident Response

| Aspect | Decision |
| --- | --- |
| Process | Platform IR ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §14) |
| Triage | Severity-based escalation ([14-Notifications](14-Notifications.md)) |
| Notify | Regulatory notification per [20-COMPLIANCE](../../20-COMPLIANCE.md) |
| Postmortem | Documented, audited |

---

## 22. Security Monitoring

| Aspect | Decision |
| --- | --- |
| Logs | Aggregated + searchable |
| Metrics | AuthZ failures, anomalies |
| Alerts | SOC thresholds ([14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md)) |
| Review | Periodic |

---

## 23. Vulnerability Management

| Aspect | Decision |
| --- | --- |
| Scanning | Dependency + code scan in CI |
| Gate | Zero high/critical at release ([00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §13) |
| Response | SLAs per severity |
| SBOM | Maintained |

---

## 24. Security Testing

| Aspect | Decision |
| --- | --- |
| SAST | In CI |
| DAST | Pre-release |
| Pen test | Periodic |
| AuthZ tests | Automated ([11-Permissions](11-Permissions.md) §23) |
| Threat modeling | At gates ([20-Testing](20-Testing.md) §17) |

---

## 25. Security Controls Matrix

| Control | Section | Status |
| --- | --- | --- |
| Authentication | §3 | Designed |
| Authorization | §4 | Designed |
| Tenant isolation | §5 | Designed |
| RLS | §6 | Designed |
| PHI protection | §7 | Designed |
| Encryption | §8 | Designed |
| Secrets | §9 | Designed |
| Sessions | §10 | Designed |
| API security | §11 | Designed |
| Input validation | §12 | Designed |
| Output encoding | §13 | Designed |
| Duplicate/merge | §14 | Designed |
| Privileged ops | §15 | Designed |
| MFA | §16 | Designed |
| Audit | §17 | Designed |
| Logging | §18 | Designed |
| Retention | §19 | Designed |
| Privacy | §20 | Designed |
| Incident response | §21 | Designed |
| Monitoring | §22 | Designed |
| Vulnerability mgmt | §23 | Designed |
| Security testing | §24 | Designed |

---

## 26. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [06-AUTHENTICATION](../../06-AUTHENTICATION.md) | AuthN | Consumes |
| [07-ROLES-PERMISSIONS](../../07-ROLES-PERMISSIONS.md) | AuthZ | Consumes |
| [17-DATA-GOVERNANCE](../../17-DATA-GOVERNANCE.md) | Governance | Consumes |
| [08-AUDIT-LOGGING](../../08-AUDIT-LOGGING.md) | Audit | Consumes |
| [09-MULTI-TENANCY](../../09-MULTI-TENANCY.md) | Tenancy | Consumes |
| [11-Permissions](11-Permissions.md) | Permissions | Consumes |
| [13-Audit](13-Audit.md) | Audit events | Consumes |
| [04-CODING-STANDARDS](../../04-CODING-STANDARDS.md) | Secure coding | Consumes |
| [10-API](10-API.md) | API security | Consumes |

---

*End of `docs/modules/master-data/12-Security.md`.*
