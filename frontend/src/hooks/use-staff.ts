import { useCallback, useEffect, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type { Staff, StaffListResponse, StaffStatus } from '@/lib/staff-types';

/**
 * Staff list data hook.
 *
 * The backend exposes two read surfaces for the staff registry:
 *   - GET /staff            (paginated; optional ?status= filter)
 *   - GET /search/staff?q=  (name search; paginated)
 *
 * `status` and `query` are controlled from the page. The hook selects the
 * appropriate endpoint and page, and exposes loading/error state + refresh.
 */

interface StaffListState {
  staff: Staff[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
}

const INITIAL: StaffListState = {
  staff: [],
  loading: true,
  error: null,
  page: 1,
  pageSize: 25,
  total: 0,
};

export function useStaff({ status, query }: { status?: StaffStatus | ''; query?: string }) {
  const [state, setState] = useState<StaffListState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);
  const pageRef = useRef(1);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  const setPage = useCallback((page: number) => {
    pageRef.current = page;
    setState((prev) => ({ ...prev, page }));
  }, []);

  useEffect(() => {
    const controller = new AbortController();
    const page = pageRef.current;

    setState((prev) => ({ ...prev, loading: true, error: null }));

    const isSearch = typeof query === 'string' && query.trim().length > 0;
    const params: Record<string, string | number> = { page };
    if (!isSearch && status) params.status = status;

    const path = isSearch ? '/search/staff' : '/staff';

    api
      .get<StaffListResponse>(path, { params, signal: controller.signal })
      .then((res) => {
        setState({
          staff: res.data,
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
          error: err instanceof ApiError ? err.message : 'Failed to load staff.',
        }));
      });

    return () => controller.abort();
  }, [query, status, refreshKey]);

  return { ...state, refresh, setPage };
}
