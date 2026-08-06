# Hospital ERP Enterprise — System Architecture

> **Document ID:** `02-SYSTEM-ARCHITECTURE.md`
> **Owner:** Architecture / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Implements the vision ([01-ENTERPRISE-VISION](01-ENTERPRISE-VISION.md)) and is sequenced by [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md). Deeper detail on data, API, security, and infrastructure lives in the referenced follow-on docs.

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Architecture Principles](#2-architecture-principles)
3. [System Context](#3-system-context)
4. [Architectural Style](#4-architectural-style)
5. [Logical Architecture](#5-logical-architecture)
6. [Technology Stack (Recommended Baseline)](#6-technology-stack-recommended-baseline)
7. [Backend Architecture](#7-backend-architecture)
8. [Frontend Architecture](#8-frontend-architecture)
9. [Mobile Architecture](#9-mobile-architecture)
10. [Data Architecture](#10-data-architecture)
11. [API & Integration Architecture](#11-api--integration-architecture)
12. [Asynchronous Messaging & Eventing](#12-asynchronous-messaging--eventing)
13. [Security Architecture](#13-security-architecture)
14. [Observability Architecture](#14-observability-architecture)
15. [Infrastructure & Deployment View](#15-infrastructure--deployment-view)
16. [Non-Functional Requirements](#16-non-functional-requirements)
17. [Standards & Conventions](#17-standards--conventions)
18. [Architecture Decision Records (ADRs)](#18-architecture-decision-records-adrs)
19. [Document Map & Dependencies](#19-document-map--dependencies)
20. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **target system architecture** for the Hospital ERP Enterprise platform. It describes the structural, run-time, and deployment views of the system; the principles that govern technical decisions; and the recommended technology baseline.

**Scope:** The architecture of the platform itself — its modules, services, data handling, APIs, and deployment. It does **not** detail the full database schema (see [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)), the API contract surface (see [11-API-STANDARDS](11-API-STANDARDS.md)), security controls (see [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)), or infrastructure operations (see [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md)).

**Status note:** Section 6 recommends a concrete stack. Per the roadmap's Phase 1 gate, **no implementation begins until this document (and the supporting design set) is approved.**

---

## 2. Architecture Principles

Derived from the guiding principles in [01-ENTERPRISE-VISION](01-ENTERPRISE-VISION.md).

| # | Principle | Consequence |
| --- | --- | --- |
| A1 | **Clinical safety first** | Fail-safe defaults; identity verification at clinical boundaries; no silent data loss |
| A2 | **Security & compliance by design** | Zero-trust posture; least privilege; audit trail on all sensitive operations |
| A3 | **Single source of truth** | One canonical data model; no duplicated authoritative state across surfaces |
| A4 | **Modular, not premature** | Modular monolith first; extract services only where demonstrated need exists |
| A5 | **Observable by default** | Every service exposes health/readiness, metrics, and structured logs |
| A6 | **Contract-first integration** | APIs defined by schema before implementation; versioned |
| A7 | **Testable & deployable** | Everything ships through CI/CD with automated quality gates |
| A8 | **Progressive, evidence-driven** | Technology choices reviewed at each phase gate, not fixed blindly |

---

## 3. System Context

The platform is the hub connecting people and systems:

```
                    ┌──────────────────────────┐
                    │        Patients          │
                    │   (mobile app / portal)   │
                    └────────────┬─────────────┘
                                 │ HTTPS / OAuth
┌───────────────┐   ┌────────────▼─────────────┐   ┌──────────────────┐
│  Clinical     │   │                          │   │  Admin / Finance │
│  staff        │──▶│   HOSPITAL ERP PLATFORM   │◀──│  / Ops staff     │
│  (web+mobile) │   │   (API, services, data)  │   │  (web)           │
└───────────────┘   └───────┬─────────┬────────┘   └──────────────────┘
                            │         │
             ┌──────────────▼──┐   ┌───▼─────────────┐
             │ External health │   │ Payment / payer │
             │ systems (FHIR)  │   │ / notification  │
             └─────────────────┘   └─────────────────┘
```

**External actors:** patients, clinical staff, administrative/finance staff, external health systems (interoperability), payers/payment gateways, and notification providers (email/SMS).

---

## 4. Architectural Style

**Primary style: Modular Monolith (API-centric), with a clean path to service extraction.**

**Rationale (evidence-driven, per A4):**
- A single deployable backend with strong internal module boundaries gives cohesion, transactionality, and simplicity during the early phases.
- Boundaries are enforced at the code level (module seams), so a module can later be extracted into a separate service with manageable effort **only when** scaling or team autonomy demands it.
- This avoids the distributed-systems tax (network failures, eventual consistency, distributed tracing) until the evidence justifies it.

**Decision rule:** A module is extracted to a service when at least two of: (a) independent scaling needs, (b) independent deployment cadence, or (c) team ownership boundaries — are clearly demonstrated. Extraction is an ADR.

**Supporting styles applied within:**
- **Layered** inside each module (interface / application / domain / infrastructure).
- **Event-driven** across modules for cross-cutting concerns (notifications, audit, integrations).
- **Contract-first REST** for synchronous external-facing APIs; **asynchronous** for integration and long-running workflows.

---

## 5. Logical Architecture

High-level layers and the modules within them.

```
┌────────────────────────────────────────────────────────────────────┐
│                         PRESENTATION LAYER                          │
│   Web: Admin Portal · Clinical Portal · Patient Portal             │
│   Mobile: Patient App · Staff App                                   │
├────────────────────────────────────────────────────────────────────┤
│                        API / GATEWAY LAYER                          │
│   API Gateway · AuthN/Z enforcement · Rate limiting · Routing        │
├────────────────────────────────────────────────────────────────────┤
│                        APPLICATION LAYER                             │
│   ┌──────┬─────────┬─────────┬─────────┬─────────┬─────────┐        │
│   │IAM   │Registry │Scheduling│  EHR   │  Billing│ Inventory│        │
│   │      │Patient/ │          │ Orders │ Finance │ Pharm/Lab│        │
│   │      │Staff    │          │ Results│         │          │        │
│   └──────┴─────────┴─────────┴─────────┴─────────┴─────────┘        │
│   Cross-cutting: Notifications · Audit · Reporting · Integration    │
├────────────────────────────────────────────────────────────────────┤
│                         DOMAIN / DATA LAYER                          │
│   Primary relational DB · Search index · Cache · Object/blob store  │
├────────────────────────────────────────────────────────────────────┤
│                        INFRASTRUCTURE LAYER                          │
│   Containers · Orchestration · Networking · Observability           │
└────────────────────────────────────────────────────────────────────┘
```

---

## 6. Technology Stack (Recommended Baseline)

> **This is a recommendation for approval, not a decision already made.** Alternatives and rationale are given; the gate review confirms or revises. Versions are pinned at implementation time (ADR), not here.

### 6.1 Recommended baseline

| Layer | Recommended | Rationale | Alternatives considered |
| --- | --- | --- | --- |
| **Backend language/runtime** | C#/.NET (or Java/Spring) | Strong typing, mature ecosystem, first-class async, enterprise support | Node/TS, Go, Python |
| **API framework** | .NET Web API / Spring Boot | REST, OpenAPI generation, DI, middleware | Express, FastAPI |
| **Primary database** | PostgreSQL | Relational integrity, ACID, JSON support, open source, mature | MySQL, SQL Server |
| **Search** | OpenSearch / Elasticsearch | Patient search, duplicates, free-text | PostgreSQL FTS |
| **Cache** | Redis | Sessions, caching, rate-limit counters, pub/sub | In-memory only |
| **Message bus** | Kafka (or RabbitMQ) | Eventing, durable async, integrations | Redis Streams |
| **Object storage** | S3-compatible (e.g., MinIO local) | Documents, images, FHIR bundles | Filesystem |
| **Frontend (web)** | React + TypeScript | Large ecosystem, component reuse, maintainability | Vue, Angular |
| **Mobile** | React Native (or Flutter) | Cross-platform from one codebase | Native (Swift/Kotlin) |
| **Containers/orchestration** | Docker + Kubernetes (prod), Compose (dev) | Standard, portable, scalable | — |
| **CI/CD** | GitHub Actions | Native to repo | Jenkins, GitLab CI |
| **Observability** | OpenTelemetry + Prometheus + Grafana + Loki | Open standard, vendor-neutral | Datadog, ELK |
| **Identity** | OAuth 2.0 / OIDC (Keycloak or native) | Standards-based authN/Z | Custom session only |

### 6.2 Decision rules for the stack
- Favor **broadly-adopted, long-lived** technologies with strong hiring pools.
- Prefer **open standards** (OpenAPI, OIDC, OpenTelemetry, FHIR) to avoid lock-in.
- Any deviation from this baseline during implementation requires an ADR.

---

## 7. Backend Architecture

### 7.1 Module map (logical boundaries)

| Module | Core responsibilities | Key data owned |
| --- | --- | --- |
| **IAM** | AuthN/Z, users, roles, sessions, audit | Users, roles, sessions, audit events |
| **Registry** | Patients, staff, facilities, encounters | Patient master, staff, org structure |
| **Scheduling** | Appointments, availability, waitlist | Slots, appointments |
| **EHR** | Encounters, notes, problems, orders, results | Clinical records, orders, results |
| **Clinical support** | Pharmacy, lab, medication safety | Formulary, specimens, stock |
| **Billing/Finance** | Charges, claims, payments, GL | Financial records |
| **Inventory/Procurement** | Stock, purchase orders, vendors, assets | Inventory, POs |
| **Reporting/Analytics** | Dashboards, exports, DW staging | Aggregates, reports |
| **Integration** | FHIR/HL7, webhooks, external adapters | Exchange logs |

### 7.2 Module structure (inside each module)
- **Interface** — DTOs and entry points (controllers/handlers).
- **Application** — use cases, orchestration, transactions, authorization checks.
- **Domain** — business rules, entities, value objects, invariants.
- **Infrastructure** — persistence, external calls, messaging adapters.

### 7.3 Concurrency, transactions & consistency
- **Synchronous operations** (e.g., booking a slot) use ACID transactions on the primary DB to guarantee no double-booking (Phase 4 exit criterion).
- **Cross-cutting concerns** (notifications, audit, integration events) are emitted after the source transaction commits (outbox pattern) to avoid lost events.
- **Idempotency** is required for all external integrations and payment operations.

---

## 8. Frontend Architecture

- **Monorepo packages** under `frontend/` for each portal (admin, clinical, patient), sharing design-system and data-access packages.
- **Design system** — a single component library enforcing consistency and accessibility (WCAG).
- **State management** — client state local; server state via a typed data-access layer (e.g., React Query / TanStack) over the API.
- **Routing & authorization** — role-aware route guards mirroring backend permissions (defense in depth; the API remains the authority).
- **Build** — package-based build with tree-shaking, code-splitting, and bundle budgets.

---

## 9. Mobile Architecture

- **Cross-platform** (single codebase for iOS/Android) for both patient and staff apps.
- **Offline-aware** where clinically justified (e.g., rounds): local cache with conflict resolution and eventual sync; never stores real PHI unencrypted at rest.
- **Push notifications** via a provider; driven by the backend event stream.
- **Secure storage** — OS keychain/keystore for tokens; biometrics optional.
- **Accessibility** — meets WCAG-equivalent guidance on mobile.

---

## 10. Data Architecture

- **Single canonical relational store** as the system of record (see [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- **Read models / projections** (search index, reporting warehouse) built from the canonical store via events — never written to directly by modules.
- **Search index** for patient lookup and duplicate matching.
- **Cache** for hot reads; cache invalidation is event-driven.
- **Object storage** for documents, images, and exported data.
- **Encryption:** data at rest (disk + storage) and in transit (TLS) enforced.
- **No real PHI** outside production; synthetic/anon data in lower environments.

---

## 11. API & Integration Architecture

- **REST + OpenAPI** contracts; versioned (`/api/v1`, `/api/v2`); contract-first (see [11-API-STANDARDS](11-API-STANDARDS.md)).
- **API Gateway** centralizes authN/Z, rate limiting, logging, and routing; individual services enforce their own authorization as defense in depth.
- **Standard envelope** for responses and error handling; consistent pagination, filtering, and idempotency keys.
- **Interoperability:** FHIR (R4) mapping for external exchange; HL7 where required; adapters isolate external specifics (see Phase 10).
- **Webhooks/public API** for third parties, with signing and replay protection.

---

## 12. Asynchronous Messaging & Eventing

- **Event bus** for cross-module concerns: notifications, audit, projection updates, integration forwarding.
- **Outbox pattern** ensures events are published reliably after the committing transaction.
- **Event schema** versioned; consumers tolerant to unknown fields (additive evolution).
- **Idempotent consumers** to safely support retries and at-least-once delivery.
- **Dead-letter** handling and observability for failed messages.

---

## 13. Security Architecture

High-level; the authoritative control set is [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md).

- **Authentication:** OAuth 2.0 / OIDC, MFA-capable, session + refresh token handling.
- **Authorization:** RBAC baseline with policy-based authorization for fine-grained rules; enforced at API layer and re-checked in services.
- **Zero trust:** every request authenticated and authorized regardless of origin; least privilege; network segmentation.
- **Secrets:** centralized secret manager; no secrets in code or env templates.
- **Audit:** immutable, complete audit trail for security-relevant and clinical events.
- **Data protection:** encryption at rest and in transit; key management; tokenization/minimization for sensitive fields.
- **Threat modeling** at each phase gate; dependency and container image scanning in CI.

---

## 14. Observability Architecture

- **Three pillars:** structured logs (Loki), metrics (Prometheus/Grafana), traces (OpenTelemetry/Jaeger).
- **Health & readiness** endpoints on every service for orchestration and load balancing.
- **Correlation IDs** propagated across synchronous and asynchronous flows.
- **Dashboards & alerts** for SLIs/SLOs; on-call runbooks (Phase 11).
- **No PHI in logs** — PII scrubbing is enforced at the logging boundary.

---

## 15. Infrastructure & Deployment View

Operational detail is in [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md); the architectural view:

- **Environments:** local → dev → staging → production, promoted via artifact-driven CI/CD.
- **Containers:** all components containerized; Compose for dev, orchestration (K8s) for production.
- **Stateful services** (DB, search, cache, bus) run on managed or dedicated infrastructure with backups.
- **Scaling:** stateless application components scale horizontally; stateful components scale by architecture.
- **Disaster recovery:** defined RPO/RTO, backup/restore drills, multi-AZ where supported.

---

## 16. Non-Functional Requirements

| Category | Requirement |
| --- | --- |
| **Performance** | Sub-second API p95 for core reads; order/result flow responsive |
| **Availability** | 99.9% availability target post-launch |
| **Scalability** | Horizontal scaling for web/API; database capacity planned (Phase 11) |
| **Security** | Zero high/critical vulns at release; OWASP-aligned |
| **Compliance** | HIPAA alignment + local regulation; audit trail completeness |
| **Accessibility** | WCAG-compliant web; accessible mobile |
| **Reliability** | Idempotent integrations; at-least-once with dedupe; DR plan |
| **Observability** | All services instrumented; correlation across flows |
| **Maintainability** | Modular seams, contract-first APIs, documented ADRs |

---

## 17. Standards & Conventions

- **API:** OpenAPI 3.x; consistent envelope, errors, pagination.
- **Logging:** structured JSON; correlation IDs; PII scrubbing.
- **Metrics:** OpenTelemetry/Prometheus naming conventions.
- **Data:** versioned migrations; naming conventions per [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md).
- **Git:** trunk-based; short-lived branches; conventional commits; PR review.
- **Code quality:** linting, formatting, coverage thresholds, dependency scans in CI.

---

## 18. Architecture Decision Records (ADRs)

Significant technical decisions are recorded as ADRs (in `docs/adr/` or the design series) with: context, decision, alternatives, consequences. Expected initial ADRs:

- **ADR-001** — Modular monolith over microservices.
- **ADR-002** — PostgreSQL as the canonical store.
- **ADR-003** — OAuth 2.0/OIDC identity.
- **ADR-004** — Contract-first REST + OpenAPI.
- **ADR-005** — Outbox pattern for reliable events.
- **ADR-006** — Backend language/runtime selection.
- **ADR-007** — Mobile cross-platform framework.

*Pending ADRs are opened as decisions are finalized at the Phase 1 gate.*

---

## 19. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Sequences this work; gate definitions |
| [01-ENTERPRISE-VISION](01-ENTERPRISE-VISION.md) | Principles this architecture implements |
| **02-SYSTEM-ARCHITECTURE (this)** | — |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | Database architecture & schema governance |
| [11-API-STANDARDS](11-API-STANDARDS.md) | API contract & versioning standards |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Audit logging (security-relevant events) |
| [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md) | Deployment & operations standards |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created system architecture: principles, context, modular-monolith style, logical/backend/frontend/mobile/data/API/eventing/security/observability/infrastructure views, recommended stack baseline, NFRs, standards, and ADR list. |

---

*End of `02-SYSTEM-ARCHITECTURE.md`.*
