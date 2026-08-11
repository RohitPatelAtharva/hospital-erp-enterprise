import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Staff API contracts. Field names mirror the backend Staff model exactly
 * (10-API §8); nothing is invented.
 */

export type StaffStatus = 'active' | 'inactive' | 'archived';

export interface Staff {
  id: string;
  tenant_id: string;
  master_record_id: string;
  enterprise_person_id: string;
  name: string | null;
  status: StaffStatus;
  version: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

export interface PaginationMeta {
  page: number;
  pageSize: number;
  total: number;
}

export type StaffListResponse = ApiEnvelope<Staff[], PaginationMeta>;
export type StaffResponse = ApiEnvelope<Staff>;

/** Fields accepted by POST /staff (CreateStaffRequest). */
export interface CreateStaffPayload {
  name: string;
  dob?: string | null;
  external_ref?: string | null;
}

/** Fields accepted by PATCH /staff/{id} (UpdateStaffRequest). */
export interface UpdateStaffPayload {
  name?: string;
  external_ref?: string | null;
}

export interface StaffIdentifier {
  id: string;
  staff_id: string;
  identity_type_id: string;
  value: string;
  status: string;
  created_at: string;
}

export interface StaffCredential {
  id: string;
  staff_id: string;
  credential_type_id: string;
  number: string | null;
  expiry: string | null;
  status: string;
  created_at: string;
}

export interface StaffConsent {
  id: string;
  staff_id: string;
  consent_type_id: string;
  status: string;
  created_at: string;
}

export interface StaffDemographic {
  id: string;
  staff_id: string;
  status: string;
  created_at: string;
}

export type StaffChildResource = 'identifiers' | 'credentials' | 'consents' | 'demographics';

export const STAFF_STATUSES: StaffStatus[] = ['active', 'inactive', 'archived'];
