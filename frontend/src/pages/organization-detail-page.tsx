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
import { OrganizationStatusBadge } from '@/components/organizations/organization-status-badge';
import { OrganizationLifecycleActions } from '@/components/organizations/organization-lifecycle-actions';
import { useOrganizationDetail } from '@/hooks/use-organization-detail';
import { formatDateTime } from '@/lib/utils';
import type {
  OrganizationChildResource,
  OrganizationContact,
  OrganizationIdentifier,
  OrganizationRelationship,
} from '@/lib/organization-types';

const TABS: { key: OrganizationChildResource; label: string }[] = [
  { key: 'identifiers', label: 'Identifiers' },
  { key: 'contacts', label: 'Contacts' },
  { key: 'relationships', label: 'Relationships' },
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

function Uuid({ value }: { value: string | null }) {
  if (!value) return '—';
  return (
    <span className="font-mono text-xs" title={value}>
      {value.slice(0, 8)}…
    </span>
  );
}

export function OrganizationDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [activeTab, setActiveTab] = useState<OrganizationChildResource>('identifiers');

  const { organization, child, loading, error, refresh, acting, actionError, runLifecycle } = useOrganizationDetail(id);

  if (!id) {
    return <ErrorState title="Missing organization ID" message="No organization identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Organization Details"
        description={organization ? `Organization ${organization.name ?? ''}`.trim() : 'Organization record'}
        crumbs={[{ label: 'Master Data' }, { label: 'Organizations', href: '/organizations' }, { label: 'Organization Details' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/organizations">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Organizations
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
        <ErrorState title="Unable to load organization" message={error} onRetry={refresh} />
      ) : organization ? (
        <>
          <div className="grid gap-4 lg:grid-cols-3">
            {/* Summary */}
            <Card className="lg:col-span-2">
              <CardHeader className="flex-row items-start justify-between gap-4">
                <div className="space-y-1">
                  <CardTitle>{organization.name ?? 'Unnamed organization'}</CardTitle>
                  <CardDescription>Organization master record</CardDescription>
                </div>
                <OrganizationStatusBadge status={organization.status} />
              </CardHeader>
              <CardContent>
                <dl className="divide-y">
                  <SummaryRow label="ID" value={<Uuid value={organization.id} />} />
                  <SummaryRow label="Type" value={<Uuid value={organization.organization_type_id} />} />
                  <SummaryRow label="Version" value={organization.version} />
                  <SummaryRow label="Created" value={formatDateTime(organization.created_at)} />
                  <SummaryRow label="Updated" value={formatDateTime(organization.updated_at)} />
                </dl>
              </CardContent>
            </Card>

            {/* Lifecycle */}
            <Card>
              <CardHeader>
                <CardTitle>Record Actions</CardTitle>
                <CardDescription>Manage the lifecycle state of this organization record.</CardDescription>
              </CardHeader>
              <CardContent>
                <OrganizationLifecycleActions
                  organization={organization}
                  acting={acting}
                  actionError={actionError}
                  onAction={runLifecycle}
                />
                <Button variant="outline" size="sm" className="mt-3" onClick={refresh} disabled={acting} aria-label="Refresh organization data">
                  <RefreshCw className="size-4" aria-hidden />
                  Refresh
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Child resources */}
          <Card>
            <CardHeader className="pb-0">
              <div role="tablist" aria-label="Organization sections" className="flex flex-wrap gap-1">
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
  resource: OrganizationChildResource;
  data: OrganizationIdentifier[] | OrganizationContact[] | OrganizationRelationship[] | undefined;
}) {
  if (!data || data.length === 0) {
    return <EmptyState title="No records" description={`No ${resource} exist for this organization.`} className="py-8" />;
  }

  return (
    <div className="space-y-2">
      {resource === 'identifiers' &&
        (data as OrganizationIdentifier[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Identity type" value={<Uuid value={item.identity_type_id} />} />
            <ChildRow label="Value" value={item.value} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'contacts' &&
        (data as OrganizationContact[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Contact" value={<Uuid value={item.contact_id} />} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'relationships' &&
        (data as OrganizationRelationship[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Related organization" value={<Uuid value={item.related_org_id} />} />
            <ChildRow label="Relation type" value={<Uuid value={item.relation_type_id} />} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
    </div>
  );
}
