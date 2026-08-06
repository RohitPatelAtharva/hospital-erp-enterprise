# Hospital ERP Enterprise — UI/UX Guidelines

> **Document ID:** `12-UI-UX-GUIDELINES.md`
> **Owner:** Product / UX Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Relationship:** Defines UX principles and interaction guidelines. Complements the visual/component standards in [13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md) and the accessibility requirements in [04-CODING-STANDARDS](04-CODING-STANDARDS.md).

---

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [UX Principles](#2-ux-principles)
3. [Personas & Journeys](#3-personas--journeys)
4. [Accessibility](#4-accessibility)
5. [Responsive & Multi-Device](#5-responsive--multi-device)
6. [Interaction & Feedback](#6-interaction--feedback)
7. [Content & Language](#7-content--language)
8. [Workflow Design](#8-workflow-design)
9. [Localization](#9-localization)
10. [Consistency](#10-consistency)
11. [Open Decisions](#11-open-decisions)
12. [Document Map & Dependencies](#12-document-map--dependencies)
13. [Appendix A — Change Log](#appendix-a--change-log)

---

## 1. Purpose & Scope

This document defines the **user experience (UX) principles and interaction guidelines** for the web portals and mobile apps. It ensures interfaces are usable, accessible, and consistent across the platform's many personas.

**Scope:** UX principles, journeys, accessibility, responsiveness, feedback, content, workflows, localization. Out of scope: visual tokens/components (see [13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md)) and implementation/coding (see [04-CODING-STANDARDS](04-CODING-STANDARDS.md)).

---

## 2. UX Principles

1. **Clinical safety first.** Critical actions are clear, confirmable, and error-resistant.
2. **Reduce cognitive load.** Show what is needed; progressive disclosure for complexity.
3. **Consistency.** Familiar patterns and components across all surfaces.
4. **Speed and clarity.** Fast, legible interfaces that communicate state clearly.
5. **Empower the user.** Self-service where valuable (patients) and efficiency where needed (staff).
6. **Accessible to all.** Usable by people with a wide range of abilities.

---

## 3. Personas & Journeys

- Design is persona-led (from [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) personas).
- Key journeys are mapped and tested: patient (book → visit → result → pay), clinical (order → result → review), front-desk (register → schedule), finance (charge → claim → collect).
- **Primary tasks** are one to three clicks/actions away; secondary tasks are discoverable.

---

## 4. Accessibility

- **MUST** meet **WCAG 2.1 AA** on web and equivalent mobile accessibility.
- Keyboard operable; visible focus; sufficient contrast; semantic markup.
- Screen-reader friendly; meaningful labels and status messages.
- **MUST NOT** rely on color alone to convey meaning (also use icons/text).
- Accessibility is tested as part of QA ([15-TESTING-STANDARDS](15-TESTING-STANDARDS.md)).

---

## 5. Responsive & Multi-Device

- Web portals are **responsive** and usable from small to large viewports.
- Touch targets are adequately sized for mobile.
- Mobile apps follow platform conventions while remaining consistent with the product (see [13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md)).
- Critical functionality is reachable on all supported surfaces.

---

## 6. Interaction & Feedback

- **Feedback on every action:** loading, success, and error states are explicit.
- **Confirmation** for destructive/irreversible or high-risk actions (release order, discharge, financial release).
- **Errors** are actionable: clear message, suggested resolution, and field-level detail.
- **Optimistic UI** where safe; always reconcile with server state.
- **Empty states** guide the user; **transitions** are meaningful, not decorative.

---

## 7. Content & Language

- **Plain language**; avoid clinical jargon in patient-facing surfaces.
- **Consistent terminology** across the platform (single content glossary).
- **Concise, action-oriented** labels and microcopy.
- Numbers, dates, and units are formatted consistently (localized) and never ambiguous in clinical contexts.

---

## 8. Workflow Design

- Support the **clinical and financial workflows** defined in the roadmap phases (5–7) with minimal steps.
- **Guard rails** prevent unsafe actions without being obstacles (e.g., validation before release).
- **Save and continue** where workflows are long; autosave where appropriate.
- **Multi-step wizards** for complex tasks; single-page for simple ones.
- Offline-capable where clinically justified, with clear sync state (see [06-AUTHENTICATION](06-AUTHENTICATION.md)).

---

## 9. Localization

- Design supports **internationalization** from the start: text, dates, numbers, time zones, and units are externalized.
- Layout accommodates language expansion (avoid hardcoded widths).
- Locale is derived from user/context, with per-facility overrides where needed.

---

## 10. Consistency

- All surfaces use the shared component library and tokens ([13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md)).
- Patterns (navigation, forms, tables, search, notifications) are reused; no bespoke UX per portal.
- A **UX review** is part of the Definition of Done for UI changes.

---

## 11. Open Decisions

| # | Decision | Options | Recommendation |
| --- | --- | --- | --- |
| UX-1 | Accessibility level | WCAG 2.1 AA vs AAA | AA |
| UX-2 | Localization scope (v1) | Single locale vs multi | Single locale, i18n-ready |
| UX-3 | Offline scope | Minimal vs clinical offline | Minimal; evaluate per workflow |

*Confirmed at the Phase 1 gate.*

---

## 12. Document Map & Dependencies

| Doc | Relationship |
| --- | --- |
| [04-CODING-STANDARDS](04-CODING-STANDARDS.md) | Accessibility & coding rules |
| [13-DESIGN-SYSTEM](13-DESIGN-SYSTEM.md) | Visual/components |
| [15-TESTING-STANDARDS](15-TESTING-STANDARDS.md) | Usability/accessibility testing |
| [00-MASTER-ROADMAP](00-MASTER-ROADMAP.md) | Personas & journeys |

---

## Appendix A — Change Log

| Date | Version | Author | Change |
| --- | --- | --- | --- |
| 2026-08-06 | 1.0.0 | Product | Created UI/UX guidelines: principles, personas/journeys, accessibility, responsiveness, feedback, content, workflows, localization, consistency, and open decisions. |

---

*End of `12-UI-UX-GUIDELINES.md`.*
