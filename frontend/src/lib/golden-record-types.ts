/**
 * Golden Record contracts (Step 11).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `GoldenRecordController`, no `GoldenRecordService`, and NO
 *     `/golden-records` route exist in `backend/routes/api.php`.
 *   - The `golden_record` table (plus `golden_record_link`,
 *     `golden_record_source`, `golden_record_audit`, `merge_event`,
 *     `merge_approval`) exists only at the schema/model/repository layer
 *     (migrations + `GoldenRecordRepository`). None of it is exposed over HTTP.
 *   - There is no list, detail, create, update, activate/deactivate/archive/
 *     restore/purge, merge/unmerge, or history endpoint for golden records.
 *   - The `status` column on `golden_record` is a bare `string` — there is NO
 *     enumeration of status values anywhere in the backend code, so no status
 *     values can be safely assumed.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The
 * Golden Records UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend golden-record API is introduced, these types should be
 * promoted to real `ApiEnvelope`-wrapped response/request shapes and the pages
 * reimplemented against them — without changing their field names.
 */

/** Mirrors `golden_record` (migration 2026_08_09_000003). */
export interface GoldenRecordRow {
  id: string;
  /** tenant scoping column (`base()` macro); not exposed via any API. */
  tenant_id?: string;
  /** master_record.id — the canonical/surviving record for this golden record. */
  master_record_id: string;
  version: number;
  /** Bare string column. NO enumerated status values exist in the backend. */
  status: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

/**
 * Mirrors `golden_record_link` (migration 2026_08_09_000005).
 * Source master records linked to a golden record; not exposed via any API.
 */
export interface GoldenRecordLinkRow {
  id: string;
  golden_record_id: string;
  master_record_id: string;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `golden_record_source` (migration 2026_08_09_000006).
 * Integration source systems for a golden record link; not exposed via any API.
 */
export interface GoldenRecordSourceRow {
  id: string;
  golden_record_link_id: string;
  /** Cross-module integration reference (uuid), nullable. */
  source_system_id: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Mirrors `golden_record_audit` (migration 2026_08_09_000005).
 * Append-only audit trail; not exposed via any API.
 */
export interface GoldenRecordAuditRow {
  id: string;
  golden_record_id: string;
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
export type GoldenRecordAvailability = 'unavailable';
