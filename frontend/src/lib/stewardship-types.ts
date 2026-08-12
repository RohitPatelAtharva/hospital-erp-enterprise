/**
 * Stewardship contracts (Step 14).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `StewardshipController`, no stewardship service, and NO `/stewardship`
 *     route exist in `backend/routes/api.php` (confirmed: route list has no
 *     steward/quality-issue/remediation/workflow endpoint; no stewardship
 *     controller/service/repository in the master-data directories).
 *   - There is NO steward list/detail, assignment/reassignment, queue, task
 *     list/detail, claim/unclaim, resolve/reopen/escalate/close, or data-quality
 *     issue list/detail/creation endpoint of any kind.
 *   - Stewardship-shaped data exists ONLY as database tables:
 *       - `steward_assignment` (master_domain_id, staff_id)
 *       - `quality_issue`       (master_record_id, reported_by IAM uuid,
 *                                severity string(20), nullable)
 *       - `remediation_task`    (quality_issue_id, assignee_id IAM uuid)
 *       - `stewardship_log`     (quality_issue_id, actor_id IAM uuid, occurred_at)
 *     None of these is served over HTTP, so no stewardship workflow can be driven
 *     from the API. `severity` is a raw string column with no enumerated values
 *     exposed. Actor/staff IDs are cross-module IAM references with no lookup.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The
 * Stewardship UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend stewardship API is introduced, these types should be promoted
 * to real `ApiEnvelope`-wrapped response/request shapes and the pages
 * reimplemented against them — without changing their field names.
 */

/** Mirrors `steward_assignment` (migration 2026_08_09_000005). */
export interface StewardAssignmentRow {
  id: string;
  master_domain_id: string;
  staff_id: string;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `quality_issue` (migration 2026_08_09_000005). Bare `severity` string
 * (no backend enum) — not to be treated as a fixed priority set.
 */
export interface QualityIssueRow {
  id: string;
  master_record_id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  reported_by: string | null;
  /** Bare string column. NO enumerated severity values exist in the backend. */
  severity: string | null;
  created_at: string;
  updated_at: string;
}

/** Mirrors `remediation_task` (migration 2026_08_09_000006). */
export interface RemediationTaskRow {
  id: string;
  quality_issue_id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  assignee_id: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `stewardship_log` (migration 2026_08_09_000006). Append-only audit log.
 */
export interface StewardshipLogRow {
  id: string;
  quality_issue_id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  actor_id: string | null;
  occurred_at: string;
  created_at: string;
  updated_at: string;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type StewardshipAvailability = 'unavailable';
