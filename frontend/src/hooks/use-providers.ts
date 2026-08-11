import { useCallback, useEffect, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type { Provider, ProviderListResponse, ProviderStatus } from '@/lib/provider-types';

/**
 * Provider list data hook.
 *
 * The backend exposes two read surfaces for the provider registry:
 *   - GET /providers           (paginated; optional ?status= filter)
 *   - GET /search/providers?q= (name search; paginated)
 *
 * `status` and `query` are controlled from the page. The hook selects the
 * appropriate endpoint and page, and exposes loading/error state + refresh.
 */

interface ProviderListState {
  providers: Provider[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
}

const INITIAL: ProviderListState = {
  providers: [],
  loading: true,
  error: null,
  page: 1,
  pageSize: 25,
  total: 0,
};

export function useProviders({ status, query }: { status?: ProviderStatus | ''; query?: string }) {
  const [state, setState] = useState<ProviderListState>(INITIAL);
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

    const path = isSearch ? '/search/providers' : '/providers';

    api
      .get<ProviderListResponse>(path, { params, signal: controller.signal })
      .then((res) => {
        setState({
          providers: res.data,
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
          error: err instanceof ApiError ? err.message : 'Failed to load providers.',
        }));
      });

    return () => controller.abort();
  }, [query, status, refreshKey]);

  return { ...state, refresh, setPage };
}
