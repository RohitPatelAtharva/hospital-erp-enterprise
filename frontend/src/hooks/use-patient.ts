import { useCallback, useEffect, useState } from 'react';
import { api, ApiError, type ApiEnvelope } from '@/lib/api-client';
import type {
  Patient,
  PatientAlias,
  PatientChildResource,
  PatientConsent,
  PatientDemographic,
  PatientIdentifier,
  PatientRelation,
  PatientResponse,
} from '@/lib/patient-types';

/**
 * Single-patient data hook.
 *
 * Fetches the patient summary (GET /patients/{id}) and its child resource lists
 * (identifiers/demographics/consents/relations/aliases) in parallel. Exposes
 * loading/error state, a refresh, and lifecycle actions that map 1:1 to the
 * backend routes. Authorization is enforced by the backend; errors from the
 * API are surfaced verbatim.
 */

type ChildData = PatientIdentifier[] | PatientDemographic[] | PatientConsent[] | PatientRelation[] | PatientAlias[];

interface PatientDetailState {
  patient: Patient | null;
  child: Partial<Record<PatientChildResource, ChildData>>;
  loading: boolean;
  error: string | null;
  acting: boolean;
  actionError: string | null;
}

const INITIAL: PatientDetailState = {
  patient: null,
  child: {},
  loading: true,
  error: null,
  acting: false,
  actionError: null,
};

const CHILD_PATHS: PatientChildResource[] = ['identifiers', 'demographics', 'consents', 'relations', 'aliases'];

export type LifecycleAction = 'deactivate' | 'reactivate' | 'archive' | 'restore' | 'purge';

export function usePatient(id: string | undefined) {
  const [state, setState] = useState<PatientDetailState>(INITIAL);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  useEffect(() => {
    if (!id) return;

    const controller = new AbortController();
    setState((prev) => ({ ...prev, loading: true, error: null }));

    const childRequests = CHILD_PATHS.map((path) =>
      api
        .get<ApiEnvelope<ChildData>>(`/patients/${id}/${path}`, { signal: controller.signal })
        .then((res) => ({ path, data: res.data as ChildData }))
        .catch(() => ({ path, data: [] as ChildData })),
    );

    api
      .get<PatientResponse>(`/patients/${id}`, { signal: controller.signal })
      .then(async (patientRes) => {
        const settled = await Promise.all(childRequests);
        const child: Partial<Record<PatientChildResource, ChildData>> = {};
        for (const { path, data } of settled) {
          child[path] = data;
        }
        setState({ patient: patientRes.data, child, loading: false, error: null, acting: false, actionError: null });
      })
      .catch((err) => {
        if (controller.signal.aborted) return;
        setState((prev) => ({
          ...prev,
          loading: false,
          error: err instanceof ApiError ? err.message : 'Failed to load patient.',
        }));
      });

    return () => controller.abort();
  }, [id, refreshKey]);

  const runLifecycle = useCallback(
    async (action: LifecycleAction) => {
      if (!id) return;
      setState((prev) => ({ ...prev, acting: true, actionError: null }));
      try {
        await api.post<PatientResponse>(`/patients/${id}/${action}`);
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
