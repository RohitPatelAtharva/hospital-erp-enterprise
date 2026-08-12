import { AlertTriangle, CheckCircle2, CircleDashed, Loader2 } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { SearchSourceState } from '@/lib/search-types';

const ICON: Record<SearchSourceState['status'], typeof Loader2> = {
  idle: CircleDashed,
  loading: Loader2,
  success: CheckCircle2,
  empty: CircleDashed,
  error: AlertTriangle,
  unavailable: AlertTriangle,
};

/**
 * Per-source status chips for the "All" scope. Each of the five endpoints keeps
 * its own outcome so partial results survive a single-source failure, and the
 * UI can clearly flag which source is unavailable vs. merely empty.
 */
export function SearchSourceStatus({ sources }: { sources: SearchSourceState[] }) {
  return (
    <div className="flex flex-wrap gap-2">
      {sources.map((s) => {
        const Icon = ICON[s.status];
        const isProblem = s.status === 'error' || s.status === 'unavailable';
        return (
          <div
            key={s.scope}
            className={cn(
              'flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs',
              isProblem
                ? 'border-destructive/30 bg-destructive/5 text-destructive'
                : 'border-border bg-muted/40 text-muted-foreground',
            )}
            title={s.error ?? undefined}
          >
            <Icon className={cn('size-3.5', s.status === 'loading' && 'animate-spin')} aria-hidden />
            <span className="font-medium">{s.label}</span>
            <span className="tabular-nums">{s.count}</span>
          </div>
        );
      })}
    </div>
  );
}
