import { useCallback, useEffect, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type {
  ReferenceStatus,
  ReferenceValue,
  ReferenceValueListResponse,
} from '@/lib/reference-data-types';

/**
 * Reference value list hook.
 *
 * Backend surface: GET /reference-values (paginated; optional ?status=).
 * No search endpoint exists for reference values, so this hook only supports
 * status filtering + pagination — never a fabricated client-side search.
 */

interface ValueListState {
  values: ReferenceValue[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
}

const INITIAL: ValueListState = {
  values: [],
  loading: true,
  error: null,
  page: 1,
  pageSize: 25,
  total: 0,
};

export function useReferenceValues({ status }: { status?: ReferenceStatus | '' }) {
  const [state, setState] = useState<ValueListState>(INITIAL);
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
    const params: Record<string, string | number> = { page };
    if (status) params.status = status;

    setState((prev) => ({ ...prev, loading: true, error: null }));

    api
      .get<ReferenceValueListResponse>('/reference-values', { params, signal: controller.signal })
      .then((res) => {
        setState({
          values: res.data,
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
          error: err instanceof ApiError ? err.message : 'Failed to load reference values.',
        }));
      });

    return () => controller.abort();
  }, [status, refreshKey]);

  return { ...state, refresh, setPage };
}
