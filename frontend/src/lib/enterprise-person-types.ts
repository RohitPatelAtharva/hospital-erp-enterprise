import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Enterprise Person API contracts (Step 19).
 *
 * Field names mirror the backend `enterprise_person` model exactly (no invented
 * fields). Unlike patients/staff/organizations, the backend does NOT expose an
 * enumerated status set for enterprise persons, so `status` is intentionally a
 * plain `string` — we must not fabricate status enums.
 */

export interface EnterprisePerson {
  id: string;
  tenant_id: string;
  name: string | null;
  version: number;
  status: string;
  dob: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

export interface PaginationMeta {
  page: number;
  pageSize: number;
  total: number;
}

export type EnterprisePersonListResponse = ApiEnvelope<EnterprisePerson[], PaginationMeta>;
export type EnterprisePersonResponse = ApiEnvelope<EnterprisePerson>;

/** Fields accepted by POST /enterprise-persons (CreateEnterprisePersonRequest). */
export interface CreateEnterprisePersonPayload {
  name: string;
  dob?: string | null;
}
