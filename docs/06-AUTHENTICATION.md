# Hospital ERP Enterprise — Authentication & Authorization

> **Document ID:** `06-AUTHENTICATION.md`
> **Owner:** Architecture / Engineering Lead (security)
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the identity & access model implemented in **Phase 2 (IAM)**. Broader security & compliance controls are in [09-SECURITY](09-SECURITY.md); identity technology choice is proposed in [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Principles](#2-principles)
3. [Actors & Identity Lifecycle](#3-actors--identity-lifecycle)
4. [Authentication Flows](#4-authentication-flows)
5. [Identity Provider Decision](#5-identity-provider-decision)
6. [Token & Session Strategy](#6-token--session-strategy)
7. [Mobile & Offline Authentication](#7-mobile--offline-authentication)
8. [Authorization Model](#8-authorization-model)
9. [Roles, Permissions & Policies](#9-roles-permissions--policies)
10. [Multi-Facility / Tenant Model](#10-multi-facility--tenant-model)
11. [Audit & Compliance](#11-audit--compliance)
12. [Threat Considerations](#12-threat-considerations)
13. [Integration with Web, Mobile & API](#13-integration-with-web-mobile--api)
14. [Open Decisions](#14-open-decisions)
15. [Document Map & Dependencies](#15-document-map--dependencies)
16. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **authentication and authorization architecture** for the Hospital ERP Enterprise platform. It describes how users and systems prove identity, how sessions and tokens are managed, how access is authorized and scoped, and how this is audited.

It is the design basis for **Phase 2 (IAM)**, which is a hard prerequisite for every authenticated feature (see [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).

**Scope:** identity, authN, authZ, sessions, roles/permissions, tenancy, audit. Out of scope: application-layer security beyond identity (in [09-SECURITY](09-SECURITY.md)) and identity data persistence schema (in [07-DATA-MODEL](07-DATA-MODEL.md)).

---

## 2. Principles

1. **Identity is the boundary.** Every request is authenticated and authorized regardless of origin (zero trust).
2. **Least privilege.** Users and services get the minimum access required, for the minimum time.
3. **Defense in depth.** Authorization is enforced at the API gateway **and** re-checked in services; the UI is never the sole gate.
4. **Standards over custom.** Use OAuth 2.0 / OIDC; avoid bespoke session schemes.
5. **MFA by policy.** Multi-factor authentication for elevated and clinical roles, and for risky actions.
6. **Auditability.** Every security-relevant event is logged immutably.
7. **Secure by default.** Short-lived tokens, rotation, revocation, secure storage on mobile.

---

## 3. Actors & Identity Lifecycle

| Actor | Identity type | Provisioning | Deprovisioning |
| --- | --- | --- | --- |
| Staff (admin, clinical, ops, finance) | Internal user | HR/admin workflow, approved | Immediate revocation on departure |
| Patient / family | Registered user | Self-service or front-desk | Consent-driven deactivation |
| Service / API client | Machine identity | Admin-managed credentials | Rotation/revocation on use |
| Integrations (external) | Service identity | Contracted, scoped | Revoked on contract end |

**Lifecycle MUST** cover: onboarding → role assignment → changes → offboarding/revocation, each audited. Deprovisioning MUST be immediate and cascade to token/session invalidation.

---

## 4. Authentication Flows

| Surface | Flow | MFA | Notes |
| --- | --- | --- | --- |
| Web (staff) | OIDC Authorization Code + PKCE | Yes (elevated) | Short-lived session; refresh via rotating token |
| Web (patient portal) | OIDC Authorization Code + PKCE | Optional (self-service) | Consent-aware |
| Mobile (patient) | OIDC Authorization Code + PKCE | Optional | Secure token storage |
| Mobile (staff) | OIDC + device binding | Yes | Biometrics optional; re-auth on sensitive action |
| API / service-to-service | Client credentials / mTLS | n/a | Scoped service tokens |
| Integrations | Scoped service identity | n/a | Least privilege per integration |

**MUST** use PKCE for all public clients (web/mobile) to prevent authorization-code interception.

---

## 5. Identity Provider Decision

**Options:** (a) adopt **Keycloak** (feature-rich, fast to deploy), or (b) a **native OIDC implementation** (more control, more effort).

**Evaluation:**

| Criterion | Keycloak | Native |
| --- | --- | --- |
| Time-to-value | Fast | Slow |
| Feature breadth (MFA, social, admin UI) | High | Build ourselves |
| Control / customization | Moderate | High |
| Operational surface | Managed/self-hosted component | In-platform |
| Risk | Dependency on a component | Bugs are ours |

**Recommendation:** adopt **Keycloak** (or an equivalent standards-based IdP) initially for velocity and maturity, keeping a native migration path. **Confirmed at the Phase 1 gate** (decision D2 in [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md)).

---

## 6. Token & Session Strategy

- **Access tokens:** short-lived (e.g., 15 min), JWT/Opaque; carry minimal claims; validated at the API gateway.
- **Refresh tokens:** longer-lived but **rotating**; bound to a client and device; revocable.
- **Revocation:** session and refresh-token revocation on logout, password change, role change, or offboarding.
- **No PHI in tokens** — tokens carry identity/claims only; data is fetched over authorized API calls.
- **Secure storage:** web tokens in memory/secure cookie (not `localStorage`); mobile tokens in OS keystore/Keychain.
- **Replay/misuse:** token binding to client; alert on anomalous refresh patterns.

---

## 7. Mobile & Offline Authentication

- **Secure storage:** OS keystore/Keychain for tokens; never plaintext.
- **Biometrics:** optional convenience; falls back to PIN/credential.
- **Offline:** authorized data cached locally with bounded retention; re-auth enforced on reconnect; no PHI cached unencrypted.
- **Device binding:** staff apps bind sessions to device where risk demands; re-auth for sensitive actions (prescribing, discharge).

---

## 8. Authorization Model

- **Baseline: RBAC.** Users have roles; roles aggregate permissions; permissions map to operations (see [09-SECURITY](09-SECURITY.md) for control mapping).
- **Progressive: policy-based (ABAC).** Where access depends on context (facility, department, patient relationship, consent), enforce policy rules in addition to roles.
- **Enforcement points:** API gateway enforces coarse authZ; service/domain layer re-checks fine-grained rules (defense in depth).
- **Tenant/facility scoping:** a user's access is always scoped to the facilities/contexts they are authorized for.

---

## 9. Roles, Permissions & Policies

- **Role catalog** is defined centrally; a **role matrix** (role × permission) is maintained and reviewed at gates (Phase 2 exit criterion).
- **Principle of separation of duties** where clinical/financial integrity demands (e.g., requester ≠ dispenser for controlled processes).
- **Permission model** is data-defined, versioned, and tested; not hard-coded in UI.
- **Elevated actions** (financial release, clinical holds, user administration) require additional verification/MFA.

---

## 10. Multi-Facility / Tenant Model

- **Data model supports multi-facility** from Phase 3 (see [07-DATA-MODEL](07-DATA-MODEL.md)); single-facility deployment first.
- **Scoping:** authorization tokens and policies carry facility/context scope so a user only sees their authorized facilities.
- **Isolation:** logical tenant isolation via scoping + row-level constraints; physical isolation only where compliance demands.

---

## 11. Audit & Compliance

- **Audit events** (immutable, append-only) for: authentication success/failure, token issuance/rotation, role/permission changes, sensitive data access, admin actions, offboarding.
- **Correlation:** audit events link to request correlation id and actor.
- **Retention** per compliance matrix in [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md); reviewable by security.
- **No sensitive payloads** (passwords, secrets, tokens) written to audit or logs.

---

## 12. Threat Considerations

| Threat | Mitigation |
| --- | --- |
| Credential stuffing / brute force | Rate limiting, lockout, account monitoring |
| Phishing / MFA bypass | MFA with phishing-resistant methods where available; user awareness |
| Token leakage / XSS | Secure storage, short-lived tokens, CSP, no tokens in URL |
| Session fixation | PKCE, regenerate session on privilege change |
| Insider abuse | Least privilege, separation of duties, audit, alerting |
| Replay attacks | Token binding, short TTL, nonce |
| Offboarding gap | Immediate revocation cascading to sessions/tokens |

---

## 13. Integration with Web, Mobile & API

- **Web/mobile** authenticate via OIDC against the IdP and use the resulting tokens against the API gateway.
- **API** validates tokens at the gateway; services trust a validated, enriched principal (subject, roles, scopes, facility).
- **Service-to-service** uses scoped service identities with least privilege; never shared end-user credentials.
- **Frontend route guards** reflect permissions (UX) but the API remains the authority (see [04-CODING-STANDARDS](04-CODING-STANDARDS.md)).

---

## 14. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| AU-1 | Identity provider | Keycloak vs native OIDC | Keycloak (aligns with D2) |
| AU-2 | Access token format | JWT vs opaque | JWT at gateway, validated centrally |
| AU-3 | Staff mobile MFA | Required vs contextual | Required for sensitive actions |
| AU-4 | Fine-grained authZ | RBAC only vs RBAC + ABAC | RBAC now, ABAC progressive |

*Confirmed at the Phase 1 gate.*

---

## 15. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) | Security architecture view |
| [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md) | Identity technology (D2) |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Security coding rules |
| [07-DATA-MODEL](07-DATA-MODEL.md) | Identity data schema |
| [08-API](08-API.md) | Token validation & API enforcement |
| [09-SECURITY](09-SECURITY.md) | Broader security & compliance |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created authentication & authorization: principles, identity lifecycle, authN flows, IdP decision, token/session strategy, mobile/offline, authZ model, roles/permissions, multi-facility tenancy, audit, threat considerations, integration, and open decisions. |

---

*End of `06-AUTHENTICATION.md`.*
