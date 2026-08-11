import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Provider API contracts. Field names mirror the backend Provider model exactly
 * (10-API §9); nothing is invented.
 */

export type ProviderStatus = 'active' | 'inactive' | 'archived';

export interface Provider {
  id: string;
  tenant_id: string;
  master_record_id: string;
  name: string | null;
  type: string | null;
  status: ProviderStatus;
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

export type ProviderListResponse = ApiEnvelope<Provider[], PaginationMeta>;
export type ProviderResponse = ApiEnvelope<Provider>;

/** Fields accepted by POST /providers (CreateProviderRequest). */
export interface CreateProviderPayload {
  name: string;
  type?: string | null;
  external_ref?: string | null;
}

/** Fields accepted by PATCH /providers/{id} (UpdateProviderRequest). */
export interface UpdateProviderPayload {
  name?: string;
  type?: string | null;
  external_ref?: string | null;
}

export interface ProviderIdentifier {
  id: string;
  provider_id: string;
  identity_type_id: string;
  value: string;
  status: string;
  created_at: string;
}

export interface ProviderCredential {
  id: string;
  provider_id: string;
  credential_type_id: string;
  number: string | null;
  status: string;
  created_at: string;
}

export interface ProviderNetwork {
  id: string;
  provider_id: string;
  network_id: string;
  status: string;
  created_at: string;
}

export type ProviderChildResource = 'identifiers' | 'credentials' | 'networks';

export const PROVIDER_STATUSES: ProviderStatus[] = ['active', 'inactive', 'archived'];
