import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Organization API contracts. Field names mirror the backend Organization model
 * exactly (10-API §10); nothing is invented.
 */

export type OrganizationStatus = 'active' | 'inactive' | 'archived';

export interface Organization {
  id: string;
  tenant_id: string;
  master_record_id: string;
  organization_type_id: string | null;
  name: string | null;
  status: OrganizationStatus;
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

export type OrganizationListResponse = ApiEnvelope<Organization[], PaginationMeta>;
export type OrganizationResponse = ApiEnvelope<Organization>;

/** Fields accepted by POST /organizations (CreateOrganizationRequest). */
export interface CreateOrganizationPayload {
  name: string;
  organization_type_code?: string | null;
  external_ref?: string | null;
}

/** Fields accepted by PATCH /organizations/{id} (UpdateOrganizationRequest). */
export interface UpdateOrganizationPayload {
  name?: string;
  external_ref?: string | null;
}

export interface OrganizationIdentifier {
  id: string;
  organization_id: string;
  identity_type_id: string;
  value: string;
  status: string;
  created_at: string;
}

export interface OrganizationContact {
  id: string;
  organization_id: string;
  contact_id: string;
  status: string;
  created_at: string;
}

export interface OrganizationRelationship {
  id: string;
  organization_id: string;
  related_org_id: string;
  relation_type_id: string;
  status: string;
  created_at: string;
}

export type OrganizationChildResource = 'identifiers' | 'contacts' | 'relationships';

export const ORGANIZATION_STATUSES: OrganizationStatus[] = ['active', 'inactive', 'archived'];
