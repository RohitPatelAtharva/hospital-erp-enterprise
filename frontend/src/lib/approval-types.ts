/**
 * Approvals contracts (Step 13).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `ApprovalController`, no approval service, and NO `/approvals` route
 *     exist in `backend/routes/api.php` (confirmed: route list has no approval,
 *     review, workflow, or steward endpoints; no approval controller/service in
 *     the master-data directories; grep for "approv"/"workflow" in HTTP+Services
 *     returns nothing).
 *   - There is NO approval list, detail, pending, approve, reject, cancel, or
 *     history endpoint of any kind.
 *   - Approval-shaped data exists ONLY as database tables:
 *       - `merge_approval`  (merge_event_id + approver_id IAM reference,
 *         append-only) — no controller/service/repository/route.
 *       - `duplicate_review` (duplicate_candidate_id + actor_id IAM reference,
 *         append-only) — no controller/service/repository/route.
 *     Neither is served over HTTP, so no approval workflow can be driven from the
 *     API. There is no `approval_status` column or workflow state anywhere.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The
 * Approvals UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend approval API is introduced, these types should be promoted to
 * real `ApiEnvelope`-wrapped response/request shapes and the pages reimplemented
 * against them — without changing their field names.
 */

/** Mirrors `merge_approval` (migration 2026_08_09_000006). Append-only, 1:1 to merge_event. */
export interface MergeApprovalRow {
  id: string;
  merge_event_id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  approver_id: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `duplicate_review` (migration 2026_08_09_000006). Append-only, 1:1 to
 * duplicate_candidate.
 */
export interface DuplicateReviewRow {
  id: string;
  duplicate_candidate_id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  actor_id: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type ApprovalAvailability = 'unavailable';
