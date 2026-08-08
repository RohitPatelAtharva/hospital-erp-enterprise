# Master Data Module — Performance

> **Document ID:** `master-data/19-Performance`
> **Owner:** Engineering Lead (performance)
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate and when load profiles change.
>
> **Relationship:** This document defines the **performance** requirements of the Master Data Management module — response targets, throughput, scalability, and optimization. It follows [14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md) as authoritative and the indexing/optimization guidance in [03-Database](03-Database.md) and [05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md).

---

## Table of Contents

1. [Purpose](#1-purpose)
2. [Scope](#2-scope)
3. [Performance Objectives](#3-performance-objectives)
4. [Performance Principles](#4-performance-principles)
5. [Response Targets](#5-response-targets)
6. [Throughput](#6-throughput)
7. [Scalability](#7-scalability)
8. [Concurrency](#8-concurrency)
9. [Database Performance](#9-database-performance)
10. [Query Optimization](#10-query-optimization)
11. [Indexing](#11-indexing)
12. [Caching](#12-caching)
13. [Queue Performance](#13-queue-performance)
14. [API Performance](#14-api-performance)
15. [Search Performance](#15-search-performance)
16. [Duplicate Detection Performance](#16-duplicate-detection-performance)
17. [Merge Performance](#17-merge-performance)
18. [Import Performance](#18-import-performance)
19. [Export Performance](#19-export-performance)
20. [Dashboard Performance](#20-dashboard-performance)
21. [Report Performance](#21-report-performance)
22. [Monitoring](#22-monitoring)
23. [Alert Thresholds](#23-alert-thresholds)
24. [Capacity Planning](#24-capacity-planning)
25. [Performance Testing](#25-performance-testing)
26. [Optimization](#26-optimization)
27. [Cross References](#27-cross-references)

---

## 1. Purpose

Define performance targets so the Master Data module remains responsive, scalable, and predictable under expected and peak load.

---

## 2. Scope

API, search, database, queue, import/export, and dashboard/report performance. Targets align to [14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md).

---

## 3. Performance Objectives

| Objective | Target |
| --- | --- |
| Responsive registry | p95 sub-second for core reads |
| Fast search | Sub-second result set |
| Reliable imports | Predictable throughput |
| Scalable | Scale under growth ([24-Future-Roadmap](24-Future-Roadmap.md) §17) |

---

## 4. Performance Principles

| # | Principle | Application |
| --- | --- | --- |
| PF-01 | Measure first | Profile before optimize |
| PF-02 | Index hot paths | [03-Database](03-Database.md) §16 |
| PF-03 | Cache reads | Redis ([12](#12-caching)) |
| PF-04 | Bound queries | Pagination + limits ([10-API](10-API.md) §25) |
| PF-05 | Async for heavy | Import/export async ([17-Import-Export](17-Import-Export.md)) |

---

## 5. Response Targets

| Operation | p95 target |
| --- | --- |
| Registry read (single) | < 200 ms |
| Registry list (page) | < 400 ms |
| Search | < 500 ms |
| Duplicate detection | < 1 s |
| Merge | < 2 s |
| Approval action | < 1 s |
| Dashboard (interactive) | < 1 s |
| Report (interactive) | < 2 s |

---

## 6. Throughput

| Surface | Target |
| --- | --- |
| API reads | ≥ 100 RPS sustained |
| Search | ≥ 50 QPS |
| Imports | ≥ 1000 rows/min |
| Exports | ≥ 1000 rows/min |
| Notifications | ≥ 1000/s burst ([14-Notifications](14-Notifications.md)) |

---

## 7. Scalability

| Aspect | Decision |
| --- | --- |
| Stateless | Services scale horizontally |
| Search | OpenSearch scales ([03-TECHNOLOGY-STACK](../../03-TECHNOLOGY-STACK.md)) |
| Cache | Redis cluster |
| DB | Read replicas where justified ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §4) |
| Queue | Partitioned Kafka |

---

## 8. Concurrency

| Aspect | Decision |
| --- | --- |
| Optimistic | Long-lived edits via versioning ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §6) |
| Locks | Short transactions |
| Isolation | Read Committed; Serializable for critical ([03-Database](03-Database.md) §13) |
| Pooling | Sized + monitored |

---

## 9. Database Performance

| Aspect | Decision |
| --- | --- |
| PostgreSQL | Primary store ([03-Database](03-Database.md)) |
| Connection pool | Bounded, monitored |
| Autovacuum | Tuned ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §7) |
| Slow query | EXPLAIN/ANALYZE |

---

## 10. Query Optimization

| Aspect | Decision |
| --- | --- |
| N+1 | Avoid; join/load appropriately |
| Projection | Select needed columns |
| Bound | WHERE/JOIN indexed |
| Read models | Projections for hot reads ([06-ERD](06-ERD.md) §24) |

---

## 11. Indexing

| Table/column | Index |
| --- | --- |
| Identifiers | `identity_type` + `value` unique |
| Master lookup | `tenant_id`, `status` |
| Search | OpenSearch index |
| Audit | `tenant_id`, `time`, `event_type` |
| Reference | `category`, `code` |

> Indexes defined in [03-Database](03-Database.md) §16 and [04-Database-Tables](04-Database-Tables.md).

---

## 12. Caching

| Aspect | Decision |
| --- | --- |
| Cache | Redis ([03-TECHNOLOGY-STACK](../../03-TECHNOLOGY-STACK.md)) |
| Reference/lookup | Hot cache |
| Golden view | Cacheable projection |
| Invalidation | Event-driven ([03-Database](03-Database.md) §15) |
| TTL | Bounded |

---

## 13. Queue Performance

| Aspect | Decision |
| --- | --- |
| Transport | Kafka ([03-TECHNOLOGY-STACK](../../03-TECHNOLOGY-STACK.md)) |
| Consumer lag | Monitored |
| Backlog | Alert on growth |

---

## 14. API Performance

| Aspect | Decision |
| --- | --- |
| Pagination | Cursor ([10-API](10-API.md) §25) |
| Rate limit | Gateway ([10-API](10-API.md) §29) |
| Idempotency | Replay-safe |
| Observability | Latency metrics ([10-API](10-API.md) §31) |

---

## 15. Search Performance

| Aspect | Decision |
| --- | --- |
| Backend | OpenSearch |
| Fuzzy | Name/identifier fuzzy ([06-ERD](06-ERD.md) §24) |
| Index refresh | Near-real-time |
| Result | Bounded, ranked |

---

## 16. Duplicate Detection Performance

| Aspect | Decision |
| --- | --- |
| Screening | Async, non-blocking |
| Batch | Chunked detection |
| Index | Pre-indexed candidate fields |
| Preview | Threshold-based triage ([04-Database-Tables](04-Database-Tables.md) §19) |

---

## 17. Merge Performance

| Aspect | Decision |
| --- | --- |
| Transaction | Bounded-size merge |
| Survivorship | Precomputed rules ([06-ERD](06-ERD.md) §15) |
| Idempotent | Retry-safe |
| Audit | In same transaction ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §6) |

---

## 18. Import Performance

| Aspect | Decision |
| --- | --- |
| Batching | Chunked writes |
| Parallel | Bounded parallelism |
| Backpressure | Queue-bounded ([17-Import-Export](17-Import-Export.md) §19) |
| Throughput | ≥ 1000 rows/min |

---

## 19. Export Performance

| Aspect | Decision |
| --- | --- |
| Streaming | Large exports streamed |
| Object storage | Output to S3/MinIO |
| Backpressure | Bounded |
| Throughput | ≥ 1000 rows/min |

---

## 20. Dashboard Performance

| Aspect | Decision |
| --- | --- |
| Pre-aggregate | KPI rolls for large data |
| Query | Bounded time range |
| Cache | Dashboard cache |
| Target | p95 < 1 s interactive ([16-Dashboards](16-Dashboards.md) §22) |

---

## 21. Report Performance

| Aspect | Decision |
| --- | --- |
| Pre-aggregate | Long-running reports pre-built |
| Async | Heavy reports queued |
| Target | p95 < 2 s interactive ([15-Reports](15-Reports.md) §25) |

---

## 22. Monitoring

| Metric | Source |
| --- | --- |
| Latency | API/service metrics ([14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md)) |
| Throughput | Request counts |
| DB | Connection, slow query |
| Search | Query latency |
| Queue | Consumer lag |
| Cache | Hit ratio |

---

## 23. Alert Thresholds

| Metric | Alert |
| --- | --- |
| p95 API latency | > 1 s sustained |
| Search latency | > 1 s |
| Import failure | Batch error rate up |
| Queue lag | Growing backlog |
| DB connections | Near pool limit |
| Cache hit ratio | Drop |

---

## 24. Capacity Planning

| Aspect | Decision |
| --- | --- |
| Baseline | Load test (Phase 11) |
| Growth | Modeled per [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) |
| Review | At gates |
| Reserve | Headroom for peak |

---

## 25. Performance Testing

| Aspect | Decision |
| --- | --- |
| Load | Volume tests ([20-Testing](20-Testing.md) §24) |
| Stress | Peak + saturation |
| Soak | Long-run stability |
| Benchmark | Baselines committed |

---

## 26. Optimization

| Aspect | Decision |
| --- | --- |
| Index | Add on hot paths |
| Cache | Expand reference/golden |
| Query | Rewrite slow queries |
| Async | Offload heavy work |
| Partition | High-volume tables ([05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) §8) |

---

## 27. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md) | Standard | Consumes |
| [03-Database](03-Database.md) | Indexing/optimization | Consumes |
| [05-DATABASE-ARCHITECTURE](../../05-DATABASE-ARCHITECTURE.md) | DB architecture | Consumes |
| [10-API](10-API.md) | API | Consumes |
| [17-Import-Export](17-Import-Export.md) | Data exchange | Consumes |
| [16-Dashboards](16-Dashboards.md) | Dashboards | Consumes |
| [15-Reports](15-Reports.md) | Reports | Consumes |
| [20-Testing](20-Testing.md) | Performance testing | Consumes |

---

*End of `docs/modules/master-data/19-Performance.md`.*
