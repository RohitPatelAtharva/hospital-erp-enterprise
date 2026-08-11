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
import { ProviderStatusBadge } from '@/components/providers/provider-status-badge';
import { ProviderLifecycleActions } from '@/components/providers/provider-lifecycle-actions';
import { useProviderDetail } from '@/hooks/use-provider-detail';
import { formatDateTime } from '@/lib/utils';
import type {
  ProviderChildResource,
  ProviderCredential,
  ProviderIdentifier,
  ProviderNetwork,
} from '@/lib/provider-types';

const TABS: { key: ProviderChildResource; label: string }[] = [
  { key: 'identifiers', label: 'Identifiers' },
  { key: 'credentials', label: 'Credentials' },
  { key: 'networks', label: 'Networks' },
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

export function ProviderDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [activeTab, setActiveTab] = useState<ProviderChildResource>('identifiers');

  const { provider, child, loading, error, refresh, acting, actionError, runLifecycle } = useProviderDetail(id);

  if (!id) {
    return <ErrorState title="Missing provider ID" message="No provider identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Provider Details"
        description={provider ? `Provider ${provider.name ?? ''}`.trim() : 'Provider record'}
        crumbs={[{ label: 'Master Data' }, { label: 'Providers', href: '/providers' }, { label: 'Provider Details' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/providers">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Providers
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
        <ErrorState title="Unable to load provider" message={error} onRetry={refresh} />
      ) : provider ? (
        <>
          <div className="grid gap-4 lg:grid-cols-3">
            {/* Summary */}
            <Card className="lg:col-span-2">
              <CardHeader className="flex-row items-start justify-between gap-4">
                <div className="space-y-1">
                  <CardTitle>{provider.name ?? 'Unnamed provider'}</CardTitle>
                  <CardDescription>Provider master record</CardDescription>
                </div>
                <ProviderStatusBadge status={provider.status} />
              </CardHeader>
              <CardContent>
                <dl className="divide-y">
                  <SummaryRow label="ID" value={<span className="font-mono text-xs">{provider.id}</span>} />
                  <SummaryRow label="Type" value={<span className="capitalize">{provider.type ?? '—'}</span>} />
                  <SummaryRow label="Version" value={provider.version} />
                  <SummaryRow label="Created" value={formatDateTime(provider.created_at)} />
                  <SummaryRow label="Updated" value={formatDateTime(provider.updated_at)} />
                </dl>
              </CardContent>
            </Card>

            {/* Lifecycle */}
            <Card>
              <CardHeader>
                <CardTitle>Record Actions</CardTitle>
                <CardDescription>Manage the lifecycle state of this provider record.</CardDescription>
              </CardHeader>
              <CardContent>
                <ProviderLifecycleActions
                  provider={provider}
                  acting={acting}
                  actionError={actionError}
                  onAction={runLifecycle}
                />
                <Button variant="outline" size="sm" className="mt-3" onClick={refresh} disabled={acting} aria-label="Refresh provider data">
                  <RefreshCw className="size-4" aria-hidden />
                  Refresh
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Child resources */}
          <Card>
            <CardHeader className="pb-0">
              <div role="tablist" aria-label="Provider sections" className="flex flex-wrap gap-1">
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
  resource: ProviderChildResource;
  data: ProviderIdentifier[] | ProviderCredential[] | ProviderNetwork[] | undefined;
}) {
  if (!data || data.length === 0) {
    return <EmptyState title="No records" description={`No ${resource} exist for this provider.`} className="py-8" />;
  }

  return (
    <div className="space-y-2">
      {resource === 'identifiers' &&
        (data as ProviderIdentifier[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Identity type" value={<span className="font-mono text-xs">{item.identity_type_id}</span>} />
            <ChildRow label="Value" value={item.value} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'credentials' &&
        (data as ProviderCredential[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Credential type" value={<span className="font-mono text-xs">{item.credential_type_id}</span>} />
            <ChildRow label="Number" value={item.number ?? '—'} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
      {resource === 'networks' &&
        (data as ProviderNetwork[]).map((item) => (
          <ChildTable key={item.id}>
            <ChildRow label="Network" value={<span className="font-mono text-xs">{item.network_id}</span>} />
            <ChildRow label="Status" value={<Badge variant="muted">{item.status}</Badge>} />
          </ChildTable>
        ))}
    </div>
  );
}
