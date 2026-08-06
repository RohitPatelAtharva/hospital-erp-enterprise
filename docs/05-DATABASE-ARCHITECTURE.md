# Hospital ERP Enterprise — Database Architecture

> **Document ID:** `05-DATABASE-ARCHITECTURE.md`
> **Owner:** Architecture / Engineering Lead (data)
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines *how data is stored, protected, and accessed*. The logical schema (entities/tables) is managed as versioned migrations in `database/`; storage choices derive from [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Database Principles](#2-database-principles)
3. [Storage Component Map](#3-storage-component-map)
4. [Database Topology & High Availability](#4-database-topology--high-availability)
5. [Migration & Schema Management](#5-migration--schema-management)
6. [Transactions, Concurrency & Consistency](#6-transactions-concurrency--consistency)
7. [Indexing & Performance](#7-indexing--performance)
8. [Data Partitioning, Archival & Retention](#8-data-partitioning-archival--retention)
9. [Data Access Patterns](#9-data-access-patterns)
10. [Backup & Recovery](#10-backup--recovery)
11. [Security at the Database Layer](#11-security-at-the-database-layer)
12. [Observability & Monitoring](#12-observability--monitoring)
13. [Non-Functional Targets](#13-non-functional-targets)
14. [Open Decisions](#14-open-decisions)
15. [Document Map & Dependencies](#15-document-map--dependencies)
16. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **database and data-storage architecture** for the Hospital ERP Enterprise platform: what storage components exist, how they are deployed and made highly available, how schema changes are managed, how data is accessed, backed up, secured, and retained.

**Scope:** storage topology, migrations, transactions/concurrency, indexing, partitioning, backups, security, monitoring. It does **not** enumerate the full logical data model (tables/relationships), which is governed through versioned schema migrations (see [04-CODING-STANDARDS](04-CODING-STANDARDS.md)), nor the API surface (see [11-API-STANDARDS](11-API-STANDARDS.md)).

**Gate:** pending approval as part of the Phase 1 design set.

---

## 2. Database Principles

1. **Single source of truth.** The primary relational database is the system of record; derived stores (search, cache, reporting) are projections.
2. **Transactional integrity first.** Clinical and financial writes are ACID; no partial states.
3. **Consistency over convenience.** Prefer a consistent canonical model over denormalized convenience.
4. **Separation of read/write paths.** Analytics and search read from projections, not the OLTP system.
5. **Data protection by default.** Encryption at rest and in transit; least privilege; no PHI outside production.
6. **Schema is code.** All changes are versioned migrations reviewed like code.
7. **Operable and observable.** Backups, health, and capacity are monitored and documented.

---

## 3. Storage Component Map

| Component | Role | Consistency model | Source of truth |
| --- | --- | --- | --- |
| **PostgreSQL (primary)** | System of record for all canonical data | ACID, strong | Yes |
| **OpenSearch** | Search & duplicate matching index | Eventually consistent (projection) | Projection of primary |
| **Redis** | Cache, sessions, rate-limit counters, short-lived state | Volatile, best-effort | Cache of primary |
| **Object storage (S3/MinIO)** | Documents, images, FHIR bundles, exports | Strong (object immutable) | Canonical file store |
| **Kafka** | Durable event/outbox stream | At-least-once, replayable | Event history |

**Rule:** applications MUST write authoritative state to PostgreSQL; they MUST NOT write directly to projections (search/cache) — those are updated via events.

---

## 4. Database Topology & High Availability

### 4.1 Development / lower environments
- Single PostgreSQL instance (Docker Compose) with seed data; no PHI (synthetic only).
- Reproducible from a clean clone (Phase 0 requirement).

### 4.2 Staging
- Mirrors production topology at reduced scale; anonymized sample data.

### 4.3 Production
- **Primary (writer)** node for all writes and consistent reads.
- **Replica(s)** for read scaling and failover; streaming replication.
- **High availability:** synchronous/async standby with automated failover; multi-AZ where supported.
- **Disaster recovery:** cross-region backup/restore capability with defined RPO/RTO (see §10).

### 4.4 Decision rules
- Read/write splitting is introduced when read load justifies it; until then, single primary serves reads.
- Failover is tested in drills, not only in theory (Phase 11).

---

## 5. Migration & Schema Management

- **Migrations are code:** versioned files under `database/`, applied in order, reviewed in PRs.
- **Forward-only by default:** migrations add/alter; destructive changes are explicit, gated, and reversible-by-backup (not by down-migration).
- **Migration workflow:** `down` migrations are generally avoided; rollback is via point-in-time recovery (PITR) rather than down migrations.
- **CI gate:** migrations run against a clean database in CI to catch drift and conflicts.
- **Seeding:** reference/seed data is versioned; environment-specific seeds are separated from schema.
- **Testing:** schema is exercised by integration tests; migrations are validated for idempotency and order.

---

## 6. Transactions, Concurrency & Consistency

- **ACID** on the primary store; explicit transactions for multi-statement writes.
- **Isolation level:** default `Read Committed`; use `Serializable` where race conditions are unacceptable (e.g., slot booking to prevent double-booking).
- **Optimistic concurrency** for long-lived edits (version columns / row versioning) to avoid lost updates on clinical records.
- **Locks:** keep transactions short; avoid long-held locks and table scans under load.
- **Outbox pattern:** cross-module events are written in the same transaction as the source state and published reliably afterward (see [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).
- **Idempotency** enforced for integrations and payments at the application layer; unique constraints back critical operations.

---

## 7. Indexing & Performance

- **Indexes** are defined in the schema migrations in `database/` for hot query paths (patient search, encounter lookup, order/result queries); naming per [04-CODING-STANDARDS](04-CODING-STANDARDS.md).
- **MUST** avoid N+1 query patterns; use joins/loading appropriate to the ORM.
- **Query analysis:** EXPLAIN/ANALYZE on slow queries; index on WHERE/JOIN/ORDER columns.
- **Vacuum & statistics** maintenance scheduled (autovacuum tuned) for steady-state performance.
- **Connection pooling** sized and monitored; avoid connection churn.
- **Performance budget:** p95 sub-second for core OLTP reads; verified by load testing (Phase 11).

---

## 8. Data Partitioning, Archival & Retention

- **Partitioning** by time for high-volume tables (encounters, audit log, results) to bound table size and speed maintenance.
- **Archival** of old/inactive data per defined policy; archives stored in object storage.
- **Retention schedule** per data class (defined in [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) and compliance matrix in [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)); automated, audited.
- **Purge/deletion** is logged and irreversible by policy; deletion of clinical records follows consent/legal requirements.

---

## 9. Data Access Patterns

- **Application access:** via a data-access layer / ORM with parameterized queries (never raw string SQL).
- **Connection management:** pooled, short-lived connections; configured per environment.
- **Read/write routing:** application uses primary for writes; read replicas for read-only paths where configured.
- **No direct DB access** from the presentation/mobile layers — all access via API ([11-API-STANDARDS](11-API-STANDARDS.md)).
- **Reporting/analytics** read from projections or a staging store, not the OLTP primary.

---

## 10. Backup & Recovery

| Aspect | Target |
| --- | --- |
| **Recovery Point Objective (RPO)** | ≤ 15 minutes (PITR via WAL archiving) |
| **Recovery Time Objective (RTO)** | ≤ 1 hour to restore service |
| **Backup type** | Full + incremental/WAL; automated, monitored |
| **Retention** | Defined (e.g., daily for 30 days, weekly for 12 months, long-term per compliance) |
| **Testing** | Restore drills on a schedule (minimum quarterly); documented runbook |
| **Off-site** | Backups in a separate region/location from primary |

**MUST** treat backup success/failure as a monitored, alerting metric — not a batch-and-forget task.

---

## 11. Security at the Database Layer

- **Encryption at rest:** enabled on all persistent stores; key management centralized.
- **Encryption in transit:** TLS enforced between app and DB, and between DB nodes.
- **Least privilege:** dedicated DB roles per service; no shared superuser in application paths.
- **Secrets:** DB credentials in the secret manager; never in code/env templates ([04-CODING-STANDARDS](04-CODING-STANDARDS.md)).
- **Network segmentation:** DB not exposed publicly; reachable only from authorized app/backup paths.
- **Audit:** database-level and application-level audit of sensitive operations; append-only.
- **No PHI in non-production** environments, enforced by policy and data masking.

---

## 12. Observability & Monitoring

- **Metrics:** connections, replication lag, latency percentiles, disk/IO, vacuum age, cache hit ratio, slow query count.
- **Logs:** structured DB logs forwarded to the observability stack; no secrets/PII.
- **Alerts:** replication lag, failover events, backup failure, capacity thresholds, connection saturation.
- **Dashboards** owned by platform/ops; reviewed at on-call readiness (Phase 11).

---

## 13. Non-Functional Targets

| Category | Target |
| --- | --- |
| **Availability** | ≥ 99.9% for the database tier post-launch |
| **RPO / RTO** | ≤ 15 min / ≤ 1 hour |
| **Latency** | p95 sub-second for core OLTP reads |
| **Capacity** | Headroom for growth; re-sized at Phase 11 load testing |
| **Security** | Encryption everywhere; least privilege; zero critical findings |
| **Retention** | Automated, compliant schedule per data class |

---

## 14. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| DB-1 | HA strategy for primary DB | Managed service vs self-hosted + replicas | Managed where available; else self-hosted standby |
| DB-2 | Read/write splitting timing | Immediate replicas vs single primary first | Single primary first; replicas when read load demands |
| DB-3 | Archival destination | Object storage vs partitioned in-DB | Object storage for cold archival |

*These are confirmed at the Phase 1 gate.*

---

## 15. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) | Module/data architecture this implements |
| [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md) | Storage technology choices |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | SQL/data-access coding rules |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Data model scope & sequencing |
| [11-API-STANDARDS](11-API-STANDARDS.md) | Data access surface |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Security controls & retention |
| [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md) | Deployment & operations |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Architecture | Created database architecture: storage map, topology & HA, migrations, transactions/concurrency, indexing, partitioning/retention, access patterns, backup/recovery (RPO/RTO), security, observability, NFRs, and open decisions. |

---

*End of `05-DATABASE-ARCHITECTURE.md`.*
