import { useCallback, useEffect, useState } from 'react';
import { api, ApiError, type ApiEnvelope } from '@/lib/api-client';
import type {
  Organization,
  OrganizationChildResource,
  OrganizationContact,
  OrganizationIdentifier,
  OrganizationRelationship,
  OrganizationResponse,
} from '@/lib/organization-types';

/**
 * Single-organization data hook.
 *
 * Fetches the organization summary (GET /organizations/{id}) and its child
 * resource lists (identifiers/contacts/relationships) in parallel. Exposes
 * loading/error state, refresh, and lifecycle actions mapping 1:1 to backend
 * routes. Authorization is enforced by the backend; errors surface verbatim.
 */

type ChildData = OrganizationIdentifier[] | OrganizationContact[] | OrganizationRelationship[];

interface OrganizationDetailState {
  organization: Organization | null;
  child: Partial<Record<OrganizationChildResource, ChildData>>;
  loading: boolean;
  error: string | null;
  acting: boolean;
  actionError: string | null;
}

const INITIAL: OrganizationDetailState = {
  organization: null,
  child: {},
  loading: true,
  error: null,
  acting: false,
  actionError: null,
};

const CHILD_PATHS: OrganizationChildResource[] = ['identifiers', 'contacts', 'relationships'];

export type OrganizationLifecycleAction = 'deactivate' | 'reactivate' | 'archive' | 'restore' | 'purge';

export function useOrganizationDetail(id: string | undefined) {
  const [state, setState] = useState<OrganizationDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    const childRequests = CHILD_PATHS.map((path) =>
      api
        .get<ApiEnvelope<ChildData>>(`/organizations/${id}/${path}`, { signal: controller.signal })
        .then((res) => ({ path, data: res.data as ChildData }))
        .catch(() => ({ path, data: [] as ChildData })),
    );

    api
      .get<OrganizationResponse>(`/organizations/${id}`, { signal: controller.signal })
      .then(async (organizationRes) => {
        const settled = await Promise.all(childRequests);
        const child: Partial<Record<OrganizationChildResource, ChildData>> = {};
        for (const { path, data } of settled) {
          child[path] = data;
        }
        setState({ organization: organizationRes.data, child, loading: false, error: null, acting: false, actionError: null });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load organization.',
        }));
      });

    return () => controller.abort();
  }, [id, refreshKey]);

  const runLifecycle = useCallback(
    async (action: OrganizationLifecycleAction) => {
      if (!id) return;
      setState((prev) => ({ ...prev, acting: true, actionError: null }));
      try {
        await api.post<OrganizationResponse>(`/organizations/${id}/${action}`);
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
