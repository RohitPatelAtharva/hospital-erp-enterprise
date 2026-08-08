# Master Data Module — Reports

> **Document ID:** `master-data/15-Reports`
> **Owner:** Analytics / Engineering Lead
> **Status:** 🔄 Pending approval (Phase 1 gate)
> **Version:** 1.0.0
> **Last updated:** 2026-08-06
> **Review cadence:** Re-reviewed at every phase gate.
>
> **Relationship:** This document defines **reports** for the Master Data Management module — categories, stakeholders, and named reports. It uses approved data sources ([04-Database-Tables](04-Database-Tables.md)) and reflects the KPIs in [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) and [20-COMPLIANCE](../../20-COMPLIANCE.md).

---

## Table of Contents

1. [Reporting Overview](#1-reporting-overview)
2. [Reporting Objectives](#2-reporting-objectives)
3. [Stakeholders](#3-stakeholders)
4. [Report Categories](#4-report-categories)
5. [Master Data Reports](#5-master-data-reports)
6. [Patient Reports](#6-patient-reports)
7. [Staff Reports](#7-staff-reports)
8. [Provider Reports](#8-provider-reports)
9. [Organization Reports](#9-organization-reports)
10. [Duplicate Reports](#10-duplicate-reports)
11. [Golden Record Reports](#11-golden-record-reports)
12. [Merge Reports](#12-merge-reports)
13. [Stewardship Reports](#13-stewardship-reports)
14. [Approval Reports](#14-approval-reports)
15. [Audit Reports](#15-audit-reports)
16. [Reference Data Reports](#16-reference-data-reports)
17. [Import/Export Reports](#17-importexport-reports)
18. [Integration Reports](#18-integration-reports)
19. [KPI Reports](#19-kpi-reports)
20. [Filters](#20-filters)
21. [Security](#21-security)
22. [Scheduling](#22-scheduling)
23. [Export Formats](#23-export-formats)
24. [Retention](#24-retention)
25. [Performance](#25-performance)
26. [Cross References](#26-cross-references)

---

## 1. Reporting Overview

Master Data reporting gives stakeholders visibility into registry health, duplicate resolution, golden-record quality, stewardship, and compliance. Reports read from approved data sources and, where appropriate, projections ([06-ERD](06-ERD.md) §24).

---

## 2. Reporting Objectives

| Objective | Detail |
| --- | --- |
| Quality | Measure data quality and duplicates |
| Compliance | Evidence for audits |
| Operational | Registry and stewardship health |
| KPI | Track module KPIs ([16-Dashboards](16-Dashboards.md)) |

---

## 3. Stakeholders

| Stakeholder | Reports |
| --- | --- |
| Executive | KPI, quality dashboards |
| Registry Admin | Duplicate, golden, merge |
| Data Steward | Stewardship, quality, reference |
| Approver | Approval history |
| Auditor | Audit, compliance |
| Finance/Ops | Organization, reference |
| Integration Owner | Integration health |

---

## 4. Report Categories

| Category | Reports |
| --- | --- |
| Registry | Patient, staff, provider, organization |
| Quality | Duplicate, golden, stewardship |
| Governance | Approval, audit, reference |
| Data exchange | Import/export, integration |
| KPI | Program + module KPIs |

---

## 5. Master Data Reports

| Report | Contents |
| --- | --- |
| Registry Summary | Counts by entity type + status |
| Record Completeness | Field-level completeness |
| Active vs Inactive | Status distribution |
| Version Activity | Version churn |

---

## 6. Patient Reports

| Report | Contents |
| --- | --- |
| Patient Registry | Patients + identifiers + status |
| Duplicate Rate | Duplicate candidates by source |
| Consent Coverage | Consent completeness |
| Demographic Quality | Missing/invalid demographic fields |

---

## 7. Staff Reports

| Report | Contents |
| --- | --- |
| Staff Registry | Staff + credentials + status |
| Credential Expiry | Upcoming expirations |
| Staff Duplicates | Duplicate candidates |

---

## 8. Provider Reports

| Report | Contents |
| --- | --- |
| Provider Registry | Providers + credentials + networks |
| Network Membership | Providers per network |
| Credential Status | Active/expired |

---

## 9. Organization Reports

| Report | Contents |
| --- | --- |
| Organization Registry | Orgs + types + status |
| Organization Relationships | Parent/subsidiary view |
| Contact Completeness | Contact coverage |

---

## 10. Duplicate Reports

| Report | Contents |
| --- | --- |
| Duplicate Queue | Open candidates by severity |
| Duplicate Resolution | Resolved vs open, aging |
| Rule Performance | Match scores by rule |
| False Positive Rate | Review outcomes |

---

## 11. Golden Record Reports

| Report | Contents |
| --- | --- |
| Golden Coverage | % of records with golden |
| Golden Quality | Source completeness |
| Link Activity | Link add/remove |

---

## 12. Merge Reports

| Report | Contents |
| --- | --- |
| Merge History | Events + records + approval |
| Merge Outcomes | Approved/rejected/executed |
| Unmerge Activity | Reversals |

---

## 13. Stewardship Reports

| Report | Contents |
| --- | --- |
| Open Issues | Quality issues by severity/aging |
| Remediation Status | Tasks open/closed |
| Steward Workload | Assignments + completion |

---

## 14. Approval Reports

| Report | Contents |
| --- | --- |
| Approval History | Decisions + actors + times |
| Approval SLA | Response-time compliance |
| Pending Approvals | Aging queue |

---

## 15. Audit Reports

| Report | Contents |
| --- | --- |
| Change Log | All audit events by resource |
| Access Review | Audit access activity |
| Retention Compliance | Archived vs online |

---

## 16. Reference Data Reports

| Report | Contents |
| --- | --- |
| Reference Inventory | Categories + values + status |
| Reference Usage | Where values are used |
| Lookup Coverage | Lookup completeness |

---

## 17. Import/Export Reports

| Report | Contents |
| --- | --- |
| Import Batches | Runs + row counts + errors |
| Validation Summary | Pass/fail per import |
| Export History | Runs + recipients + status |

---

## 18. Integration Reports

| Report | Contents |
| --- | --- |
| Integration Health | Endpoint status + errors |
| Mapping Coverage | Mapped vs unmapped fields |
| Cross-Reference Sync | Resolution status |

---

## 19. KPI Reports

| Report | Contents |
| --- | --- |
| Module KPI | Duplicate rate, golden coverage, quality |
| Program KPI | Aligns to [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) §2.3 |

---

## 20. Filters

| Filter | Applies |
| --- | --- |
| Tenant | Always enforced |
| Status | Entity status |
| Date range | Activity/audit/import |
| Severity | Duplicates/issues |
| Entity type | Registries |

---

## 21. Security

| Aspect | Decision |
| --- | --- |
| Access | Report access per role ([11-Permissions](11-Permissions.md)) |
| PHI | Reports may include PHI only for authorized roles |
| Audit | Report generation audited |
| Export | Report export governed |

---

## 22. Scheduling

| Aspect | Decision |
| --- | --- |
| On-demand | Interactive |
| Scheduled | Recurring digest |
| Delivery | Via [14-Notifications](14-Notifications.md) |
| Cadence | Daily/weekly/monthly per report |

---

## 23. Export Formats

| Format | Use |
| --- | --- |
| PDF | Formal reports |
| CSV | Data export |
| XLSX | Analysis |
| JSON | Machine consumption |

---

## 24. Retention

Report outputs follow [17-DATA-GOVERNANCE](../../17-DATA-GOVERNANCE.md) §12 retention; PHI-containing reports are governed.

---

## 25. Performance

Report performance follows [14-PERFORMANCE-STANDARDS](../../14-PERFORMANCE-STANDARDS.md) and [19-Performance](19-Performance.md) §21 — pre-aggregated where needed; p95 < 2 s for interactive reports ([19-Performance](19-Performance.md) §5).

---

## 26. Cross References

| Reference | Relationship | Direction |
| --- | --- | --- |
| [04-Database-Tables](04-Database-Tables.md) | Data sources | Consumes |
| [16-Dashboards](16-Dashboards.md) | Dashboards | Consumes |
| [13-Audit](13-Audit.md) | Audit reports | Consumes |
| [11-Permissions](11-Permissions.md) | Access | Consumes |
| [19-Performance](19-Performance.md) | Performance | Consumes |
| [20-COMPLIANCE](../../20-COMPLIANCE.md) | Compliance | Consumes |
| [00-MASTER-ROADMAP](../../00-MASTER-ROADMAP.md) | KPIs | Consumes |

---

*End of `docs/modules/master-data/15-Reports.md`.*
