import { useCallback, useEffect, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type {
  ReferenceCategory,
  ReferenceCategoryResponse,
  ReferenceLifecycleAction,
} from '@/lib/reference-data-types';

/**
 * Single reference category data hook.
 *
 * Fetches GET /reference-categories/{id}. Note: there is NO backend endpoint
 * that returns a category's child values (no GET /reference-categories/{id}/values),
 * so this hook does not attempt to load values — the UI surfaces an explicit
 * unavailable state instead. Lifecycle actions map 1:1 to backend routes.
 */

interface CategoryDetailState {
  category: ReferenceCategory | null;
  loading: boolean;
  error: string | null;
  acting: boolean;
  actionError: string | null;
}

const INITIAL: CategoryDetailState = {
  category: null,
  loading: true,
  error: null,
  acting: false,
  actionError: null,
};

export function useReferenceCategoryDetail(id: string | undefined) {
  const [state, setState] = useState<CategoryDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    api
      .get<ReferenceCategoryResponse>(`/reference-categories/${id}`, { signal: controller.signal })
      .then((res) => {
        setState({ category: res.data, loading: false, error: null, acting: false, actionError: null });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load reference category.',
        }));
      });

    return () => controller.abort();
  }, [id, refreshKey]);

  const runLifecycle = useCallback(
    async (action: ReferenceLifecycleAction) => {
      if (!id) return;
      setState((prev) => ({ ...prev, acting: true, actionError: null }));
      try {
        await api.post<ReferenceCategoryResponse>(`/reference-categories/${id}/${action}`);
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
