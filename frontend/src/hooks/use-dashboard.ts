import { useCallback, useEffect, useState } from 'react';
import { api, ApiError, type ApiEnvelope } from '@/lib/api-client';

/**
 * Dashboard data hook.
 *
 * Fetches real, read-only registry metrics in parallel and tracks per-metric
 * loading/error state so one failing endpoint does not blank the others.
 * No fabricated numbers: a metric is only populated when its endpoint returns a
 * usable total. Aggregates the API has no endpoint for are left unavailable.
 */

export type MetricStatus = 'loading' | 'success' | 'error';

export interface DashboardMetric {
  status: MetricStatus;
  total?: number;
  error?: string;
}

export type HealthStatus = 'loading' | 'connected' | 'degraded' | 'unavailable';

export interface DashboardHealth {
  status: HealthStatus;
  service?: string;
}

export interface DashboardState {
  patients: DashboardMetric;
  staff: DashboardMetric;
  providers: DashboardMetric;
  organizations: DashboardMetric;
  referenceCategories: DashboardMetric;
  health: DashboardHealth;
  loading: boolean;
}

export interface DashboardSnapshot extends DashboardState {
  refresh: () => void;
}

interface PaginatedResponseMeta {
  total?: number;
  page?: number;
  pageSize?: number;
}

const INITIAL_METRIC: DashboardMetric = { status: 'loading' };
const INITIAL_HEALTH: DashboardHealth = { status: 'loading' };

const INITIAL_STATE: DashboardState = {
  patients: INITIAL_METRIC,
  staff: INITIAL_METRIC,
  providers: INITIAL_METRIC,
  organizations: INITIAL_METRIC,
  referenceCategories: INITIAL_METRIC,
  health: INITIAL_HEALTH,
  loading: true,
};

function toMetric<T>(settled: PromiseSettledResult<T>, totalOf: (value: T) => number | undefined): DashboardMetric {
  if (settled.status === 'fulfilled') {
    const total = totalOf(settled.value);
    if (typeof total === 'number') {
      return { status: 'success', total };
    }
    return { status: 'error', error: 'Response did not include a total.' };
  }
  return {
    status: 'error',
    error: settled.reason instanceof ApiError ? settled.reason.message : 'Request failed.',
  };
}

function loadCount(path: string): Promise<number> {
  return api.get<ApiEnvelope<unknown[], PaginatedResponseMeta>>(path).then((res) => {
    const total = res.meta?.total;
    if (typeof total !== 'number') {
      throw new ApiError(200, { message: `No total available for ${path}.` });
    }
    return total;
  });
}

function loadHealth(): Promise<DashboardHealth> {
  return api
    .get<ApiEnvelope<{ status?: string; checks?: Record<string, string> }>>('/health')
    .then((res) => {
      const status = res.data?.status;
      if (status === 'ok') return { status: 'connected', service: res.meta?.service as string | undefined };
      if (status === 'degraded') return { status: 'degraded', service: res.meta?.service as string | undefined };
      return { status: 'unavailable', service: res.meta?.service as string | undefined };
    });
}

export function useDashboard(): DashboardSnapshot {
  const [state, setState] = useState<DashboardState>(INITIAL_STATE);
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = useCallback(() => {
    setRefreshKey((key) => key + 1);
  }, []);

  useEffect(() => {
    let cancelled = false;

    setState((prev) => ({
      ...prev,
      loading: true,
      patients: INITIAL_METRIC,
      staff: INITIAL_METRIC,
      providers: INITIAL_METRIC,
      organizations: INITIAL_METRIC,
      referenceCategories: INITIAL_METRIC,
      health: INITIAL_HEALTH,
    }));

    Promise.allSettled([
      loadCount('/patients'),
      loadCount('/staff'),
      loadCount('/providers'),
      loadCount('/organizations'),
      loadCount('/reference-categories'),
      loadHealth(),
    ]).then(([patients, staff, providers, organizations, referenceCategories, health]) => {
      if (cancelled) return;
      setState({
        patients: toMetric(patients, (total) => total),
        staff: toMetric(staff, (total) => total),
        providers: toMetric(providers, (total) => total),
        organizations: toMetric(organizations, (total) => total),
        referenceCategories: toMetric(referenceCategories, (total) => total),
        health: health.status === 'fulfilled' ? health.value : { status: 'unavailable' },
        loading: false,
      });
    });

    return () => {
      cancelled = true;
    };
  }, [refreshKey]);

  return { ...state, refresh };
}
