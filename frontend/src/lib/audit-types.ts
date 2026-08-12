/**
 * Audit Management contracts (Step 18).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `AuditController` / `AuditsController` / `AuditLogController` /
 *     `AuditEventController` / `ActivityLogController` / `ActivityController`
 *     exists in `backend/app/Http/Controllers/`; no Audit/Activity Service or
 *     Repository exists; and NO audit route exists in `backend/routes/api.php`
 *     (confirmed: a full backend grep for audit/activity/audit_log/audit_event/
 *     activity_log/change_log/access_log returns only internal classes and
 *     append-only child tables — never an audit HTTP endpoint).
 *   - The backend DOES have an internal audit *subsystem* (the `App\Audit\*`
 *     classes: AuditRecorder, AuditEvent name constants, AuditStore,
 *     AuditSanitizer) and audit-shaped DATABASE tables:
 *       - `audit_action`       (code string(50), unique (tenant_id, code))
 *       - `audit_actor`        (actor_key string(255), unique actor_key)
 *       - `audit_reference`    (event_id, audit_action_id, audit_actor_id,
 *                               entity, entity_id, occurred_at timestamp)
 *       - `audit_retention`    (category string(50), unique category)
 *       - `version_audit`, `golden_record_audit` (append-only child tables of
 *         those entities, also unexposed)
 *     None of these is served over HTTP. `App\Audit\AuditEvent` is a PHP
 *     constant class of event *names* (created, updated, merge.executed,
 *     export.run, ...) used only by the internal recorder — it is NOT an HTTP
 *     API contract, and the frontend must not present these names as if a list
 *     endpoint returns them.
 *   - `Support/Permissions.php` defines `audit:read` (AUDIT_READ), and it IS used
 *     by RoleRegistry / AuthorizationTest — but it gates NO HTTP route (no audit
 *     controller/route wires it). There is therefore no real audit HTTP
 *     authorization contract to consume. The nav item reuses the existing real
 *     `masterdata:read` permission used by sibling Master Data pages, per the
 *     project rule "do not invent audit:read for the UI" when no route honors it.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the migration
 * column shapes and the internal event vocabulary exactly so the contract is
 * recorded, but NO frontend component may call an endpoint or invent values
 * from them. The Audit Management UI intentionally shows an honest
 * "unavailable" state.
 *
 * If/when a backend audit API is introduced, these types should be promoted to
 * real `ApiEnvelope`-wrapped response/request shapes and the pages reimplemented
 * against them — without changing their field names.
 */

/** Mirrors `audit_action` (migration 2026_08_09_000001). Foundation lookup table. */
export interface AuditActionRow {
  id: string;
  tenant_id: string;
  code: string;
  status: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/** Mirrors `audit_actor` (migration 2026_08_09_000001). Append-only, no status. */
export interface AuditActorRow {
  id: string;
  tenant_id: string;
  actor_key: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/** Mirrors `audit_reference` (migration 2026_08_09_000002). Append-only. */
export interface AuditReferenceRow {
  id: string;
  tenant_id: string;
  event_id: string;
  audit_action_id: string;
  audit_actor_id: string;
  entity: string;
  entity_id: string | null;
  occurred_at: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/** Mirrors `audit_retention` (migration 2026_08_09_000001). Foundation lookup. */
export interface AuditRetentionRow {
  id: string;
  tenant_id: string;
  category: string;
  status: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/**
 * Internal event-name vocabulary defined by `App\Audit\AuditEvent` (Laravel
 * constant class). DOCUMENTED ONLY — these names are used by the internal audit
 * recorder and are NOT returned by any HTTP endpoint. The frontend must never
 * present them as if a list/detail API supplies them.
 */
export type AuditEventName =
  | 'created' | 'updated' | 'deactivated' | 'archived' | 'purged'
  | 'candidate_created' | 'reviewed' | 'threshold_changed'
  | 'merge.initiated' | 'merge.approved' | 'merge.rejected' | 'merge.executed'
  | 'unmerge.executed' | 'golden.established' | 'golden.updated'
  | 'golden.link_changed' | 'approval.decided' | 'approval.mfa'
  | 'import.applied' | 'import.rollback' | 'export.run'
  | 'integration.changed' | 'cross_reference.changed';

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type AuditAvailability = 'unavailable';
