import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, RefreshCw } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { ErrorState } from '@/components/feedback/error-state';
import { EmptyState } from '@/components/feedback/empty-state';
import { StaffStatusBadge } from '@/components/staff/staff-status-badge';
import { StaffLifecycleActions } from '@/components/staff/staff-lifecycle-actions';
import { useStaffDetail } from '@/hooks/use-staff-detail';
import { formatDate, formatDateTime } from '@/lib/utils';
import type {
  StaffChildResource,
  StaffConsent,
  StaffCredential,
  StaffDemographic,
  StaffIdentifier,
} from '@/lib/staff-types';

const TABS: { key: StaffChildResource; label: string }[] = [
  { key: 'identifiers', label: 'Identifiers' },
  { key: 'credentials', label: 'Credentials' },
  { key: 'consents', label: 'Consents' },
  { key: 'demographics', label: 'Demographics' },
];

function SummaryRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between gap-4 border-b py-2.5 last:border-0">
      <dt className="text-muted-foreground text-sm">{label}</dt>
      <dd className="text-sm font-medium text-right">{value}</dd>
    </div>
  );
}

function ChildTable({ children }: { children: React.ReactNode }) {
  return (
    <div className="rounded-md border">
      <table className="w-full text-sm">
        <tbody className="divide-y">{children}</tbody>
      </table>
    </div>
  );
}

function ChildRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <tr>
      <th scope="row" className="text-muted-foreground w-1/3 px-4 py-2.5 text-left font-normal">
        {label}
      </th>
      <td className="px-4 py-2.5 text-right font-medium">{value}</td>
    </tr>
  );
}

export function StaffDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [activeTab, setActiveTab] = useState<StaffChildResource>('identifiers');

  const { staff, child, loading, error, refresh, acting, actionError, runLifecycle } = useStaffDetail(id);

  if (!id) {
    return <ErrorState title="Missing staff ID" message="No staff identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Staff Details"
        description={staff ? `Staff ${staff.name ?? ''}`.trim() : 'Staff record'}
        crumbs={[{ label: 'Master Data' }, { label: 'Staff', href: '/staff' }, { label: 'Staff Details' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/staff">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Staff
            </Link>
          </Button>
        }
      />

      {loading ? (
        <Card>
          <CardHeader>
            <Skeleton className="h-6 w-48" />
          </CardHeader>
          <CardContent className="space-y-4">
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-2/3" />
          </CardContent>
        </Card>
      ) : error ? (
        <ErrorState title="Unable to load staff" message={error} onRetry={refresh} />
      ) : staff ? (
        <>
          <div className="grid gap-4 lg:grid-cols-3">
            {/* Summary */}
            <Card className="lg:col-span-2">
              <CardHeader className="flex-row items-start justify-between gap-4">
                <div className="space-y-1">
                  <CardTitle>{staff.name ?? 'Unnamed staff member'}</CardTitle>
                  <CardDescription>Staff master record</CardDescription>
                </div>
                <StaffStatusBadge status={staff.status} />
              </CardHeader>
              <CardContent>
                <dl className="divide-y">
                  <SummaryRow label="ID" value={<span className="font-mono text-xs">{staff.id}</span>} />
                  <SummaryRow label="Version" value={staff.version} />
                  <SummaryRow label="Created" value={formatDateTime(staff.created_at)} />
                  <SummaryRow label="Updated" value={formatDateTime(staff.updated_at)} />
                </dl>
              </CardContent>
            </Card>

            {/* Lifecycle */}
            <Card>
              <CardHeader>
                <CardTitle>Record Actions</CardTitle>
                <CardDescription>Manage the lifecycle state of this staff record.</CardDescription>
              </CardHeader>
              <CardContent>
                <StaffLifecycleActions
                  staff={staff}
                  acting={acting}
                  actionError={actionError}
                  onAction={runLifecycle}
                />
                <Button variant="outline" size="sm" className="mt-3" onClick={refresh} disabled={acting} aria-label="Refresh staff data">
                  <RefreshCw className="size-4" aria-hidden />
                  Refresh
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Child resources */}
          <Card>
            <CardHeader className="pb-0">
              <div role="tablist" aria-label="Staff sections" className="flex flex-wrap gap-1">
                {TABS.map((tab) => (
                  <button
                    key={tab.key}
                    role="tab"
                    aria-selected={activeTab === tab.key}
                    aria-controls={`tab-${tab.key}`}
                    id={`tab-button-${tab.key}`}
                    onClick={() => setActiveTab(tab.key)}
                    className={`rounded-md px-3 py-1.5 text-sm font-medium transition-colors focus-visible:ring-ring focus-visible:ring-2 focus-visible:outline-none ${
                      activeTab === tab.key
                        ? 'bg-accent text-accent-foreground'
                        : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground'
                    }`}
                  >
                    {tab.label}
                  </button>
                ))}
              </div>
            </CardHeader>
            <CardContent>
              <div role="tabpanel" id={`tab-${activeTab}`} aria-labelledby={`tab-button-${activeTab}`}>
                <ChildResourcePanel resource={activeTab} data={child[activeTab]} />
              </div>
            </CardContent>
          </Card>
        </>
      ) : null}
    </PageContainer>
  );
}

function ChildResourcePanel({
  resource,
  data,
}: {
  resource: StaffChildResource;
  data:
    | StaffIdentifier[]
    | StaffCredential[]
    | StaffConsent[]
    | StaffDemographic[]
    | undefined;
}) {
  if (!data || data.length === 0) {
    return <EmptyState title="No records" description={`No ${resource} exist for this staff member.`} className="py-8" />;
  }

  return (
    <div className="space-y-2">
      {resource === 'identifiers' &&
        (data as StaffIdentifier[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Identity type" value={<span className="font-mono text-xs">{item.identity_type_id}</span>} />
            <ChildRow label="Value" value={item.value} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'credentials' &&
        (data as StaffCredential[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Credential type" value={<span className="font-mono text-xs">{item.credential_type_id}</span>} />
            <ChildRow label="Number" value={item.number ?? '—'} />
            <ChildRow label="Expiry" value={formatDate(item.expiry)} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'consents' &&
        (data as StaffConsent[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Consent type" value={<span className="font-mono text-xs">{item.consent_type_id}</span>} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'demographics' &&
        (data as StaffDemographic[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
            <ChildRow label="Created" value={formatDateTime(item.created_at)} />
          </ChildTable>
        ))}
    </div>
  );
}
