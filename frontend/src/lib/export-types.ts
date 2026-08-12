/**
 * Export Management contracts (Step 16).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `ExportController`, no `ExportService`, no `ExportRepository`, and NO
 *     `/exports` route exist in `backend/routes/api.php` (confirmed: route list
 *     has no export endpoint; no export controller/service/repository in the
 *     master-data directories; grep for "export" across routes/HTTP/Services
 *     returns only the Laravel default scaffold, the database-only models, and
 *     the `export:run` permission constant in `Support/Permissions.php`).
 *   - There is NO export list/detail, create-batch, queue, preview, enqueue,
 *     run, download, or cancel endpoint of any kind. No file-download or
 *     push-to-endpoint behavior is exposed.
 *   - Export-shaped data exists ONLY as database tables:
 *       - `export_batch`        (actor_id IAM uuid, status string(20), batch_ref)
 *       - `export_queue_item`   (export_batch_id, status string(20), item_ref)
 *       - `export_recipient`    (export_batch_id, integration_endpoint_id)
 *     None of these is served over HTTP, so no export workflow can be driven from
 *     the API. `status` is a raw string column with no enumerated values exposed
 *     (no pending/running/completed/failed set is defined in code). Recipient
 *     rows reference `integration_endpoint_id`, but the Integration module is also
 *     unexposed, so no endpoint lookup exists.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The Export
 * Management UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend export API is introduced, these types should be promoted to
 * real `ApiEnvelope`-wrapped response/request shapes and the pages reimplemented
 * against them — without changing their field names.
 */

/** Mirrors `export_batch` (migration 2026_08_09_000005). Append-only. */
export interface ExportBatchRow {
  id: string;
  /** Cross-module IAM reference (uuid), nullable — no lookup endpoint exists. */
  actor_id: string | null;
  /** Bare string column. NO enumerated status values exist in the backend. */
  status: string | null;
  batch_ref: string;
  created_at: string;
  updated_at: string;
}

/** Mirrors `export_queue_item` (migration 2026_08_09_000006). Append-only. */
export interface ExportQueueItemRow {
  id: string;
  export_batch_id: string;
  /** Bare string column. NO enumerated status values exist in the backend. */
  status: string | null;
  item_ref: string | null;
  created_at: string;
  updated_at: string;
}

/** Mirrors `export_recipient` (migration 2026_08_09_000006). Append-only. */
export interface ExportRecipientRow {
  id: string;
  export_batch_id: string;
  /** Integration module FK — also unexposed, no lookup endpoint exists. */
  integration_endpoint_id: string;
  created_at: string;
  updated_at: string;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type ExportAvailability = 'unavailable';
