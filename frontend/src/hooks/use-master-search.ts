import { useCallback, useEffect, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api-client';
import type {
  SearchListResponse,
  SearchResult,
  SearchScope,
  SearchSourceState,
} from '@/lib/search-types';

/**
 * Master Data Search hook (Step8).
 *
 * - Debounces the query (~300ms) so we never fire on every keystroke.
 * - Uses an AbortController per run so superseded queries cancel in flight.
 * - Concrete scopes (patients/staff/providers/organizations/master) hit their
 *   dedicated endpoint: GET /search/{scope}?q=&page=.
 * - The "all" scope fans out to all five endpoints via Promise.allSettled and
 *   preserves partial results; each source keeps its own success/error/empty
 *   state so the UI can show "source unavailable" without discarding the rest.
 * - Honors real pagination (the API paginates at 25); no fabricated controls.
 */

interface UseMasterSearchOptions {
  /** Initial query. */
  initialQuery?: string;
  /** Initial scope (defaults to "all"). */
  initialScope?: SearchScope;
  /** Debounce window in ms (defaults to 300). */
  debounceMs?: number;
}

interface MasterSearchState {
  query: string;
  scope: SearchScope;
  results: SearchResult[];
  /** Per-source status; for a concrete scope this is a single entry. */
  sources: SearchSourceState[];
  loading: boolean;
  /** Global error only set when even a concrete scope fails outright. */
  error: string | null;
  page: number;
  pageSize: number;
  total: number;
}

const SCOPE_LABELS: Record<SearchScope, string> = {
  all: 'All',
  patients: 'Patients',
  staff: 'Staff',
  providers: 'Providers',
  organizations: 'Organizations',
  master: 'Master Records',
};

const CONCRETE_SCOPES: Exclude<SearchScope, 'all'>[] = [
  'patients',
  'staff',
  'providers',
  'organizations',
  'master',
];

const PAGE_SIZE = 25;

function endpointFor(scope: Exclude<SearchScope, 'all'>): string {
  return `/search/${scope}`;
}

function idleSources(scope: SearchScope): SearchSourceState[] {
  if (scope === 'all') {
    return CONCRETE_SCOPES.map((s) => ({
      scope: s,
      label: SCOPE_LABELS[s],
      status: 'idle',
      count: 0,
    }));
  }
  return [{ scope, label: SCOPE_LABELS[scope], status: 'idle', count: 0 }];
}

function emptyState(query: string, scope: SearchScope): MasterSearchState {
  return {
    query,
    scope,
    results: [],
    sources: idleSources(scope),
    loading: false,
    error: null,
    page: 1,
    pageSize: PAGE_SIZE,
    total: 0,
  };
}

function describeError(err: unknown): string {
  if (err instanceof ApiError) {
    if (err.status === 401) return 'Your session has expired. Please sign in again.';
    if (err.status === 403) return 'You do not have permission to search this source.';
    if (err.status === 429) return 'Too many requests. Please wait a moment and retry.';
    if (err.status >= 500) return 'The search service is unavailable. Please retry.';
    return err.message || 'Search failed.';
  }
  return 'Search failed.';
}

export function useMasterSearch(options: UseMasterSearchOptions = {}) {
  const { initialQuery = '', initialScope = 'all', debounceMs = 300 } = options;

  const [query, setQuery] = useState(initialQuery);
  const [scope, setScope] = useState<SearchScope>(initialScope);
  const [state, setState] = useState<MasterSearchState>(() => emptyState(initialQuery, initialScope));
  const [refreshKey, setRefreshKey] = useState(0);

  const pageRef = useRef(1);
  const debounceRef = useRef<number | undefined>(undefined);
  const abortRef = useRef<AbortController | null>(null);
  // When the user paginates we want an immediate refetch, not the typing debounce.
  const skipDebounceRef = useRef(false);

  const refresh = useCallback(() => setRefreshKey((key) => key + 1), []);

  const setPage = useCallback((page: number) => {
    skipDebounceRef.current = true;
    pageRef.current = page;
    setState((prev) => ({ ...prev, page }));
  }, []);

  // Keep page so scope/query changes reset it, but never fire a debounced run.
  useEffect(() => {
    skipDebounceRef.current = false;
    pageRef.current = 1;
    setState((prev) => ({ ...prev, page: 1, results: [], total: 0 }));
  }, [scope, query]);

  useEffect(() => {
    // Cancel any pending debounce when deps change.
    window.clearTimeout(debounceRef.current);
    abortRef.current?.abort();

    const trimmed = query.trim();

    // No query yet: show the "start typing" empty state, not a loading spinner.
    if (trimmed.length === 0) {
      setState(emptyState(query, scope));
      return;
    }

    const controller = new AbortController();
    abortRef.current = controller;

    const execute = () => {
      setState((prev) => ({
        ...prev,
        query,
        scope,
        loading: true,
        error: null,
        sources: idleSources(scope).map((s) => ({ ...s, status: 'loading' })),
      }));

      const page = pageRef.current;
      const run = async () => {
        if (scope === 'all') {
          await runAll(controller.signal, page);
        } else {
          await runOne(scope, controller.signal, page);
        }
      };

      run().catch(() => {
        // Aborted requests are expected; everything else is handled inside run*.
        if (controller.signal.aborted) return;
      });
    };

    // Page changes skip the debounce; typing is debounced.
    if (skipDebounceRef.current) {
      skipDebounceRef.current = false;
      execute();
    } else {
      debounceRef.current = window.setTimeout(execute, debounceMs);
    }

    return () => {
      window.clearTimeout(debounceRef.current);
      controller.abort();
    };
  // runOne/runAll are recreated every render but intentionally omitted: they
  // only read the latest `query`/`scope` via closure, which are already
  // dependencies, and including them would re-trigger the debounce mid-flight.
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [query, scope, pageRef.current, debounceMs, refreshKey]);

  async function runOne(
    scope: Exclude<SearchScope, 'all'>,
    signal: AbortSignal,
    page: number,
  ) {
    try {
      const res = await api.get<SearchListResponse>(endpointFor(scope), {
        params: { q: query.trim(), page },
        signal,
      });
      if (signal.aborted) return;
      const data = res.data ?? [];
      setState((prev) => ({
        ...prev,
        loading: false,
        results: data,
        total: res.meta?.total ?? data.length,
        pageSize: res.meta?.pageSize ?? PAGE_SIZE,
        error: null,
        sources: [
          {
            scope,
            label: SCOPE_LABELS[scope],
            status: data.length === 0 ? 'empty' : 'success',
            count: res.meta?.total ?? data.length,
          },
        ],
      }));
    } catch (err) {
      if (signal.aborted) return;
      const message = describeError(err);
      setState((prev) => ({
        ...prev,
        loading: false,
        results: [],
        error: message,
        sources: [{ scope, label: SCOPE_LABELS[scope], status: 'error', count: 0, error: message }],
      }));
    }
  }

  async function runAll(signal: AbortSignal, page: number) {
    const settled = await Promise.allSettled(
      CONCRETE_SCOPES.map((s) =>
        api.get<SearchListResponse>(endpointFor(s), {
          params: { q: query.trim(), page },
          signal,
        }),
      ),
    );
    if (signal.aborted) return;

    const sources: SearchSourceState[] = [];
    const merged: SearchResult[] = [];

    settled.forEach((outcome, index) => {
      const s = CONCRETE_SCOPES[index];
      if (outcome.status === 'fulfilled') {
        const res = outcome.value;
        const data = res.data ?? [];
        merged.push(...data);
        sources.push({
          scope: s,
          label: SCOPE_LABELS[s],
          status: data.length === 0 ? 'empty' : 'success',
          count: res.meta?.total ?? data.length,
        });
      } else {
        const reason = outcome.reason;
        // A rejected (aborted) request mid-flight is treated as unavailable.
        const status: SearchSourceState['status'] =
          reason instanceof ApiError && (reason.status === 401 || reason.status === 403)
            ? 'unavailable'
            : 'error';
        sources.push({
          scope: s,
          label: SCOPE_LABELS[s],
          status,
          count: 0,
          error: describeError(reason),
        });
      }
    });

    const anyFailed = sources.some((s) => s.status === 'error' || s.status === 'unavailable');
    const anySuccess = sources.some((s) => s.status === 'success' || s.status === 'empty');

    setState((prev) => ({
      ...prev,
      loading: false,
      results: merged,
      // Total is only meaningful within a single source; for "all" we surface
      // per-source counts instead. We still expose a best-effort merged total.
      total: sources.reduce((sum, s) => sum + (s.count || 0), 0),
      pageSize: PAGE_SIZE,
      error: anyFailed && !anySuccess ? 'One or more search sources failed.' : null,
      sources,
    }));
  }

  /** Clear the query (and therefore results) without losing the chosen scope. */
  const clear = useCallback(() => {
    setQuery('');
    pageRef.current = 1;
  }, []);

  return {
    ...state,
    setQuery,
    setScope,
    setPage,
    refresh,
    clear,
  };
}
