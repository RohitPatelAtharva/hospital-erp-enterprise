import type { ApiEnvelope } from '@/lib/api-client';

/**
 * Version API contracts (10-API §18). Field names mirror the backend Version
 * model and VersionService::diff exactly; nothing is invented.
 *
 * The backend exposes ONLY version history for a master record — there is no
 * `GET /master-records` list and no `GET /master-records/{id}` overview
 * endpoint. Likewise the diff returns a change *envelope* (version numbers,
 * actor IDs, timestamps, delta type) and never field-level payloads, because
 * the `version` table stores metadata only (no snapshot column).
 */

export interface PaginationMeta {
  page: number;
  pageSize: number;
  total: number;
}

/** A single append-only version record of a master record. */
export interface Version {
  id: string;
  tenant_id: string;
  master_record_id: string;
  actor_id: string | null;
  version_number: number;
  created_at: string;
  updated_at: string;
  /** Present only in the diff envelope; DB column may be nullable/absent. */
  occurred_at?: string | null;
}

export type VersionListResponse = ApiEnvelope<Version[], PaginationMeta>;
export type VersionResponse = ApiEnvelope<Version>;

/** A reference to a version as returned inside the diff envelope. */
export interface VersionRef {
  id: string;
  actor_id: string | null;
  version_number: number;
  occurred_at: string | null;
}

/** The "current" side of a diff also carries the master record scope. */
export interface VersionDiffCurrent extends VersionRef {
  master_record_id: string;
}

/** Change envelope returned by GET /master-records/{id}/versions/{vid}/diff. */
export interface VersionDiff {
  current: VersionDiffCurrent;
  previous: VersionRef | null;
  delta:
    | { type: 'initial' }
    | { type: 'revision'; from: number; to: number };
}

export type VersionDiffResponse = ApiEnvelope<VersionDiff>;
