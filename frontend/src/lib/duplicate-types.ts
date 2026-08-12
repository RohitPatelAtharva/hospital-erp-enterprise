/**
 * Duplicate Management contracts (Step 10).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `DuplicateController`, no `DuplicateService`, and NO `/duplicates`
 *     route exist in `backend/routes/api.php`.
 *   - The `duplicate_candidate`, `match_score`, and `duplicate_review` tables
 *     exist only at the schema/model/repository layer (migrations +
 *     `DuplicateCandidateRepository`). They are NOT exposed over HTTP.
 *   - There is no list, detail, review, confirm, dismiss, resolve, merge,
 *     unmerge, archive, or restore endpoint for duplicates.
 *   - The `status` column on `duplicate_candidate` is a bare `string` — there
 *     is NO enumeration of status values anywhere in the backend code, so no
 *     status values can be safely assumed.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The
 * Duplicate Management UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend duplicate API is introduced, the types below should be
 * promoted to real `ApiEnvelope`-wrapped response/request shapes and the page
 * reimplemented against them — without changing their field names.
 */

/** Mirrors `duplicate_candidate` (migration 2026_08_09_000005). */
export interface DuplicateCandidateRow {
  id: string;
  /** tenant scoping column (`base()` macro); not exposed via any API. */
  tenant_id?: string;
  /** master_record.id — the surviving/authoritative record. */
  master_record_id: string;
  /** master_record.id — the candidate being flagged as potential duplicate. */
  candidate_record_id: string;
  version: number;
  /** Bare string column. NO enumerated status values exist in the backend. */
  status: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

/**
 * Mirrors `match_score` (migration 2026_08_09_000006).
 * Append-only confidence score against a match rule; not exposed via any API.
 */
export interface MatchScoreRow {
  id: string;
  duplicate_candidate_id: string;
  match_rule_id: string;
  /** decimal(10,4), nullable. */
  value: number | null;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `duplicate_review` (migration 2026_08_09_000006).
 * Append-only steward review; not exposed via any API.
 */
export interface DuplicateReviewRow {
  id: string;
  duplicate_candidate_id: string;
  /** Cross-module IAM reference (uuid), nullable. */
  actor_id: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type DuplicateAvailability = 'unavailable';
