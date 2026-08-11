import { useCallback, useEffect, useState } from 'react';
import { api, ApiError, type ApiEnvelope } from '@/lib/api-client';
import type {
  Staff,
  StaffChildResource,
  StaffConsent,
  StaffCredential,
  StaffDemographic,
  StaffIdentifier,
  StaffResponse,
} from '@/lib/staff-types';

/**
 * Single-staff data hook.
 *
 * Fetches the staff summary (GET /staff/{id}) and its child resource lists
 * (identifiers/credentials/consents/demographics) in parallel. Exposes
 * loading/error state, refresh, and lifecycle actions mapping 1:1 to backend
 * routes. Authorization is enforced by the backend; errors surface verbatim.
 */

type ChildData = StaffIdentifier[] | StaffCredential[] | StaffConsent[] | StaffDemographic[];

interface StaffDetailState {
  staff: Staff | null;
  child: Partial<Record<StaffChildResource, ChildData>>;
  loading: boolean;
  error: string | null;
  acting: boolean;
  actionError: string | null;
}

const INITIAL: StaffDetailState = {
  staff: null,
  child: {},
  loading: true,
  error: null,
  acting: false,
  actionError: null,
};

const CHILD_PATHS: StaffChildResource[] = ['identifiers', 'credentials', 'consents', 'demographics'];

export type StaffLifecycleAction = 'deactivate' | 'reactivate' | 'archive' | 'restore' | 'purge';

export function useStaffDetail(id: string | undefined) {
  const [state, setState] = useState<StaffDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    const childRequests = CHILD_PATHS.map((path) =>
      api
        .get<ApiEnvelope<ChildData>>(`/staff/${id}/${path}`, { signal: controller.signal })
        .then((res) => ({ path, data: res.data as ChildData }))
        .catch(() => ({ path, data: [] as ChildData })),
    );

    api
      .get<StaffResponse>(`/staff/${id}`, { signal: controller.signal })
      .then(async (staffRes) => {
        const settled = await Promise.all(childRequests);
        const child: Partial<Record<StaffChildResource, ChildData>> = {};
        for (const { path, data } of settled) {
          child[path] = data;
        }
        setState({ staff: staffRes.data, child, loading: false, error: null, acting: false, actionError: null });
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
  }, [id, refreshKey]);

  const runLifecycle = useCallback(
    async (action: StaffLifecycleAction) => {
      if (!id) return;
      setState((prev) => ({ ...prev, acting: true, actionError: null }));
      try {
        await api.post<StaffResponse>(`/staff/${id}/${action}`);
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
