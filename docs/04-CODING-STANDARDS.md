# Hospital ERP Enterprise — Coding Standards

> **Document ID:** `04-CODING-STANDARDS.md`
> **Owner:** Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines *how code is written and reviewed* across the platform. Complements the stack ([03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md)) and architecture ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [General Principles](#2-general-principles)
3. [Applicability by Technology](#3-applicability-by-technology)
4. [Naming Conventions](#4-naming-conventions)
5. [Code Structure & Organization](#5-code-structure--organization)
6. [Documentation & Comments](#6-documentation--comments)
7. [Error Handling](#7-error-handling)
8. [Testing Standards](#8-testing-standards)
9. [Security Coding Practices](#9-security-coding-practices)
10. [Performance Considerations](#10-performance-considerations)
11. [Database & SQL Standards](#11-database--sql-standards)
12. [Git & Workflow](#12-git--workflow)
13. [Code Review Standards](#13-code-review-standards)
14. [Tooling & Enforcement](#14-tooling--enforcement)
15. [Definition of Done (Code)](#15-definition-of-done-code)
16. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **minimum, enforceable coding standards** for the Hospital ERP Enterprise platform. It exists to ensure code is consistent, reviewable, testable, secure, and maintainable across all teams — and to make reviews deterministic rather than a matter of individual taste.

**Scope:** source code in the primary languages of the platform, database/SQL, and the supporting workflow (Git, CI, review). It does **not** define architecture ([02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md)), technology selection ([03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md)), or schema design ([07-DATA-MODEL](07-DATA-MODEL.md)).

**Compliance model:** standards marked **MUST** are enforced (CI or review blocker). Standards marked **SHOULD** are strongly recommended; deviation requires a justified note in the PR. Standards marked **MAY** are optional guidance. (RFC 2119 key words.)

---

## 2. General Principles

1. **Correctness before cleverness.** Write the simplest code that is correct and readable.
2. **Code is read more than written.** Optimize for the next reader (including the author in six months).
3. **Fail safe.** Default to safe behavior, especially around clinical and financial data.
4. **Prefer explicit over implicit.** Make state changes, side effects, and non-obvious behavior explicit.
5. **Small, focused changes.** Favor many small PRs over one large one.
6. **Automate what can be automated.** Lint, format, and type-check in CI — not in review.
7. **No dead code.** Remove unused code, files, and dependencies rather than leaving them commented out.

---

## 3. Applicability by Technology

Standards apply per language; cross-cutting requirements (error handling, security, testing) apply everywhere.

| Technology | Style baseline | Primary tooling |
| --- | --- | --- |
| C# / .NET | Microsoft .NET conventions / EditorConfig + Roslyn analyzers | `dotnet format`, analyzers, `.editorconfig` |
| TypeScript / React | Airbnb or TS-ESLint style; Prettier formatting | ESLint, Prettier, `tsc --strict` |
| React Native | Same as React/TS; platform file conventions | ESLint, Prettier |
| SQL | Repository-defined convention; lowercase identifiers | Migration lint, SQL review |
| YAML / JSON / Docker | 2-space indent; consistent ordering | Prettier/YAML lint, `docker` lint |
| Markdown | 2-space, table-based (this series) | markdownlint |

---

## 4. Naming Conventions

- **MUST** use descriptive, intention-revealing names; avoid abbreviations and single-letter names (loop counters excepted).
- **C#:** `PascalCase` for types/methods/properties; `camelCase` for locals/parameters; `_camelCase` for private fields; `IPascalCase` for interfaces; `PascalCase` for constants.
- **TypeScript/JavaScript:** `camelCase` for variables/functions; `PascalCase` for types/classes/components; `SCREAMING_SNAKE` for true constants; `kebab-case` for file names; `IThing` not required (prefer `Thing` types + `thing` instances).
- **Database:** `snake_case` for tables/columns; singular table names; explicit constraint names (`fk_`, `pk_`, `uq_`, `idx_` prefixes).
- **Booleans:** use positive, predicate-style names (`isActive`, `hasInsurance`, not `notActive`).
- **Acronyms:** treat as words except where convention dictates (e.g., `Id`, `ApiClient`).

---

## 5. Code Structure & Organization

- **MUST** follow the module/layer structure from [02-SYSTEM-ARCHITECTURE](02-SYSTEM-ARCHITECTURE.md) (interface → application → domain → infrastructure inside each module).
- **MUST** keep files focused: one primary responsibility per file; prefer small cohesive files.
- **MUST** place related code (feature/folder) together; avoid cross-cutting `utils` dumping grounds.
- **SHOULD** keep functions/methods short and single-purpose; extract when complexity grows.
- **SHOULD** limit nesting depth; early-return instead of deep `if`/`else`.
- **MUST NOT** place business rules in the presentation layer or controllers; the domain owns rules.
- **Dependency direction** MUST point inward toward the domain; infrastructure may depend on domain, not vice versa.

---

## 6. Documentation & Comments

- **SHOULD** prefer self-documenting code (clear names, small functions) over comments.
- **MUST** comment the *why* when the *what* is non-obvious (business rules, clinical/financial rationale, temporal coupling).
- **MUST NOT** leave commented-out code in the tree; delete it (history preserves it).
- **Public API surfaces** (C# public types, exported TS functions) SHOULD have doc comments/TSDoc.
- **MUST** update comments when the code they describe changes; stale comments are a defect.
- **CHANGELOG/ADR**: significant behavioral changes MUST be reflected in the relevant ADR and release notes.

---

## 7. Error Handling

- **MUST** not swallow exceptions; handle them where the recovery action is known.
- **MUST** fail fast on invalid input at boundaries; validate early.
- **API errors** MUST use the standard error envelope defined in [08-API](08-API.md) (consistent codes, message, correlation id).
- **MUST** log errors with sufficient context (correlation id, operation, entity) and **MUST NOT** log PHI or secrets.
- **MUST** use structured logging (JSON) per the observability standard.
- **Async/integrations** MUST be idempotent and tolerant to retries; failures MUST surface to a dead-letter/alert path, never silently dropped.
- **MUST NOT** use exceptions for control flow.

---

## 8. Testing Standards

- **MUST** write automated tests for all new behavior (unit, and integration where it crosses boundaries).
- **Test pyramid:** many unit tests, fewer integration tests, fewest E2E tests — proportionate to risk.
- **MUST** cover clinical and financial critical paths with integration tests (per roadmap phase gates).
- **MUST** name tests to describe behavior: `Method_Condition_ExpectedResult` (C#) / descriptive `it/should` strings (JS).
- **MUST** not assert on implementation details; assert on observable behavior.
- **MUST** use deterministic data; no dependence on wall-clock, network, or real PHI.
- **MUST NOT** skip tests without a tracked issue; no disabled/ignored-by-default critical tests.
- **Coverage:** SHOULD maintain coverage thresholds set per module in CI; critical paths ≥ 80%.

---

## 9. Security Coding Practices

- **MUST** follow OWASP guidance; treat all input as untrusted and validate/sanitize at boundaries.
- **MUST** use parameterized queries / ORM binding — **never** string-concatenate SQL.
- **MUST NOT** store secrets, credentials, or keys in code or environment templates; use the secret manager.
- **MUST** enforce authorization in the service/domain layer, not only in UI routing (defense in depth).
- **MUST** apply least privilege to data access and roles.
- **MUST** not log sensitive data (PHI, tokens, PII); implement scrubbing at the logging boundary.
- **MUST** escape/encode output to prevent XSS; use framework-recommended encoding.
- **MUST** cap and validate request payloads; enforce rate limits at the gateway.
- **Dependencies** MUST be scanned in CI; failing CVEs block merge.

---

## 10. Performance Considerations

- **MUST** avoid N+1 query patterns; batch where possible.
- **MUST** paginate list endpoints; never return unbounded collections.
- **SHOULD** index hot query paths (confirmed in schema design, [07-DATA-MODEL](07-DATA-MODEL.md)).
- **MUST** use async I/O in the backend for I/O-bound work.
- **MUST** not block the UI thread; use appropriate async/worker patterns on web/mobile.
- **SHOULD** cache hot, immutable reads; invalidate on writes via events.
- **MUST** set bundle budgets on web; lazy-load non-critical routes.

---

## 11. Database & SQL Standards

- **MUST** manage schema exclusively via versioned migrations in `database/`; no ad-hoc DDL.
- **MUST** follow naming conventions in §4 (snake_case, explicit constraint names).
- **MUST** define primary keys, foreign keys, and indexes explicitly; document intent.
- **MUST** use transactions for multi-statement writes; keep transactions short.
- **MUST** not select `*` in application queries; select only needed columns.
- **SHOULD** write queries readably with consistent capitalization and alignment.
- **MUST** protect against SQL injection (see §9) and ensure deterministic, reviewable migrations.

---

## 12. Git & Workflow

- **Branching:** trunk-based development; short-lived feature branches off `main`.
- **Commit style:** conventional commits (`feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `chore:`); imperative, concise messages.
- **MUST** keep the default branch releasable; PRs are the only path to merge.
- **MUST** link commits/PRs to issues/tickets where one exists.
- **MUST** not commit generated artifacts, secrets, or local config (see `.gitignore`).
- **MUST** rebase/merge to avoid conflicting merges; keep history meaningful.

---

## 13. Code Review Standards

- **MUST** require at least one approving review from a peer; two for clinical/financial or security-sensitive changes.
- **Reviewer MUST** verify: correctness, tests, security, naming/style, and that it satisfies the ticket.
- **Author MUST** keep PRs small (SHOULD < 400 lines unless justified) and self-review before request.
- **MUST** address all review comments or explicitly close them with rationale.
- **Automation MUST** run before human review: lint, format, type-check, tests, security/dependency scan.
- **Review MUST NOT** block on style already enforced by tooling; focus human review on substance.

---

## 14. Tooling & Enforcement

Enforcement is automatic, not manual:

- **EditorConfig / .editorconfig** — indentation, charset, end-of-line, whitespace.
- **Formatters:** Prettier (TS/React/YAML/JSON/Markdown), `dotnet format` (C#).
- **Linters/analyzers:** ESLint + TS strict, Roslyn analyzers, markdownlint.
- **Type checking:** `tsc --strict` (no `any` escapes), C# nullable reference types enabled.
- **CI gates** (blocking): format → lint → type-check → build → test → coverage → dependency scan → container scan.
- **Fail-loud:** any gate failure blocks merge; no manual override bypass.

---

## 15. Definition of Done (Code)

A code change is **done** when:

1. Implements the acceptance criteria of the linked ticket.
2. Passes all CI gates (format, lint, types, build, tests, coverage, security scans).
3. Has automated tests covering new behavior and critical paths.
4. Reviewed and approved per §13.
5. No secrets, PHI, dead code, or commented-out code introduced.
6. Documentation/comments updated where behavior changed.
7. Merged to `main` with the default branch releasable.

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Engineering | Created coding standards: principles, per-technology style, naming, structure, documentation, error handling, testing, security coding, performance, SQL, Git workflow, review standards, tooling enforcement, and Definition of Done. |

---

*End of `04-CODING-STANDARDS.md`.*
