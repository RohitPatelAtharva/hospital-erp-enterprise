import type { LucideIcon } from 'lucide-react';
import { Users, UserRound, Stethoscope, Building2, BookMarked, Library } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import type { DashboardMetric, DashboardState } from '@/hooks/use-dashboard';

interface OverviewRow {
  title: string;
  href: string;
  icon: LucideIcon;
  metric?: DashboardMetric;
  /** Renders as "unavailable" because no endpoint provides a count. */
  unavailable?: boolean;
}

function CountCell({ metric, unavailable }: { metric?: DashboardMetric; unavailable?: boolean }) {
  if (unavailable) return <span className="text-muted-foreground text-sm">Data unavailable</span>;
  if (!metric) return null;
  if (metric.status === 'loading') return <Skeleton className="h-5 w-16" aria-label="Loading" />;
  if (metric.status === 'error') return <span className="text-muted-foreground text-sm">Unavailable</span>;
  return <span className="text-sm font-medium tabular-nums">{metric.total?.toLocaleString()}</span>;
}

function StatusCell({ metric, unavailable }: { metric?: DashboardMetric; unavailable?: boolean }) {
  if (unavailable) return <Badge variant="muted">No endpoint</Badge>;
  if (!metric) return null;
  if (metric.status === 'success') return <Badge variant="success">Live</Badge>;
  if (metric.status === 'error') return <Badge variant="destructive">Error</Badge>;
  return <Badge variant="muted">Loading</Badge>;
}

export function MasterDataOverview({ state }: { state: DashboardState }) {
  const rows: OverviewRow[] = [
    { title: 'Patients', href: '/patients', icon: Users, metric: state.patients },
    { title: 'Staff', href: '/staff', icon: UserRound, metric: state.staff },
    { title: 'Providers', href: '/providers', icon: Stethoscope, metric: state.providers },
    { title: 'Organizations', href: '/organizations', icon: Building2, metric: state.organizations },
    { title: 'Reference Data', href: '/reference-data', icon: BookMarked, unavailable: true },
    { title: 'Master Records', href: '/master-records', icon: Library, unavailable: true },
  ];

  return (
    <Card>
      <CardHeader>
        <CardTitle>Master Data Overview</CardTitle>
      </CardHeader>
      <CardContent>
        <ul className="divide-y">
          {rows.map((row) => {
            const Icon = row.icon;
            return (
              <li key={row.title}>
                <Link
                  to={row.href}
                  className={cn(
                    'flex items-center gap-4 rounded-md px-2 py-3 transition-colors',
                    'hover:bg-accent focus-visible:ring-ring focus-visible:ring-2 focus-visible:outline-none',
                  )}
                >
                  <div className="bg-muted flex size-9 shrink-0 items-center justify-center rounded-lg">
                    <Icon className="text-muted-foreground size-4" aria-hidden />
                  </div>
                  <span className="flex-1 text-sm font-medium">{row.title}</span>
                  <StatusCell metric={row.metric} unavailable={row.unavailable} />
                  <span className="w-28 text-right">
                    <CountCell metric={row.metric} unavailable={row.unavailable} />
                  </span>
                </Link>
              </li>
            );
          })}
        </ul>
      </CardContent>
    </Card>
  );
}
