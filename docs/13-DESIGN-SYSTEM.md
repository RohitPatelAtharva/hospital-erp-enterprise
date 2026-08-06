# Hospital ERP Enterprise — Design System

> **Document ID:** `13-DESIGN-SYSTEM.md`
> **Owner:** Frontend / Design Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines the shared visual language and component library. Implements the UX guidelines in [12-UI-UX-GUIDELINES](12-UI-UX-GUIDELINES.md); built with the frontend stack in [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Design System Principles](#2-design-system-principles)
3. [Design Tokens](#3-design-tokens)
4. [Component Library](#4-component-library)
5. [Component Principles](#5-component-principles)
6. [Theming & Branding](#6-theming--branding)
7. [Accessibility in Components](#7-accessibility-in-components)
8. [Consumption & Integration](#8-consumption--integration)
9. [Versioning & Governance](#9-versioning--governance)
10. [Open Decisions](#10-open-decisions)
11. [Document Map & Dependencies](#11-document-map--dependencies)
12. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **design system** for the Hospital ERP Enterprise platform: the design tokens, component library, and rules that ensure a consistent, accessible, and maintainable interface across all web portals and mobile apps.

**Scope:** tokens, components, theming, accessibility in components, governance. Out of scope: UX principles/journeys (see [12-UI-UX-GUIDELINES](12-UI-UX-GUIDELINES.md)) and frontend implementation standards (see [04-CODING-STANDARDS](04-CODING-STANDARDS.md)).

---

## 2. Design System Principles

1. **One source of truth.** Shared tokens and components; no duplicate implementations.
2. **Accessible by default.** Every component meets accessibility standards without extra work.
3. **Composable.** Small, focused components compose into complex UI.
4. **Predictable.** Consistent behavior and appearance across all surfaces.
5. **Themeable.** Token-driven theming for branding and contexts.
6. **Governed.** Change is reviewed and versioned; breaking changes are managed.

---

## 3. Design Tokens

- **Color** — semantic palette (primary, success, warning, danger, neutral) with contrast-safe pairs; light/dark variants.
- **Typography** — type scale, weights, and line-heights tuned for readability and clinical clarity.
- **Spacing** — a consistent spacing scale for rhythm and alignment.
- **Radius & elevation** — consistent corner radii and shadow levels.
- **Motion** — duration and easing tokens for meaningful transitions.
- Tokens are the **single source** for visual values; components reference tokens, never raw values.

---

## 4. Component Library

Baseline component categories:

| Category | Examples |
| --- | --- |
| **Navigation** | App shell, top bar, side nav, tabs, breadcrumbs |
| **Actions** | Button, icon button, menu, dropdown, tooltip |
| **Inputs** | Text field, select, date/time, search, radio, checkbox, toggle, upload |
| **Display** | Tables, cards, lists, badges, avatars, typography |
| **Feedback** | Toast, inline alert, modal, dialog, skeleton, progress |
| **Patterns** | Form layout, filters, pagination, empty state, empty/error |

- Components are **typed and documented** (props, variants, accessibility notes).
- **Storybook / component explorer** is the living catalog.

---

## 5. Component Principles

- **Composable:** prefer composition over props explosion.
- **Accessible:** keyboard operable, focus-managed, ARIA-correct, screen-reader friendly.
- **Consistent state:** explicit loading/empty/error/disabled states per component.
- **Design-token driven:** components use tokens for all visual values.
- **Theme-aware:** respect light/dark and brand themes via tokens.
- **Tested:** components ship with tests ([15-TESTING-STANDARDS](15-TESTING-STANDARDS.md)).

---

## 6. Theming & Branding

- Branding is applied via **tokens**, not by forking components.
- Portal contexts (admin/clinical/patient) share the core system with context-appropriate variants.
- Light/dark themes are token-swappable; contrast requirements are validated.
- Third-party branding (multi-facility) can be supported via theme tokens without code change.

---

## 7. Accessibility in Components

- Every component **MUST** meet WCAG 2.1 AA ([12-UI-UX-GUIDELINES](12-UI-UX-GUIDELINES.md)).
- Accessible labels are part of component APIs (not optional add-ons).
- Focus order and keyboard shortcuts are designed, not incidental.
- Automated + manual accessibility checks are part of component release ([15-TESTING-STANDARDS](15-TESTING-STANDARDS.md)).

---

## 8. Consumption & Integration

- The design system is a **shared package** consumed by all web/mobile apps in the monorepo ([00-MASTER-ROADMAP](00-MASTER-ROADMAP.md), [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md)).
- Versioned and published internally; apps pin the version they use.
- No app may import raw tokens or bypass the component API for visual primitives.
- Mobile follows the same token/component conventions with platform-appropriate behavior.

---

## 9. Versioning & Governance

- **Semantic versioning** for the design system package.
- Changes are reviewed by design + engineering; accessibility regressions block release.
- Breaking changes follow a deprecation window; migration guides provided.
- The component catalog and tokens are kept in sync with implementation.

---

## 10. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| DS-1 | Component testing | Visual regression + unit vs unit only | Visual regression + unit |
| DS-2 | Mobile parity | Shared tokens, native behavior vs full RN components | Shared tokens + RN components |
| DS-3 | Open-source baseline | Adopt a library vs in-house | Evaluate library; augment in-house |

*Confirmed at the Phase 1 gate.*

---

## 11. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [03-TECHNOLOGY-STACK](03-TECHNOLOGY-STACK.md) | Frontend/mobile stack |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Frontend coding rules |
| [12-UI-UX-GUIDELINES](12-UI-UX-GUIDELINES.md) | UX principles & accessibility |
| [15-TESTING-STANDARDS](15-TESTING-STANDARDS.md) | Component testing |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Repo layout & phases |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Frontend | Created design system: principles, tokens, component library, component principles, theming, accessibility, consumption/integration, versioning/governance, and open decisions. |

---

*End of `13-DESIGN-SYSTEM.md`.*
