/**
 * Integrations Management contracts (Step 17).
 *
 * CRITICAL BACKEND REALITY (verified, do not assume):
 *   - No `IntegrationController`, `IntegrationEndpointController`,
 *     `IntegrationMapController`, `WebhookController`, `SyncController`, or
 *     `ConnectorController` exists in `backend/app/Http/Controllers/`; no
 *     Integration/Webhook/Sync/Connector Service or Repository exists; and NO
 *     integration route exists in `backend/routes/api.php` (confirmed: a full
 *     backend grep for integration/webhook/connector/sync/oauth/credential/
 *     secret/mapping returns only the unrelated Staff/Provider *credential*
 *     endpoints, the model files, and the audit sanitizer — never an
 *     integration HTTP endpoint). Routed Staff/Provider `credentials` endpoints
 *     are NOT integration endpoints.
 *   - There is NO integration list/detail, endpoint list/detail, mapping editor,
 *     webhook config, sync trigger/history, test-connection, retry, enable/
 *     disable, or delete API of any kind. `integration:manage` is defined only
 *     as a permission constant in `Support/Permissions.php` and gates NOTHING
 *     (no route references it), so no integration authorization contract exists.
 *   - Integration-shaped data exists ONLY as database tables:
 *       - `integration_endpoint` (code string(50), unique (tenant_id, code))
 *       - `integration_map`      (integration_endpoint_id, resource_type)
 *       - `mapping_field`        (integration_map_id, source_field)
 *     None is served over HTTP. `integration_endpoint` carries no secret/
 *     credential column in the schema, so there is nothing to mask — but the
 *     rule still holds: no fake credential/secret UI is invented.
 *
 * Therefore these types are SCHEMA-DOCUMENTATION ONLY. They mirror the
 * migration/model column shapes exactly so the contract is recorded, but NO
 * frontend component may call an endpoint or invent values from them. The
 * Integrations Management UI intentionally shows an honest "unavailable" state.
 *
 * If/when a backend integration API is introduced, these types should be
 * promoted to real `ApiEnvelope`-wrapped response/request shapes and the pages
 * reimplemented against them — without changing their field names.
 */

/** Mirrors `integration_endpoint` (migration 2026_08_09_000001). Foundation table. */
export interface IntegrationEndpointRow {
  id: string;
  tenant_id: string;
  /** Unique per tenant. No name/URL/secret/credential columns in schema. */
  code: string;
  status: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/** Mirrors `integration_map` (migration 2026_08_09_000005). */
export interface IntegrationMapRow {
  id: string;
  tenant_id: string;
  integration_endpoint_id: string;
  /** e.g. resource type string; no enumerated set exposed. */
  resource_type: string | null;
  status: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/** Mirrors `mapping_field` (migration 2026_08_09_000006). */
export interface MappingFieldRow {
  id: string;
  tenant_id: string;
  integration_map_id: string;
  source_field: string | null;
  status: string;
  created_at: string;
  updated_at: string;
  created_by: string | null;
  updated_by: string | null;
}

/**
 * UI discriminator for the unavailable state. This is the ONE frontend-only
 * field, used purely to drive messaging — it is never sent to or read from the
 * backend.
 */
export type IntegrationAvailability = 'unavailable';
