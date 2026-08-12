/**
 * Merge / Unmerge contracts (Step 12).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `MergeController`, no `MergeService`, and NO `/merge` or `/merges`
 *     route exist in `backend/routes/api.php`.
 *   - There is no merge, unmerge, merge-preview, conflict-resolution, survivor-
 *     selection, or merge-history endpoint of any kind.
 *   - The `merge_event`, `merge_record`, `merge_approval`, and
 *     `survivorship_decision` tables exist only at the schema/model/repository
 *     layer (migrations + `MergeEventRepository`). NONE of it is exposed over
 *     HTTP, so no merge operations can be initiated or reversed from the API.
 *   - Merge tables carry no `status` column (e.g. `merge_event` only records an
 *     `occurred_at` timestamp), so no merge statuses can be assumed.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The Merge
 * Management UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend merge API is introduced, these types should be promoted to
 * real `ApiEnvelope`-wrapped response/request shapes and the pages reimplemented
 * against them — without changing their field names.
 */

/** Mirrors `merge_event` (migration 2026_08_09_000006). Append-only. */
export interface MergeEventRow {
  id: string;
  /** golden_record.id the merge was applied to. */
  golden_record_id: string;
  /** When the merge occurred. */
  occurred_at: string;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `merge_record` (migration 2026_08_09_000006). Append-only link
 * between a merge event and the master records it touched.
 */
export interface MergeRecordRow {
  id: string;
  merge_event_id: string;
  master_record_id: string;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `merge_approval` (migration 2026_08_09_000006). Append-only 1:1
 * approval record; `approver_id` is a cross-module IAM reference (uuid), nullable.
 */
export interface MergeApprovalRow {
  id: string;
  merge_event_id: string;
  approver_id: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `survivorship_decision` (migration 2026_08_09_000006). Append-only
 * link between a merge event and the survivorship rule it applied.
 */
export interface SurvivorshipDecisionRow {
  id: string;
  merge_event_id: string;
  survivorship_rule_id: string;
  created_at: string;
  updated_at: string;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type MergeAvailability = 'unavailable';
