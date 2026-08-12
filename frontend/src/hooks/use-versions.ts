import { useCallback, useEffect, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type { Version, VersionListResponse } from '@/lib/version-types';

/**
 * Version history for a master record.
 *
 * Backend surface: GET /master-records/{id}/versions (paginated, 25/page).
 * There is no master-record overview endpoint, so this hook only fetches the
 * real version history. A 404 (unknown master record id) is surfaced as an
 * honest error rather than fabricated.
 */

interface VersionsState {
  versions: Version[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
}

const INITIAL: VersionsState = {
  versions: [],
  loading: true,
  error: null,
  page: 1,
  pageSize: 25,
  total: 0,
};

export function useVersions(masterRecordId: string | undefined) {
  const [state, setState] = useState<VersionsState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);
  const pageRef = useRef(1);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  const setPage = useCallback((page: number) => {
    pageRef.current = page;
    setState((prev) => ({ ...prev, page }));
  }, []);

  useEffect(() => {
    if (!masterRecordId) return;

    const controller = new AbortController();
    const page = pageRef.current;

    setState((prev) => ({ ...prev, loading: true, error: null }));

    api
      .get<VersionListResponse>(`/master-records/${masterRecordId}/versions`, {
        params: { page },
        signal: controller.signal,
      })
      .then((res) => {
        setState({
          versions: res.data,
          loading: false,
          error: null,
          page: res.meta?.page ?? page,
          pageSize: res.meta?.pageSize ?? 25,
          total: res.meta?.total ?? 0,
        });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load version history.',
        }));
      });

    return () => controller.abort();
  }, [masterRecordId, refreshKey]);

  return { ...state, refresh, setPage };
}
