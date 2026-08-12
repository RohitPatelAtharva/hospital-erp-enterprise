import { useCallback, useEffect, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type {
  EnterprisePerson,
  EnterprisePersonListResponse,
} from '@/lib/enterprise-person-types';

/**
 * Enterprise Person list data hook.
 *
 * Reads via GET /enterprise-persons, which is paginated (25/page) and ordered by
 * created_at DESC, with an optional `?status=` filter. Status values are NOT
 * enumerated by the backend contract, so the page passes through a caller-supplied
 * free-text status string rather than a typed enum (we never invent statuses).
 *
 * Pagination metadata (page/pageSize/total) comes straight from the API envelope;
 * no client-side paging or fake totals.
 */

interface UseEnterprisePersonsArgs {
  status?: string;
}

interface EnterprisePersonsState {
  persons: EnterprisePerson[];
  loading: boolean;
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
}

const INITIAL: EnterprisePersonsState = {
  persons: [],
  loading: true,
  error: null,
  page: 1,
  pageSize: 25,
  total: 0,
};

export function useEnterprisePersons({ status }: UseEnterprisePersonsArgs = {}) {
  const [state, setState] = useState<EnterprisePersonsState>(INITIAL);
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

    const params: Record<string, string | number> = { page };
    if (status && status.trim().length > 0) params.status = status.trim();

    api
      .get<EnterprisePersonListResponse>('/enterprise-persons', {
        params,
        signal: controller.signal,
      })
      .then((res) => {
        setState({
          persons: res.data,
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
          error: err instanceof ApiError ? err.message : 'Failed to load enterprise persons.',
        }));
      });

    return () => controller.abort();
  }, [status, refreshKey]);

  return { ...state, refresh, setPage };
}
