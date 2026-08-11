import { Users, UserRound, Stethoscope, Building2 } from 'lucide-react';
import { DashboardKpiCard } from '@/components/dashboard/dashboard-kpi-card';
import type { DashboardState } from '@/hooks/use-dashboard';

export function DashboardKpiGrid({ state }: { state: DashboardState }) {
  return (
    <section aria-label="Registry key metrics">
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <DashboardKpiCard label="Patients" icon={Users} metric={state.patients} />
        <DashboardKpiCard label="Staff" icon={UserRound} metric={state.staff} />
        <DashboardKpiCard label="Providers" icon={Stethoscope} metric={state.providers} />
        <DashboardKpiCard label="Organizations" icon={Building2} metric={state.organizations} />
      </div>
    </section>
  );
}
