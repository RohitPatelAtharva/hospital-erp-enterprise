import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Master Data Search contracts (Step8).
 *
 * Field shapes are lifted from the backend SearchController
 * (api/v1/master-data/search) and the underlying models — nothing is invented.
 * The backend returns five independent paginated endpoints; there is no unified
 * `/search/all`, so the "All" scope is orchestrated client-side via
 * Promise.allSettled across the five real endpoints.
 *
 * The `kind` discriminator is the only frontend-added field: each endpoint
 * returns its raw model rows, and the hook stamps `kind` so results can be
 * narrowed with a discriminated union.
 */

/** Scopes selectable in the UI. `all` fans out to the five concrete scopes. */
export type SearchScope = 'all' | 'patients' | 'staff' | 'providers' | 'organizations' | 'master';

/** Pagination envelope metadata returned by every search endpoint (paginate 25). */
export interface SearchMeta {
  page: number;
  pageSize: number;
  total: number;
}

interface BaseSearchResult {
  id: string;
  status: string;
  version: number;
  created_at: string;
  updated_at: string;
}

export interface PatientSearchResult extends BaseSearchResult {
  kind: 'patient';
  name: string | null;
  dob: string | null;
  sex: string | null;
}

export interface StaffSearchResult extends BaseSearchResult {
  kind: 'staff';
  name: string | null;
}

export interface ProviderSearchResult extends BaseSearchResult {
  kind: 'provider';
  name: string | null;
  type: string | null;
}

export interface OrganizationSearchResult extends BaseSearchResult {
  kind: 'organization';
  name: string | null;
  organization_type_id: string | null;
}

export interface MasterRecordSearchResult extends BaseSearchResult {
  kind: 'master';
  entity_type_id: string;
  external_ref: string | null;
}

export type SearchResult =
  | PatientSearchResult
  | StaffSearchResult
  | ProviderSearchResult
  | OrganizationSearchResult
  | MasterRecordSearchResult;

/** Raw envelope shape returned by a single concrete search endpoint. */
export type SearchListResponse = ApiEnvelope<SearchResult[], SearchMeta>;

/** Per-source outcome used to render the "All" scope partial-result UI. */
export interface SearchSourceState {
  scope: SearchScope;
  label: string;
  status: 'idle' | 'loading' | 'success' | 'empty' | 'error' | 'unavailable';
  count: number;
  error?: string;
}

/** Narrowing helpers for the discriminated union. */
export const isPatient = (r: SearchResult): r is PatientSearchResult => r.kind === 'patient';
export const isStaff = (r: SearchResult): r is StaffSearchResult => r.kind === 'staff';
export const isProvider = (r: SearchResult): r is ProviderSearchResult => r.kind === 'provider';
export const isOrganization = (r: SearchResult): r is OrganizationSearchResult => r.kind === 'organization';
export const isMasterRecord = (r: SearchResult): r is MasterRecordSearchResult => r.kind === 'master';
