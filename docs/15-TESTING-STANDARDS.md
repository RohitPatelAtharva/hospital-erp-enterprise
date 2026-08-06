# Hospital ERP Enterprise — Testing Standards

> **Document ID:** `15-TESTING-STANDARDS.md`
> **Owner:** QA / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the testing strategy and quality gates. Builds on the testing rules in [04-CODING-STANDARDS](04-CODING-STANDARDS.md) and the Definition of Done in [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Testing Principles](#2-testing-principles)
3. [Test Strategy & Pyramid](#3-test-strategy--pyramid)
4. [Test Types](#4-test-types)
5. [Coverage & Quality Gates](#5-coverage--quality-gates)
6. [Test Data Management](#6-test-data-management)
7. [Environment & Tooling](#7-environment--tooling)
8. [Clinical & Financial Safety Testing](#8-clinical--financial-safety-testing)
9. [Accessibility & UX Testing](#9-accessibility--ux-testing)
10. [Performance & Load Testing](#10-performance--load-testing)
11. [Security & Compliance Testing](#11-security--compliance-testing)
12. [Test Ownership & Lifecycle](#12-test-ownership--lifecycle)
13. [Open Decisions](#13-open-decisions)
14. [Document Map & Dependencies](#14-document-map--dependencies)
15. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **testing strategy and standards** for the Hospital ERP Enterprise platform: what is tested, how, to what coverage, and what gates protect the default branch and releases.

**Scope:** test types, strategy, coverage, data management, tooling, safety testing, accessibility/UX, performance, security. Out of scope: performance targets (see [14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md)) and deployment/release process (see [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md)).

---

## 2. Testing Principles

1. **Tested, not aspirational.** Automated tests are a release gate, not a nice-to-have.
2. **Risk-proportionate.** The depth of testing scales with clinical/financial/security risk.
3. **Behavior over implementation.** Test observable behavior, not internals.
4. **Deterministic.** Tests are reliable, repeatable, and independent.
5. **Fast feedback.** Fast unit tests, gated integration/E2E, quick CI signal.
6. **Safety-critical coverage.** Clinical and financial critical paths are comprehensively covered.

---

## 3. Test Strategy & Pyramid

- **Unit tests** (fast, many) — pure logic, domain rules, single units.
- **Integration tests** (fewer) — services with real DB/queues, cross-boundary flows.
- **E2E tests** (fewest) — critical user journeys across the full stack.
- **Contract tests** — API contracts vs. OpenAPI ([11-API-STANDARDS](11-API-STANDARDS.md)).
- **Safety-critical flows** get additional integration + E2E coverage (§8).

---

## 4. Test Types

| Type | Scope | Where run |
| --- | --- | --- |
| **Unit** | Functions, domain rules | CI (fast) |
| **Integration** | Modules/services with real dependencies | CI |
| **API / contract** | Endpoints vs OpenAPI schema | CI |
| **E2E** | Critical journeys (web/mobile) | CI (staged) |
| **Accessibility** | WCAG automated + manual | CI + release |
| **Performance** | Latency/throughput/capacity | Staging (Phase 11) |
| **Security** | SAST/DAST/dependency scans | CI + release |
| **Manual/UAT** | Exploratory, acceptance | Staging |

---

## 5. Coverage & Quality Gates

- **Coverage thresholds** set per module in CI; **critical paths ≥ 80%** (roadmap KPI).
- **MUST** block merge if: build/lint/type checks fail, coverage below threshold, security/dependency scan has high/critical findings, or required tests fail.
- **MUST NOT** skip/disable tests without a tracked issue; critical tests cannot be disabled by default.
- Coverage and quality gates are part of the Definition of Done ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).

---

## 6. Test Data Management

- **No real PHI** in non-production environments, ever ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).
- Use **synthetic** and **anonymized** fixtures; deterministic datasets for repeatability.
- Versioned seed/fixture data under `tests/` and `database/`.
- Test data is isolated per test; no cross-test contamination.
- Environments are refreshed from controlled sources, never from production data.

---

## 7. Environment & Tooling

- **CI** runs lint → build → unit → integration → coverage → scans → E2E (staged) as blocking gates ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).
- A **clean-DB migration run** in CI validates schema ([05-DATABASE-ARCHITECTURE](05-DATABASE-ARCHITECTURE.md)).
- Tests are containerized and reproducible from a clean clone.
- Flakiness is treated as a defect: flaky tests are quarantined and fixed, not ignored.

---

## 8. Clinical & Financial Safety Testing

- **MUST** cover the roadmap's safety-critical scenarios (order → result → review; charge → claim → collect) with integration + E2E tests.
- **MUST** test negative/guardrail cases (no double-booking, no invalid order release, separation-of-duties violations blocked).
- **MUST** test data integrity (constraints, referential integrity, optimistic concurrency).
- Safety testing is an explicit Phase 5/7 exit criterion ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).

---

## 9. Accessibility & UX Testing

- **Automated** WCAG checks in CI; **manual** accessibility review on key journeys ([12-UI-UX-GUIDELINES](12-UI-UX-GUIDELINES.md)).
- Component accessibility tests per [13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md).
- Usability/UAT with representative personas on staging.

---

## 10. Performance & Load Testing

- Performance testing follows [14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md).
- Load/capacity tests run on staging mirroring production topology (Phase 11).
- Results documented and tied to SLOs.

---

## 11. Security & Compliance Testing

- **SAST** (static) and **DAST** (dynamic) in CI and at release.
- **Dependency** and **container image** scans; high/critical findings block release.
- **Penetration testing** and **compliance audit** at Phase 11 ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md)).
- Authorization tests (negative cases, scoping, separation of duties) per [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md).

---

## 12. Test Ownership & Lifecycle

- **Developers** own unit/integration tests for their code; **QA** owns E2E, UAT, and cross-cutting suites.
- Tests are reviewed in PRs like code.
- Test failures during release stop the pipeline ([16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md)).
- Test strategy is revisited each phase gate.

---

## 13. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| T-1 | E2E scope (v1) | Critical journeys only vs broader | Critical journeys + safety-critical |
| T-2 | Coverage threshold | 80% global vs per-module | Per-module with critical ≥ 80% |
| T-3 | Visual regression | Include vs defer | Include for design-system components |

*Confirmed at the Phase 1 gate.*

---

## 14. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Unit/integration coding rules |
| [07-ROLES-PERMISSIONS](07-ROLES-PERMISSIONS.md) | Authorization testing |
| [12-UI-UX-GUIDELINES](12-UI-UX-GUIDELINES.md) | Accessibility requirements |
| [13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md) | Component testing |
| [14-PERFORMANCE-STANDARDS](14-PERFORMANCE-STANDARDS.md) | Performance/load targets |
| [16-DEPLOYMENT-STANDARDS](16-DEPLOYMENT-STANDARDS.md) | Test gates at release |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Definition of Done |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | QA | Created testing standards: principles, strategy/pyramid, test types, coverage gates, data management, tooling, clinical/financial safety, accessibility/UX, performance, security, ownership, and open decisions. |

---

*End of `15-TESTING-STANDARDS.md`.*
