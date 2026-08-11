import { useCallback, useEffect, useState } from 'react';
import { api, ApiError, type ApiEnvelope } from '@/lib/api-client';
import type {
  Provider,
  ProviderChildResource,
  ProviderCredential,
  ProviderIdentifier,
  ProviderNetwork,
  ProviderResponse,
} from '@/lib/provider-types';

/**
 * Single-provider data hook.
 *
 * Fetches the provider summary (GET /providers/{id}) and its child resource
 * lists (identifiers/credentials/networks) in parallel. Exposes loading/error
 * state, refresh, and lifecycle actions mapping 1:1 to backend routes.
 * Authorization is enforced by the backend; errors surface verbatim.
 */

type ChildData = ProviderIdentifier[] | ProviderCredential[] | ProviderNetwork[];

interface ProviderDetailState {
  provider: Provider | null;
  child: Partial<Record<ProviderChildResource, ChildData>>;
  loading: boolean;
  error: string | null;
  acting: boolean;
  actionError: string | null;
}

const INITIAL: ProviderDetailState = {
  provider: null,
  child: {},
  loading: true,
  error: null,
  acting: false,
  actionError: null,
};

const CHILD_PATHS: ProviderChildResource[] = ['identifiers', 'credentials', 'networks'];

export type ProviderLifecycleAction = 'deactivate' | 'reactivate' | 'archive' | 'restore' | 'purge';

export function useProviderDetail(id: string | undefined) {
  const [state, setState] = useState<ProviderDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    const childRequests = CHILD_PATHS.map((path) =>
      api
        .get<ApiEnvelope<ChildData>>(`/providers/${id}/${path}`, { signal: controller.signal })
        .then((res) => ({ path, data: res.data as ChildData }))
        .catch(() => ({ path, data: [] as ChildData })),
    );

    api
      .get<ProviderResponse>(`/providers/${id}`, { signal: controller.signal })
      .then(async (providerRes) => {
        const settled = await Promise.all(childRequests);
        const child: Partial<Record<ProviderChildResource, ChildData>> = {};
        for (const { path, data } of settled) {
          child[path] = data;
        }
        setState({ provider: providerRes.data, child, loading: false, error: null, acting: false, actionError: null });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load provider.',
        }));
      });

    return () => controller.abort();
  }, [id, refreshKey]);

  const runLifecycle = useCallback(
    async (action: ProviderLifecycleAction) => {
      if (!id) return;
      setState((prev) => ({ ...prev, acting: true, actionError: null }));
      try {
        await api.post<ProviderResponse>(`/providers/${id}/${action}`);
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
