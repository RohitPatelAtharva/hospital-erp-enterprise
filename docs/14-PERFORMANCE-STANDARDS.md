# Hospital ERP Enterprise — Performance Standards

> **Document ID:** `14-PERFORMANCE-STANDARDS.md`
> **Owner:** Engineering / SRE Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines performance targets, budgets, and testing. Grounded in the NFRs in [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) and validated during Phase 11 ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Performance Principles](#2-performance-principles)
3. [Service-Level Objectives](#3-service-level-objectives)
4. [Backend Performance](#4-backend-performance)
5. [Database & Query Performance](#5-database--query-performance)
6. [Frontend Performance](#6-frontend-performance)
7. [Mobile Performance](#7-mobile-performance)
8. [Budget & Regression Guarding](#8-budget--regression-guarding)
9. [Load & Capacity Testing](#9-load--capacity-testing)
10. [Monitoring & Alerting](#10-monitoring--alerting)
11. [Open Decisions](#11-open-decisions)
12. [Document Map & Dependencies](#12-document-map--dependencies)
13. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines **performance standards** for the Hospital ERP Enterprise platform: latency and capacity targets, frontend/mobile budgets, database and query expectations, and the load-testing/monitoring that verifies them.

**Scope:** performance targets, budgets, testing, monitoring. Out of scope: general architecture ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)) and operational hardening ([16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md)).

---

## 2. Performance Principles

1. **Performance is a requirement.** Measured, budgeted, and gated — not an afterthought.
2. **User-perceived first.** Prioritize latency that users/clinicians experience.
3. **Clinical work must not wait.** Critical order/result flows are latency-sensitive.
4. **Predictable.** Avoid unbounded queries and runaway work; bound by budgets and pagination.
5. **Verifiable.** Targets are tested under realistic load and monitored in production.

---

## 3. Service-Level Objectives

| Metric | Target |
| --- | --- |
| **API p95 latency (core reads)** | < 1 s |
| **API p95 latency (core writes)** | < 1 s |
| **Availability** | ≥ 99.9% post-launch |
| **Error rate (5xx)** | < 0.1% |
| **Search result latency** | < 500 ms p95 |

SLOs are reviewed and refined at Phase 11 load testing.

---

## 4. Backend Performance

- **MUST** use async I/O for I/O-bound work; avoid blocking calls ([04-CODING-STANDARDS](04-CODING-STANDARDS.md)).
- **MUST** paginate and bound collection endpoints; no unbounded queries.
- **MUST** avoid N+1 query patterns; use joins/batching appropriate to the ORM.
- **SHOULD** cache hot, immutable reads; invalidate on writes via events.
- **MUST** keep work out of request path where possible (async/queued for non-critical effects).
- Profiling identifies hot paths; performance-critical code is measured and budgeted.

---

## 5. Database & Query Performance

- Targets from [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md): p95 sub-second core OLTP reads.
- **MUST** index hot query paths; EXPLAIN/ANALYZE on slow queries.
- **MUST** keep transactions short; avoid long-held locks and table scans.
- **MUST** monitor connection pooling, replication lag, and slow queries.
- Read/write splitting introduced when read load justifies it ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).

---

## 6. Frontend Performance

- **Bundle budgets** set and enforced in CI; code-splitting for non-critical routes.
- **Core Web Vitals targets:** LCP < 2.5 s, INP < 200 ms, CLS < 0.1 (good ranges) on representative hardware.
- **MUST** lazy-load routes and heavy components; avoid large blocking JS on first paint.
- **MUST** cache/refetch efficiently; avoid redundant API calls; use proper cache invalidation.
- Client state and server data handling per [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md).

---

## 7. Mobile Performance

- Responsive and smooth on mid-range devices; target frame rate for interactions.
- **MUST** avoid jank on scroll and list rendering (windowing for large lists).
- **Network efficiency:** minimize payloads, batch requests, cache appropriately.
- Offline handling must not degrade perceived performance; sync is backgrounded.
- Bundle size budgeted and monitored.

---

## 8. Budget & Regression Guarding

- **Budgets** (latency, bundle size, resource count) are defined and enforced in CI.
- Performance-sensitive changes are benchmarked before and after.
- **Regression guard:** a PR that degrades a measured target beyond tolerance blocks merge.
- Performance budgets live with code and are reviewed, not aspirational.

---

## 9. Load & Capacity Testing

- **Load testing** in Phase 11 ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)) on staging mirroring production topology.
- Test **realistic workloads** (concurrent users, order/result flows, search, billing) and peak scenarios.
- Measure latency percentiles, throughput, error rate, and resource utilization at scale.
- Establish **capacity headroom** and triggers for scaling.
- Results are documented and feed SLO/capacity decisions.

---

## 10. Monitoring & Alerting

- Instrument latency percentiles, error rates, throughput, and saturation ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).
- Dashboards per service; **alerts on SLO breach** (latency, error rate, availability).
- Correlate performance with events/releases to catch regressions early.
- Anomaly detection surfaces capacity or query regressions.

---

## 11. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| PF-1 | SLO enforcement | Soft vs hard (pager) | Hard for availability; soft for latency |
| PF-2 | Load tooling | Cloud vs in-house | Use managed load-testing |
| PF-3 | Frontend framework budget baseline | Set at Phase 1 vs Phase 11 | Set now, validate at 11 |

*Confirmed at the Phase 1 gate.*

---

## 12. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) | NFRs & observability |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Performance coding rules |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | DB performance targets |
| [15-TESTING-STANDARDS](15-TESTING-STANDARDS.md) | Performance testing approach |
| [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md) | Load/capacity in staging |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Engineering | Created performance standards: principles, SLOs, backend/database/frontend/mobile performance, budgets, load testing, monitoring, and open decisions. |

---

*End of `14-PERFORMANCE-STANDARDS.md`.*
