/**
 * Type-safe API client foundation.
 *
 * Wraps fetch against the backend's standard envelope (`{ data, meta }`,
 * 10-API.md §24 error model). This is the single seam pages use to talk to the
 * API; swap transport (axios) or add auth interception here without touching
 * callers. No fabricated data — pages wire real responses into this client.
 */

export interface ApiEnvelope<TData, TMeta = Record<string, unknown>> {
  data: TData;
  meta?: TMeta;
}

export interface ApiErrorBody {
  message?: string;
  errors?: Record<string, string[]>;
  code?: string;
}

export interface ApiRequestOptions extends RequestInit {
  params?: Record<string, string | number | boolean | undefined | null>;
}

const BASE_URL = (import.meta.env.VITE_API_BASE_URL as string | undefined) ?? '/api/v1';

export class ApiError extends Error {
  readonly status: number;
  readonly code?: string;
  readonly errors?: Record<string, string[]>;

  constructor(status: number, body?: ApiErrorBody) {
    super(body?.message ?? `Request failed with status ${status}`);
    this.name = 'ApiError';
    this.status = status;
    this.code = body?.code;
    this.errors = body?.errors;
  }
}

function buildUrl(path: string, params?: ApiRequestOptions['params']): string {
  const url = new URL(`${BASE_URL}${path}`, window.location.origin);
  if (params) {
    for (const [key, value] of Object.entries(params)) {
      if (value !== undefined && value !== null) {
        url.searchParams.set(key, String(value));
      }
    }
  }
  return url.toString();
}

async function request<TData>(path: string, options: ApiRequestOptions = {}): Promise<TData> {
  const { params, headers, ...rest } = options;

  const response = await fetch(buildUrl(path, params), {
    ...rest,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...headers,
    },
  });

  if (!response.ok) {
    let body: ApiErrorBody | undefined;
    try {
      body = (await response.json()) as ApiErrorBody;
    } catch {
      // Non-JSON error body; fall through with status only.
    }
    throw new ApiError(response.status, body);
  }

  if (response.status === 204) {
    return undefined as TData;
  }

  return (await response.json()) as TData;
}

export const api = {
  get<TData>(path: string, options: ApiRequestOptions = {}): Promise<TData> {
    return request<TData>(path, { ...options, method: 'GET' });
  },

  post<TData>(path: string, body?: unknown, options: ApiRequestOptions = {}): Promise<TData> {
    return request<TData>(path, {
      ...options,
      method: 'POST',
      body: body === undefined ? undefined : JSON.stringify(body),
    });
  },

  patch<TData>(path: string, body?: unknown, options: ApiRequestOptions = {}): Promise<TData> {
    return request<TData>(path, {
      ...options,
      method: 'PATCH',
      body: body === undefined ? undefined : JSON.stringify(body),
    });
  },

  delete<TData>(path: string, options: ApiRequestOptions = {}): Promise<TData> {
    return request<TData>(path, { ...options, method: 'DELETE' });
  },
};
