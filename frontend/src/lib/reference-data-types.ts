import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Reference Data API contracts (10-API §11). Field names mirror the backend
 * ReferenceCategory and ReferenceValue models exactly; nothing is invented.
 */

export type ReferenceStatus = 'active' | 'inactive';

/** ReferenceCategory model response. */
export interface ReferenceCategory {
  id: string;
  tenant_id: string;
  code: string;
  status: ReferenceStatus;
  version: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

/** ReferenceValue model response. */
export interface ReferenceValue {
  id: string;
  tenant_id: string;
  reference_category_id: string;
  reference_version_id: string;
  code: string;
  status: ReferenceStatus;
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

export type ReferenceCategoryListResponse = ApiEnvelope<ReferenceCategory[], PaginationMeta>;
export type ReferenceCategoryResponse = ApiEnvelope<ReferenceCategory>;

export type ReferenceValueListResponse = ApiEnvelope<ReferenceValue[], PaginationMeta>;
export type ReferenceValueResponse = ApiEnvelope<ReferenceValue>;

/** Fields accepted by POST /reference-categories (CreateReferenceCategoryRequest). */
export interface CreateReferenceCategoryPayload {
  code: string;
}

/** Fields accepted by PATCH /reference-categories/{id} (UpdateReferenceCategoryRequest). */
export interface UpdateReferenceCategoryPayload {
  code?: string;
}

/** Fields accepted by POST /reference-values (CreateReferenceValueRequest). */
export interface CreateReferenceValuePayload {
  code: string;
  category_code: string;
  version_code?: string | null;
}

/** Fields accepted by PATCH /reference-values/{id} (UpdateReferenceValueRequest). */
export interface UpdateReferenceValuePayload {
  label?: string;
  value?: string;
}

export type ReferenceLifecycleAction = 'deactivate' | 'reactivate' | 'purge';
