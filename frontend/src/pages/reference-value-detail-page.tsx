import { Link, useParams } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { ErrorState } from '@/components/feedback/error-state';
import { ReferenceStatusBadge } from '@/components/reference/reference-status-badge';
import { ReferenceLifecycleActions } from '@/components/reference/reference-lifecycle-actions';
import { useReferenceValueDetail } from '@/hooks/use-reference-value-detail';
import { formatDateTime } from '@/lib/utils';

function SummaryRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between gap-4 border-b py-2.5 last:border-0">
      <dt className="text-muted-foreground text-sm">{label}</dt>
      <dd className="text-sm font-medium text-right">{value}</dd>
    </div>
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

export function ReferenceValueDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { value, loading, error, refresh, acting, actionError, runLifecycle } = useReferenceValueDetail(id);

  if (!id) {
    return <ErrorState title="Missing value ID" message="No reference value identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Reference Value"
        description={value ? `Value ${value.code}` : 'Reference value'}
        crumbs={[{ label: 'Master Data' }, { label: 'Reference Values', href: '/reference-values' }, { label: 'Value' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/reference-values">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Reference Values
            </Link>
          </Button>
        }
      />

      {loading ? (
        <Card>
          <CardHeader>
            <Skeleton className="h-6 w-40" />
          </CardHeader>
          <CardContent className="space-y-4">
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-2/3" />
          </CardContent>
        </Card>
      ) : error ? (
        <ErrorState title="Unable to load value" message={error} onRetry={refresh} />
      ) : value ? (
        <div className="grid gap-4 lg:grid-cols-3">
          <Card className="lg:col-span-2">
            <CardHeader className="flex-row items-start justify-between gap-4">
              <div className="space-y-1">
                <CardTitle className="font-mono">{value.code}</CardTitle>
                <CardDescription>Reference value</CardDescription>
              </div>
              <ReferenceStatusBadge status={value.status} />
            </CardHeader>
            <CardContent>
              <dl className="divide-y">
                <SummaryRow label="ID" value={<span className="font-mono text-xs">{value.id}</span>} />
                <SummaryRow label="Code" value={<span className="font-mono">{value.code}</span>} />
                <SummaryRow label="Category" value={<Uuid value={value.reference_category_id} />} />
                <SummaryRow label="Version" value={<Uuid value={value.reference_version_id} />} />
                <SummaryRow label="Version #" value={value.version} />
                <SummaryRow label="Created" value={formatDateTime(value.created_at)} />
                <SummaryRow label="Updated" value={formatDateTime(value.updated_at)} />
              </dl>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Record Actions</CardTitle>
              <CardDescription>Manage the lifecycle state of this reference value.</CardDescription>
            </CardHeader>
            <CardContent>
              <ReferenceLifecycleActions
                status={value.status}
                acting={acting}
                actionError={actionError}
                onAction={runLifecycle}
              />
            </CardContent>
          </Card>
        </div>
      ) : null}
    </PageContainer>
  );
}
