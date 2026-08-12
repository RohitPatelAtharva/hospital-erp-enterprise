import { useCallback, useEffect, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type {
  ReferenceLifecycleAction,
  ReferenceValue,
  ReferenceValueResponse,
} from '@/lib/reference-data-types';

/**
 * Single reference value data hook.
 *
 * Fetches GET /reference-values/{id} and exposes lifecycle actions mapping
 * 1:1 to backend routes.
 */

interface ValueDetailState {
  value: ReferenceValue | null;
  loading: boolean;
  error: string | null;
  acting: boolean;
  actionError: string | null;
}

const INITIAL: ValueDetailState = {
  value: null,
  loading: true,
  error: null,
  acting: false,
  actionError: null,
};

export function useReferenceValueDetail(id: string | undefined) {
  const [state, setState] = useState<ValueDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    api
      .get<ReferenceValueResponse>(`/reference-values/${id}`, { signal: controller.signal })
      .then((res) => {
        setState({ value: res.data, loading: false, error: null, acting: false, actionError: null });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load reference value.',
        }));
      });

    return () => controller.abort();
  }, [id, refreshKey]);

  const runLifecycle = useCallback(
    async (action: ReferenceLifecycleAction) => {
      if (!id) return;
      setState((prev) => ({ ...prev, acting: true, actionError: null }));
      try {
        await api.post<ReferenceValueResponse>(`/reference-values/${id}/${action}`);
        setState((prev) => ({ ...prev, acting: false }));
        refresh();
      } catch (err) {
        setState((prev) => ({
          ...prev,
          acting: false,
          actionError: err instanceof ApiError ? err.message : `Failed to ${action}.`,
        }));
      }
    },
    [id, refresh],
  );

  return { ...state, refresh, runLifecycle };
}
