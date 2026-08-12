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
import { useEnterprisePerson } from '@/hooks/use-enterprise-person';
import { formatDate, formatDateTime } from '@/lib/utils';

function SummaryRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between gap-4 border-b py-2.5 last:border-0">
      <dt className="text-muted-foreground text-sm">{label}</dt>
      <dd className="text-sm font-medium text-right">{value}</dd>
    </div>
  );
}

export function EnterprisePersonDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { person, loading, error, notFound, refresh } = useEnterprisePerson(id);

  if (!id) {
    return <ErrorState title="Missing enterprise person ID" message="No enterprise person identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Enterprise Person"
        description={person ? (person.name ?? 'Unnamed enterprise person') : 'Enterprise person record'}
        crumbs={[
          { label: 'Master Data' },
          { label: 'Enterprise Persons', href: '/enterprise-persons' },
          { label: 'Enterprise Person' },
        ]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/enterprise-persons">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Enterprise Persons
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
        <ErrorState title="Unable to load enterprise person" message={error} onRetry={refresh} />
      ) : notFound ? (
        <EmptyState
          title="Enterprise person not found"
          description="No enterprise person exists for this identifier. It may have been deleted or the ID is incorrect."
        />
      ) : person ? (
        <Card>
          <CardHeader className="flex-row items-start justify-between gap-4">
            <div className="space-y-1">
              <CardTitle>{person.name ?? 'Unnamed enterprise person'}</CardTitle>
              <CardDescription>Enterprise person master record</CardDescription>
            </div>
            <Badge variant="muted">{person.status}</Badge>
          </CardHeader>
          <CardContent>
            <dl className="divide-y">
              <SummaryRow label="ID" value={<span className="font-mono text-xs" title={person.id}>{person.id.slice(0, 8)}…</span>} />
              <SummaryRow label="Name" value={person.name ?? '—'} />
              <SummaryRow label="Date of birth" value={formatDate(person.dob)} />
              <SummaryRow label="Version" value={<span className="font-mono">{person.version}</span>} />
              <SummaryRow label="Created" value={formatDateTime(person.created_at)} />
              <SummaryRow label="Updated" value={formatDateTime(person.updated_at)} />
              <SummaryRow
                label="Deleted"
                value={person.deleted_at ? formatDateTime(person.deleted_at) : '—'}
              />
            </dl>
            <Button variant="outline" size="sm" className="mt-4" onClick={refresh} disabled={loading} aria-label="Refresh enterprise person data">
              <RefreshCw className="size-4" aria-hidden />
              Refresh
            </Button>
          </CardContent>
        </Card>
      ) : null}
    </PageContainer>
  );
}
