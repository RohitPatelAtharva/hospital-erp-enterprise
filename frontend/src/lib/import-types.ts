/**
 * Import Management contracts (Step 15).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `ImportController`, no `ImportService`, no `ImportRepository`, and NO
 *     `/imports` route exist in `backend/routes/api.php` (confirmed: route list
 *     has no import endpoint; no import controller/service/repository in the
 *     master-data directories; grep for "import" across routes/HTTP/Services
 *     returns only the Laravel default scaffold and database-only models).
 *   - There is NO import list/detail, upload, preview, validate, commit, or
 *     rollback endpoint of any kind. No file-upload behavior is exposed.
 *   - Import-shaped data exists ONLY as database tables:
 *       - `import_batch`      (actor_id IAM uuid, status string(20), batch_ref, occurred_at)
 *       - `import_staging_row`(import_batch_id, status string(20), row_num)
 *       - `import_validation` (1:1 import_staging_row)
 *     None of these is served over HTTP, so no import workflow can be driven from
 *     the API. `status` is a raw string column with no enumerated values exposed
 *     (no pending/validated/committed set is defined in code).
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The Import
 * Management UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend import API is introduced, these types should be promoted to
 * real `ApiEnvelope`-wrapped response/request shapes and the pages reimplemented
 * against them — without changing their field names.
 */

/** Mirrors `import_batch` (migration 2026_08_09_000005). Append-only. */
export interface ImportBatchRow {
  id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  actor_id: string | null;
  /** Bare string column. NO enumerated status values exist in the backend. */
  status: string | null;
  batch_ref: string;
  occurred_at: string;
  created_at: string;
  updated_at: string;
}

/** Mirrors `import_staging_row` (migration 2026_08_09_000006). Append-only. */
export interface ImportStagingRowRow {
  id: string;
  import_batch_id: string;
  /** Bare string column. NO enumerated status values exist in the backend. */
  status: string | null;
  row_num: number | null;
  created_at: string;
  updated_at: string;
}

/** Mirrors `import_validation` (migration 2026_08_09_000006). Append-only, 1:1. */
export interface ImportValidationRow {
  id: string;
  import_staging_row_id: string;
  created_at: string;
  updated_at: string;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type ImportAvailability = 'unavailable';
