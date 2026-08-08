# Master Data Module — Dashboards

> **Document ID:** `master-data/16-Dashboards`
> **Owner:** Analytics / Product Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate.
>
> **Relationship:** This document defines **dashboards** for the Master Data Management module — KPIs, widgets, and role-based visibility. It uses approved KPIs and reports ([15-Reports](15-Reports.md)) and data sources ([04-Database-Tables](04-Database-Tables.md)).

---

## Table of Contents

1. [Dashboard Overview](#1-dashboard-overview)
2. [Objectives](#2-objectives)
3. [Personas](#3-personas)
4. [Executive Dashboard](#4-executive-dashboard)
5. [Master Data Dashboard](#5-master-data-dashboard)
6. [Patient Dashboard](#6-patient-dashboard)
7. [Staff Dashboard](#7-staff-dashboard)
8. [Provider Dashboard](#8-provider-dashboard)
9. [Organization Dashboard](#9-organization-dashboard)
10. [Duplicate Dashboard](#10-duplicate-dashboard)
11. [Golden Record Dashboard](#11-golden-record-dashboard)
12. [Stewardship Dashboard](#12-stewardship-dashboard)
13. [Data Quality Dashboard](#13-data-quality-dashboard)
14. [Integration Dashboard](#14-integration-dashboard)
15. [KPI Widgets](#15-kpi-widgets)
16. [Charts](#16-charts)
17. [Filters](#17-filters)
18. [Drill-down](#18-drill-down)
19. [Alerts](#19-alerts)
20. [Real-Time Metrics](#20-real-time-metrics)
21. [Role-Based Visibility](#21-role-based-visibility)
22. [Performance](#22-performance)
23. [Accessibility](#23-accessibility)
24. [Mobile](#24-mobile)
25. [Cross References](#25-cross-references)

---

## 1. Dashboard Overview

Dashboards give stakeholders an at-a-glance view of master-data health, duplicate resolution, golden coverage, quality, and integration. They are built on the platform analytics surface and render data from approved reports/projections.

---

## 2. Objectives

| Objective | Detail |
| --- | --- |
| Monitor | Registry and quality health |
| Detect | Duplicates, quality issues |
| Demonstrate | Compliance and KPI attainment |
| Decide | Support prioritization |

---

## 3. Personas

| Persona | Dashboard |
| --- | --- |
| Executive | Executive, data quality |
| Registry Admin | Duplicate, golden, master data |
| Data Steward | Stewardship, quality |
| Integration Owner | Integration |

---

## 4. Executive Dashboard

| Widget | Metric |
| --- | --- |
| Registry size | Total records by type |
| Duplicate rate | % duplicate candidates |
| Golden coverage | % with golden record |
| Quality index | Overall data quality |
| Compliance | Audit/retention indicators |

---

## 5. Master Data Dashboard

| Widget | Metric |
| --- | --- |
| Registry counts | Patients, staff, providers, orgs |
| Status mix | Active vs inactive |
| Version activity | Recent changes |
| Recent activity | Latest events |

---

## 6. Patient Dashboard

| Widget | Metric |
| --- | --- |
| Patient count | Active patients |
| Duplicate rate | Candidates / reviewed |
| Consent coverage | % with consent |
| Demographic quality | Missing fields |

---

## 7. Staff Dashboard

| Widget | Metric |
| --- | --- |
| Staff count | Active staff |
| Credential status | Valid / expiring / expired |
| Duplicate rate | Staff candidates |

---

## 8. Provider Dashboard

| Widget | Metric |
| --- | --- |
| Provider count | Active providers |
| Network membership | Providers per network |
| Credential status | Valid / expiring |

---

## 9. Organization Dashboard

| Widget | Metric |
| --- | --- |
| Org count | Active organizations |
| Relationship map | Parent/subsidiary |
| Contact coverage | % with contacts |

---

## 10. Duplicate Dashboard

| Widget | Metric |
| --- | --- |
| Open candidates | By severity |
| Aging | Oldest unresolved |
| Resolution rate | Resolved vs open |
| Rule performance | Scores by rule |

---

## 11. Golden Record Dashboard

| Widget | Metric |
| --- | --- |
| Golden coverage | % with golden |
| Golden quality | Source completeness |
| Link activity | Adds/removals |

---

## 12. Stewardship Dashboard

| Widget | Metric |
| --- | --- |
| Open issues | By severity |
| Remediation | Tasks open/closed |
| Steward workload | Assignments |

---

## 13. Data Quality Dashboard

| Widget | Metric |
| --- | --- |
| Completeness | Field-level completeness |
| Validity | Invalid values |
| Uniqueness | Duplicate indicators |
| Timeliness | Recency of updates |

---

## 14. Integration Dashboard

| Widget | Metric |
| --- | --- |
| Endpoint status | Up/down/error |
| Sync health | Last sync + failures |
| Mapping coverage | Mapped fields |

---

## 15. KPI Widgets

| KPI | Source |
| --- | --- |
| Duplicate rate | [15-Reports](15-Reports.md) §10 |
| Golden coverage | §11 |
| Quality index | §13 |
| Approval SLA | [14-Notifications](14-Notifications.md) §19 |
| Integration uptime | [15-Reports](15-Reports.md) §18 |

---

## 16. Charts

| Chart | Use |
| --- | --- |
| Line | Trends over time |
| Bar | Category comparisons |
| Donut | Status distribution |
| Table | Detail with drill-down |
| Heatmap | Quality by field/entity |

---

## 17. Filters

| Filter | Applies |
| --- | --- |
| Tenant | Always |
| Date range | Time-series |
| Status | Entity status |
| Severity | Duplicates/issues |
| Entity type | Registries |

---

## 18. Drill-down

| Level | Action |
| --- | --- |
| Widget → Report | Click to report detail ([15-Reports](15-Reports.md)) |
| Report → Record | Click to registry record ([08-UI](08-UI.md)) |
| Record → Audit | View history |

---

## 19. Alerts

| Alert | Trigger |
| --- | --- |
| Duplicate spike | Rate above threshold |
| Quality drop | Index below threshold |
| Integration failure | Endpoint down |
| Approval SLA risk | Pending aging |

> Alerts route via [14-Notifications](14-Notifications.md).

---

## 20. Real-Time Metrics

| Metric | Real-time |
| --- | --- |
| Integration health | Yes |
| Import progress | Yes |
| Pending approvals | Near-real-time |
| Steady-state KPIs | Refreshed on schedule |

---

## 21. Role-Based Visibility

| Role | Dashboards |
| --- | --- |
| Executive | Executive, quality (read) |
| Registry Admin | Master data, duplicate, golden |
| Data Steward | Stewardship, quality |
| Integration Owner | Integration |
| Auditor | Audit indicators |

---

## 22. Performance

Dashboard performance follows [19-Performance](19-Performance.md) §20 and [14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md): p95 sub-second for interactive; pre-aggregated for large.

---

## 23. Accessibility

Follow [12-UI-UX-GUIDELINES](../../12-UI-UX-GUIDELINES.md) §14 — WCAG 2.1 AA, color-blind-safe charts, keyboard-navigable.

---

## 24. Mobile

Dashboards are responsive ([08-UI](08-UI.md) §26); mobile shows key KPIs with drill-down.

---

## 25. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [15-Reports](15-Reports.md) | Reports | Consumes |
| [08-UI](08-UI.md) | UI | Consumes |
| [11-Permissions](11-Permissions.md) | Access | Consumes |
| [19-Performance](19-Performance.md) | Performance | Consumes |
| [14-Notifications](14-Notifications.md) | Alerts | Consumes |
| [12-UI-UX-GUIDELINES](../../12-UI-UX-GUIDELINES.md) | Accessibility | Consumes |

---

*End of `docs/modules/master-data/16-Dashboards.md`.*
