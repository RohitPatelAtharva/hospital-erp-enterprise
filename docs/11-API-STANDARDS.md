# Hospital ERP Enterprise — API Standards

> **Document ID:** `11-API-STANDARDS.md`
> **Owner:** Engineering / Architecture Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the REST API contract and versioning standards. Complements the integration architecture in [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) and the coding rules in [04-CODING-STANDARDS](04-CODING-STANDARDS.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [API Principles](#2-api-principles)
3. [REST Conventions](#3-rest-conventions)
4. [OpenAPI Contracts](#4-openapi-contracts)
5. [Versioning Strategy](#5-versioning-strategy)
6. [Response & Error Envelope](#6-response--error-envelope)
7. [Pagination, Filtering & Sorting](#7-pagination-filtering--sorting)
8. [Idempotency](#8-idempotency)
9. [Authentication & Authorization](#9-authentication--authorization)
10. [Rate Limiting & Protection](#10-rate-limiting--protection)
11. [Consistency & Events](#11-consistency--events)
12. [Documentation](#12-documentation)
13. [Open Decisions](#13-open-decisions)
14. [Document Map & Dependencies](#14-document-map--dependencies)
15. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines **API design and versioning standards** for the Hospital ERP Enterprise platform. It ensures APIs are consistent, contract-first, secure, and evolvable across web, mobile, and integrations.

**Scope:** REST conventions, OpenAPI, versioning, response/error formats, pagination, idempotency, auth, rate limiting, documentation. Out of scope: internal service contracts and eventing (see [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).

---

## 2. API Principles

1. **Contract-first.** APIs are defined by OpenAPI schema before implementation; consumers and producers agree on the contract.
2. **Consistent.** Uniform resource naming, envelope, errors, and pagination across all resources.
3. **Versioned.** Public contracts are versioned; breaking changes are explicit and gated.
4. **Secure by default.** Every endpoint is authenticated and authorized; least privilege.
5. **Resilient.** Idempotency, retries, and graceful errors for integrations and payments.
6. **Observable.** Requests are traceable via correlation id; errors are structured and safe.

---

## 3. REST Conventions

- **Resources** use plural nouns (`/patients`, `/encounters/{id}/orders`).
- **HTTP methods:** `GET` (read), `POST` (create / action), `PUT` (full replace), `PATCH` (partial update), `DELETE` (delete/deactivate).
- **Nested resources** for clear ownership; avoid deep nesting (max ~2 levels).
- **Action verbs** via POST sub-resources (`/orders/{id}/release`) rather than custom methods.
- **Status codes:** use correct semantics (200/201/202/204/400/401/403/404/409/422/429/5xx).
- **No sensitive data** in URLs or query strings; use headers/body.

---

## 4. OpenAPI Contracts

- **MUST** provide an OpenAPI 3.x document for every public API.
- Schema is the **source of truth** for requests/responses; DTOs are generated/validated against it.
- **MUST** define security schemes, error responses, and pagination params in the spec.
- Contract changes are reviewed like code; schema linting is part of CI.

---

## 5. Versioning Strategy

- **URI versioning:** `/api/v1/patients`, `/api/v2/patients`.
- **Compatibility:** within a major version, changes are **additive** (new optional fields, new endpoints); breaking changes require a new major version.
- **Deprecation:** deprecated versions remain available with a documented sunset; consumers are notified.
- **Parallel run:** overlapping major versions are supported during transition; data contracts remain compatible.
- **Documentation:** each version's contract is versioned and accessible.

---

## 6. Response & Error Envelope

Standard response envelope:

```json
{
  "data": { },
  "meta": { "page": 1, "pageSize": 50, "total": 123 }
}
```

Standard error envelope (from [04-CODING-STANDARDS](04-CODING-STANDARDS.md)):

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Human-readable summary",
    "details": [ { "field": "dateOfBirth", "reason": "invalid" } ],
    "correlationId": "abc-123"
  }
}
```

- **MUST NOT** leak stack traces or sensitive data in errors.
- Error codes are stable and documented.

---

## 7. Pagination, Filtering & Sorting

- **Pagination:** cursor-based for large collections; `page`/`pageSize` supported where cursor is impractical. Defaults and caps defined (no unbounded queries).
- **Filtering:** explicit, whitelisted filter params; validation of filter values.
- **Sorting:** whitelisted sort fields; deterministic tie-breakers.
- Responses include pagination metadata (§6) and stable ordering.

---

## 8. Idempotency

- **MUST** support idempotency keys for write endpoints that can be retried safely (payments, order creation, claims).
- Client supplies an `Idempotency-Key`; the server deduplicates and returns the original result for repeats.
- Backed by unique constraints and idempotency storage ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- Enables safe retries for integrations and network resilience.

---

## 9. Authentication & Authorization

- **Authentication:** OAuth 2.0 / OIDC bearer tokens, validated at the gateway ([06-AUTHENTICATION](06-AUTHENTICATION.md)).
- **Authorization:** coarse at gateway (route), fine-grained re-checked in services; scoped to tenant/facility ([07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md), [09-MULTI-TENANCY](09-MULTI-TENANCY.md)).
- **Service-to-service:** scoped machine identities; never end-user credentials.
- **Denied** requests return `403` and are audited ([08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)).

---

## 10. Rate Limiting & Protection

- **Rate limiting** at the gateway per principal/client to prevent abuse ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).
- **Payload limits** and input validation at the boundary ([04-CODING-STANDARDS](04-CODING-STANDARDS.md)).
- **429** responses include retry-after guidance; clients honor backoff.
- Anomalous usage is monitored and alerted.

---

## 11. Consistency & Events

- Synchronous APIs return committed state; asynchronous outcomes are communicated via events ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).
- Long-running workflows (claims, integrations) expose status resources and event notifications.
- Cross-service effects use the outbox pattern for reliability.

---

## 12. Documentation

- **API reference** generated from OpenAPI and kept in sync with the spec.
- **Guides** cover auth, pagination, idempotency, error handling, and versioning for consumers.
- Breaking changes and deprecations are announced with migration guidance.

---

## 13. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| API-1 | Versioning scheme | URI vs header vs content-negotiation | URI (`/api/v{n}`) |
| API-2 | Pagination default | Cursor vs offset | Cursor for large; offset elsewhere |
| API-3 | Contract generation | Code-first vs spec-first | Spec-first (contract-first) |
| API-4 | Public webhooks | v1 scope vs later | Later (Phase 10) |

*Confirmed at the Phase 1 gate.*

---

## 14. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) | API/integration architecture |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Error envelope & coding rules |
| [06-AUTHENTICATION](06-AUTHENTICATION.md) | Token validation |
| [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md) | Authorization on APIs |
| [09-MULTI-TENANCY](09-MULTI-TENANCY.md) | Tenant scoping |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Audit of API events |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Engineering | Created API standards: principles, REST conventions, OpenAPI, versioning, response/error envelope, pagination, idempotency, auth, rate limiting, consistency/events, documentation, and open decisions. |

---

*End of `11-API-STANDARDS.md`.*
