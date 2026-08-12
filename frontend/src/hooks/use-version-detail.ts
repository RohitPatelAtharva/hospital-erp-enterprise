import { useCallback, useEffect, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type {
  Version,
  VersionDiff,
  VersionDiffResponse,
  VersionResponse,
} from '@/lib/version-types';

/**
 * Single version + its diff envelope.
 *
 * Backend surfaces:
 *   GET /master-records/{id}/versions/{vid}      -> Version
 *   GET /master-records/{id}/versions/{vid}/diff -> VersionDiff (envelope only)
 *
 * No overview/detail endpoint exists for the master record itself, so we only
 * fetch the version and its diff. Actor IDs are shown raw (never resolved to a
 * person name — there is no actor lookup endpoint).
 */

interface VersionDetailState {
  version: Version | null;
  diff: VersionDiff | null;
  loading: boolean;
  error: string | null;
}

const INITIAL: VersionDetailState = {
  version: null,
  diff: null,
  loading: true,
  error: null,
};

export function useVersionDetail(masterRecordId: string | undefined, versionId: string | undefined) {
  const [state, setState] = useState<VersionDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!masterRecordId || !versionId) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    let diffAborted = false;

    const versionReq = api.get<VersionResponse>(
      `/master-records/${masterRecordId}/versions/${versionId}`,
      { signal: controller.signal },
    );

    // The diff is best-effort: a failure must not blank the page.
    const diffReq = api
      .get<VersionDiffResponse>(`/master-records/${masterRecordId}/versions/${versionId}/diff`, {
        signal: controller.signal,
      })
      .catch((err) => {
        if (controller.signal.aborted || diffAborted) throw err;
        // Non-fatal: leave diff as null and continue.
        return null;
      });

    Promise.all([versionReq, diffReq])
      .then(([versionRes, diffRes]) => {
        setState({
          version: versionRes.data,
          diff: diffRes?.data ?? null,
          loading: false,
          error: null,
        });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load version.',
        }));
      });

    return () => {
      diffAborted = true;
      controller.abort();
    };
  }, [masterRecordId, versionId, refreshKey]);

  return { ...state, refresh };
}
