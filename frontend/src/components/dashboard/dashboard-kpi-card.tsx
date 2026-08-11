import type { LucideIcon } from 'lucide-react';
import { Minus } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import type { DashboardMetric } from '@/hooks/use-dashboard';

function formatCount(value: number): string {
  return value.toLocaleString();
}

export function DashboardKpiCard({
  label,
  icon: Icon,
  metric,
  className,
}: {
  label: string;
  icon: LucideIcon;
  metric: DashboardMetric;
  className?: string;
}) {
  return (
    <Card className={cn('gap-3 py-5', className)}>
      <CardContent className="flex items-start justify-between gap-3 px-5">
        <div className="space-y-2">
          <p className="text-muted-foreground text-sm font-medium">{label}</p>
          {metric.status === 'loading' && <Skeleton className="h-9 w-20" aria-label="Loading" />}
          {metric.status === 'success' && (
            <span className="text-2xl font-semibold tabular-nums">{formatCount(metric.total ?? 0)}</span>
          )}
          {metric.status === 'error' && (
            <span className="text-muted-foreground text-sm">Unavailable</span>
          )}
        </div>
        <div className="bg-muted flex size-10 shrink-0 items-center justify-center rounded-lg">
          <Icon className="text-muted-foreground size-5" aria-hidden />
        </div>
      </CardContent>
      {metric.status === 'error' && (
        <CardContent className="flex items-center gap-1.5 px-5 pt-0 text-xs text-destructive">
          <Minus className="size-3.5 shrink-0" aria-hidden />
          <span className="text-balance">{metric.error ?? 'Could not load.'}</span>
        </CardContent>
      )}
    </Card>
  );
}
