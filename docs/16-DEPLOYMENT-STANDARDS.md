# Hospital ERP Enterprise — Deployment Standards

> **Document ID:** `16-DEPLOYMENT-STANDARDS.md`
> **Owner:** SRE / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the deployment, release, and operational standards. Builds on the CI/CD direction in [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md), the environment model in [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md), and operational hardening in Phase 11.

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Deployment Principles](#2-deployment-principles)
3. [Environments](#3-environments)
4. [CI/CD Pipeline](#4-cicd-pipeline)
5. [Release & Promotion](#5-release--promotion)
6. [Zero-Downtime & Rollback](#6-zero-downtime--rollback)
7. [Configuration & Secrets](#7-configuration--secrets)
8. [Database Deployments](#8-database-deployments)
9. [Container & Artifact Management](#9-container--artifact-management)
10. [Observability & Incident Response](#10-observability--incident-response)
11. [Open Decisions](#11-open-decisions)
12. [Document Map & Dependencies](#12-document-map--dependencies)
13. [Appendix A — Change Log](#13-appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines **deployment, release, and operational standards** for the Hospital ERP Enterprise platform: environment topology, CI/CD, release promotion, zero-downtime and rollback, config/secrets, DB deployments, artifacts, and incident response.

**Scope:** deployment, release, operations. Out of scope: performance targets ([14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md)), testing gates ([15-TESTING-STANDARDS](15-TESTING-STANDARDS.md)), and data backup/DR (see [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).

---

## 2. Deployment Principles

1. **Artifact-driven.** A build promoted to production is byte-identical to what passed staging.
2. **Automated end-to-end.** No manual steps in release; CI/CD drives promotion.
3. **Rollback-first.** Every release is designed to be reversible.
4. **Immutable environments.** Config and state are managed, not hand-edited in place.
5. **Observable.** Health, metrics, and logs accompany every deploy.
6. **Safe by default.** Fail-safe deploy steps; minimal blast radius.

---

## 3. Environments

| Environment | Purpose | Refresh source | Promotion from |
| --- | --- | --- | --- |
| **Local** | Developer iteration | Fixtures + seed | — |
| **Dev** | Integration, CI | Synthetic | Build |
| **Staging** | Validation, UAT, load | Anonymized | Artifact |
| **Production** | Live operation | Production DB | Staging-approved artifact |

- **No real PHI** in local/dev/CI; anonymized data in staging ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).
- Staging mirrors production topology for accurate validation ([14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md)).

---

## 4. CI/CD Pipeline

- **CI (merge gate):** lint → build → type-check → unit → integration → coverage → SAST/dependency/container scans → contract tests ([15-TESTING-STANDARDS](15-TESTING-STANDARDS.md)).
- **CD:** build immutable artifacts and images; on approval, promote through environments.
- Workflows live in `.github/` ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).
- Pipelines are versioned; pipeline changes are reviewed like code.

---

## 5. Release & Promotion

- **Releases** are built once and promoted; each environment deploys the approved artifact.
- **Semantic versioning** and release notes; breaking changes gated and communicated ([11-API-STANDARDS](11-API-STANDARDS.md)).
- Promotion to production requires passing staging gates (tests, security, performance) and go/no-go review (Phase 11).
- Releases are scheduled with defined windows; emergency releases follow the same gates where possible.

---

## 6. Zero-Downtime & Rollback

- **MUST** prefer zero-downtime deploys (rolling/blue-green or similar).
- **MUST** support DB-compatible forward/backward deploy (schema and app release in compatible order; see §8).
- **Rollback runbook** per release: revert artifact, and where needed a data rollback path (PITR — see [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- Deploy failures auto-abort; the system remains on the previous healthy release.

---

## 7. Configuration & Secrets

- **Config** is externalized per environment; no environment-specific values in code.
- **Secrets** live in the secret manager ([04-CODING-STANDARDS](04-CODING-STANDARDS.md)); never in code, images, or logs.
- Secret rotation is automated and audited ([08-AUDIT-LOGGING](08-AUDIT-LOGGING.md)).
- Configuration changes are reviewed and versioned; drift is detected.

---

## 8. Database Deployments

- **Migrations** are applied as part of the release, in order, with a clean-DB CI validation ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- **Expand-contract** for schema changes where needed for zero-downtime (additive first, then backfill, then remove).
- **Rollback of schema** is via backup/PITR, not down-migrations ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- Migration failures abort the release and prevent promotion.

---

## 9. Container & Artifact Management

- **OCI images** built reproducibly, scanned, signed, and stored in a private registry.
- **No floating tags** in production; images pinned to immutable digests/versions.
- **Artifacts** (packages, bundles) are versioned and retained for rollback.
- Supply-chain provenance is recorded (build metadata, SBOM).

---

## 10. Observability & Incident Response

- Every service exposes **health/readiness** for orchestration ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).
- Metrics, logs, and traces shipped to the observability stack; dashboards and SLO alerts ([14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md)).
- **Incident response:** severity-based triage, on-call runbooks, status communication, and post-incident review.
- On-call readiness is established in Phase 11 ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).

---

## 11. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| DP-1 | Deploy strategy | Rolling vs blue-green | Rolling; blue-green where critical |
| DP-2 | Managed vs self-hosted infrastructure | Managed services vs self-host | Managed where available |
| DP-3 | Multi-region DR | Active/passive vs active/active | Active/passive initially |

*Confirmed at the Phase 1 gate.*

---

## 12. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md) | CI/CD, containers, orchestration |
| [05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md) | Migrations, backup/DR |
| [08-AUDIT-LOGGING](08-AUDIT-LOGGING.md) | Audit of releases/secrets |
| [14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md) | Staging load/capacity |
| [15-TESTING-STANDARDS](15-TESTING-STANDARDS.md) | Release test gates |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Environments, phases, Definition of Done |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | SRE | Created deployment standards: principles, environments, CI/CD, release & promotion, zero-downtime & rollback, config/secrets, DB deployments, containers/artifacts, observability & incident response, and open decisions. |

---

*End of `16-DEPLOYMENT-STANDARDS.md`.*
