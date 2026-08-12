import { useCallback, useEffect, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type { EnterprisePerson, EnterprisePersonResponse } from '@/lib/enterprise-person-types';

/**
 * Single Enterprise Person data hook.
 *
 * Fetches GET /enterprise-persons/{id} and exposes loading/error state plus a
 * refresh. A 404 from the API is surfaced as a specific not-found state so the
 * detail page can render an honest "not found" view rather than a generic error.
 * Authorization is enforced by the backend; errors are surfaced verbatim.
 */

interface EnterprisePersonDetailState {
  person: EnterprisePerson | null;
  loading: boolean;
  error: string | null;
  notFound: boolean;
}

const INITIAL: EnterprisePersonDetailState = {
  person: null,
  loading: true,
  error: null,
  notFound: false,
};

export function useEnterprisePerson(id: string | undefined) {
  const [state, setState] = useState<EnterprisePersonDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null, notFound: false }));

    api
      .get<EnterprisePersonResponse>(`/enterprise-persons/${id}`, { signal: controller.signal })
      .then((res) => {
        setState({ person: res.data, loading: false, error: null, notFound: false });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        if (err instanceof ApiError && err.status === 404) {
          setState((prev) => ({ ...prev, loading: false, notFound: true }));
          return;
        }
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load enterprise person.',
        }));
      });

    return () => controller.abort();
  }, [id, refreshKey]);

  return { ...state, refresh };
}
