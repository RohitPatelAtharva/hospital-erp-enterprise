# Hospital ERP Enterprise — Technology Stack

> **Document ID:** `03-TECHNOLOGY-STACK.md`
> **Owner:** Architecture / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Locks the technology baseline proposed in [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md). Authoritative on *what we build with*; the architecture doc is authoritative on *how we build*.

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Stack Selection Principles](#2-stack-selection-principles)
3. [Stack Summary](#3-stack-summary)
4. [Detailed Component Decisions](#4-detailed-component-decisions)
5. [Versioning & Pinning Policy](#5-versioning--pinning-policy)
6. [Interoperability & Standards](#6-interoperability--standards)
7. [Licensing & Cost Considerations](#7-licensing--cost-considerations)
8. [Open Decisions / Approval Gate](#8-open-decisions--approval-gate)
9. [Appendix A — Decision Records](#appendix-a--decision-records)
10. [Appendix B — Change Log](#appendix-b--change-log)

---

## 1. Purpose & Scope

This document defines the **concrete technology stack** for the Hospital ERP Enterprise platform: the languages, frameworks, databases, and services we build with, why each was chosen, and how versions are managed.

It is the approved baseline against which all implementation proceeds. It deliberately separates **stack choice** (here) from **system structure** ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).

**Scope:** decisions about technology selection and versioning. Out of scope: schema design ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)), API contracts ([11-API-STANDARDS](11-API-STANDARDS.md)), security controls ([08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)), and infrastructure operations ([16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md)).

**Gate:** Per the roadmap Phase 1 gate, implementation begins only after this baseline is approved. Items marked ⚠️ **proposed** require sign-off; items marked ✅ **approved** are locked.

---

## 2. Stack Selection Principles

Every choice is tested against these principles (derived from the vision and architecture docs):

1. **Maturity & longevity** — broadly adopted, long-lived technologies with strong community and hiring pools.
2. **Open standards over proprietary lock-in** — OpenAPI, OIDC, OpenTelemetry, FHIR.
3. **Security posture** — active security maintenance; no EOL components; strong supply-chain visibility.
4. **Operability** — tooling for observability, deployment, and debugging is first-class.
5. **Fit for a clinical/financial domain** — strong typing, transactional integrity, auditability.
6. **Team velocity** — reduces boilerplate without sacrificing correctness.

---

## 3. Stack Summary

> Legend: ✅ Approved · ⚠️ Proposed · 🔶 Conditional

| Layer | Choice | Status |
| --- | --- | --- |
| **Backend language/runtime** | ⚠️ C#/.NET (primary proposal) | Proposed |
| **API framework** | ⚠️ .NET Web API | Proposed |
| **Primary database** | ⚠️ PostgreSQL 16+ | Proposed |
| **Search** | ⚠️ OpenSearch | Proposed |
| **Cache** | ⚠️ Redis | Proposed |
| **Message bus** | ⚠️ Apache Kafka | Proposed |
| **Object storage** | ⚠️ S3-compatible (MinIO local) | Proposed |
| **Frontend (web)** | ⚠️ React + TypeScript | Proposed |
| **Mobile** | ⚠️ React Native | Proposed |
| **Identity** | ⚠️ OAuth 2.0 / OIDC (Keycloak or native) | Proposed |
| **Containers / orchestration** | ⚠️ Docker + Kubernetes | Proposed |
| **CI/CD** | ✅ GitHub Actions (repo-native) | Approved |
| **Observability** | ⚠️ OpenTelemetry + Prometheus + Grafana + Loki | Proposed |

---

## 4. Detailed Component Decisions

### 4.1 Backend language & runtime

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ C#/.NET (LTS) |
| **Rationale** | Strong typing & async, mature enterprise ecosystem, excellent performance, first-class DI/middleware, long-term support, large talent pool. |
| **Alternatives** | Java/Spring (viable equivalent — final pick between the two at gate); Node/TS (higher velocity, weaker enterprise/clinical conventions); Go (great for infra, thinner domain ecosystem); Python (not preferred for this domain). |
| **Consequence** | Pins backend conventions, test framework, and packaging to the .NET ecosystem. |

### 4.2 API framework

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ .NET Web API (ASP.NET Core) |
| **Rationale** | REST, OpenAPI generation, middleware pipeline, built-in auth integration, performance. |
| **Alternatives** | Spring Boot; Express; FastAPI. |

### 4.3 Primary database

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ PostgreSQL 16+ |
| **Rationale** | ACID/relational integrity (critical for clinical + financial), JSONB for flexible clinical payloads, mature, open source, strong concurrency, excellent tooling and extensions. |
| **Alternatives** | MySQL (similar but weaker JSON/advanced features); SQL Server (commercial, strong but proprietary). |
| **Consequence** | All transactional integrity relies on this; migrations versioned in `database/`. |

### 4.4 Search

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ OpenSearch |
| **Rationale** | Full-text + fuzzy search for patient lookup and duplicate matching; robust at scale; open-source distribution of the Elasticsearch lineage. |
| **Alternatives** | Elasticsearch (license differences); PostgreSQL full-text (adequate early, weaker at scale). |

### 4.5 Cache

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ Redis |
| **Rationale** | Low-latency cache, session store, rate-limit counters, pub/sub; ubiquitous and battle-tested. |
| **Alternatives** | In-memory (not shared across instances); Memcached (cache only). |

### 4.6 Message bus

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ Apache Kafka |
| **Rationale** | Durable, ordered, replayable event stream for cross-module concerns (notifications, audit, projections, integrations); strong at-least-once semantics with consumer groups. |
| **Alternatives** | RabbitMQ (simpler broker, fine for moderate throughput); Redis Streams (lightweight, less durable). |

### 4.7 Object storage

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ S3-compatible (MinIO in dev/staging) |
| **Rationale** | Documents, images, FHIR bundles, exports; S3 API is a de-facto standard, portable across clouds. |
| **Alternatives** | Cloud-native object stores; filesystem (not recommended for prod). |

### 4.8 Frontend (web)

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ React 18+ + TypeScript |
| **Rationale** | Large ecosystem, component reuse across portals, strong typing for shared contracts, excellent maintainability and hiring pool. |
| **Alternatives** | Vue, Angular (both viable; React chosen for ecosystem and team fit). |

### 4.9 Mobile

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ React Native |
| **Rationale** | Single codebase for iOS/Android patient & staff apps; aligns with React web skills; adequate native performance; large ecosystem. |
| **Alternatives** | Flutter (Dart, also strong); Native Swift/Kotlin (best control, two codebases). |

### 4.10 Identity & access

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ OAuth 2.0 / OIDC (Keycloak or native implementation) |
| **Rationale** | Standards-based authentication/authorization, MFA, session + refresh handling, integrates with web/mobile/API. |
| **Decision point** | Whether to adopt Keycloak (fast, feature-rich) vs. a native OIDC implementation (more control, more work) — **resolved at the Phase 1 gate**. |

### 4.11 Containers & orchestration

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ Docker (Compose in dev) + Kubernetes (prod) |
| **Rationale** | Portable, standard, scales horizontally; reproducible environments. |
| **Alternative** | Docker Compose-only (acceptable early; K8s adopted as production demands). |

### 4.12 CI/CD

| Aspect | Detail |
| --- | --- |
| **Choice** | ✅ GitHub Actions |
| **Rationale** | Native to the repository; gated pipelines (lint → build → test → security scan); no additional tool. |
| **Detail** | Workflows under `.github/`; artifact-driven promotion across environments. |

### 4.13 Observability

| Aspect | Detail |
| --- | --- |
| **Choice** | ⚠️ OpenTelemetry (tracing/metrics) + Prometheus + Grafana + Loki |
| **Rationale** | Open standard instrumentation, vendor-neutral, self-hosted option, mature dashboards/alerting. |
| **Alternatives** | Datadog (commercial, excellent but cost); ELK (log-focused, weaker metrics). |

---

## 5. Versioning & Pinning Policy

- **Pin exact versions** at implementation time and record them in an **ADR** and the dependency manifests.
- **Track LTS** for the primary runtime and frameworks; adopt new LTS within a defined window (e.g., ≤ 12 months of release).
- **No floating/latest** tags in production deployments.
- **Dependency updates** are automated (Dependabot/Renovate) and gated on passing CI + security scans.
- **End-of-life rule:** no component may be used past its EOL; upgrade is planned ahead of EOL with an ADR.

---

## 6. Interoperability & Standards

- **APIs:** OpenAPI 3.x (see [11-API-STANDARDS](11-API-STANDARDS.md)).
- **Identity:** OAuth 2.0 / OIDC.
- **Interoperability:** FHIR R4 for clinical exchange; HL7 where required (see Phase 10 and [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md) for integration surface).
- **Observability:** OpenTelemetry for traces/metrics.
- **Containers:** OCI images.

These standards are chosen to **avoid lock-in** and to make external integration predictable.

---

## 7. Licensing & Cost Considerations

- **Preference for permissive/open licensing** (PostgreSQL, Redis, React, OpenSearch, Grafana, Prometheus, Loki) to avoid per-seat or per-node licensing surprises.
- **Notable commercial items** to budget: container registry/storage, managed services (if adopted), observability hosting (if not self-hosted), and any enterprise middleware.
- A **cost model** is prepared in [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md); this document records the licensing implications of each choice.

---

## 8. Open Decisions / Approval Gate

The following must be **explicitly approved** (or revised) to close the Phase 1 gate:

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| D1 | Backend language | .NET vs Java | .NET |
| D2 | Identity solution | Keycloak vs native OIDC | Keycloak (evaluate) |
| D3 | Mobile framework | React Native vs Flutter | React Native |
| D4 | Message bus | Kafka vs RabbitMQ | Kafka |
| D5 | Search | OpenSearch vs Elasticsearch vs PG FTS | OpenSearch |

**Approval:** review and sign off each decision (or state a preferred alternative) in this document. A renumber-free record of decisions is kept in **Appendix A**.

---

## Appendix A — Decision Records

> Final confirmed decisions reference the ADRs maintained in the codebase (`docs/adr/`). Status: pending until the gate.

| ADR | Title | Status |
| --- | --- | --- |
| ADR-006 | Backend language/runtime selection | ⚠️ Pending |
| ADR-002 | PostgreSQL as canonical store | ⚠️ Pending |
| ADR-003 | OAuth 2.0/OIDC identity | ⚠️ Pending |
| ADR-007 | Mobile cross-platform framework | ⚠️ Pending |
| — | Message bus / search / caching | ⚠️ Pending |

---

## Appendix B — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created technology stack: selection principles, full component decisions with rationale/alternatives, versioning policy, standards, licensing, and the approval-gate decision list. |

---

*End of `03-TECHNOLOGY-STACK.md`.*
