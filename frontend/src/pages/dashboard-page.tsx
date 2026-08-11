import { RefreshCw, Search } from 'lucide-react';
import { Link } from 'react-router-dom';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { useDashboard } from '@/hooks/use-dashboard';
import { DashboardKpiGrid } from '@/components/dashboard/dashboard-kpi-grid';
import { MasterDataOverview } from '@/components/dashboard/master-data-overview';
import { LifecycleOverview } from '@/components/dashboard/lifecycle-overview';
import { RecentActivity } from '@/components/dashboard/recent-activity';
import { QuickActions } from '@/components/dashboard/quick-actions';
import { SystemStatus } from '@/components/dashboard/system-status';

export function DashboardPage() {
  const state = useDashboard();

  return (
    <PageContainer>
      <PageHeader
        title="Master Data Dashboard"
        description="Centralized overview of hospital master data"
        crumbs={[{ label: 'Dashboard' }]}
        actions={
          <>
            <Button variant="outline" size="sm" onClick={state.refresh} disabled={state.loading}>
              <RefreshCw className={`size-4 ${state.loading ? 'animate-spin' : ''}`} aria-hidden />
              Refresh
            </Button>
            <Button variant="outline" size="sm" asChild>
              <Link to="/search">
                <Search className="size-4" aria-hidden />
                Search
              </Link>
            </Button>
          </>
        }
      />

      <DashboardKpiGrid state={state} />

      <div className="grid gap-4 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <MasterDataOverview state={state} />
        </div>
        <div className="space-y-4">
          <QuickActions />
          <SystemStatus health={state.health} />
        </div>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <LifecycleOverview />
        <RecentActivity />
      </div>
    </PageContainer>
  );
}
