import { Link, useParams } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { ErrorState } from '@/components/feedback/error-state';
import { useVersionDetail } from '@/hooks/use-version-detail';
import { formatDateTime } from '@/lib/utils';
import type { VersionRef } from '@/lib/version-types';

function Uuid({ value }: { value: string | null }) {
  if (!value) return '—';
  return (
    <span className="font-mono text-xs" title={value}>
      {value.slice(0, 8)}…
    </span>
  );
}

function SummaryRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between gap-4 border-b py-2.5 last:border-0">
      <dt className="text-muted-foreground text-sm">{label}</dt>
      <dd className="text-sm font-medium text-right">{value}</dd>
    </div>
  );
}

function VersionRefBlock({ title, ref }: { title: string; ref: VersionRef | null }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-sm">{title}</CardTitle>
      </CardHeader>
      <CardContent>
        {ref ? (
          <dl className="divide-y">
            <SummaryRow label="Version #" value={<span className="font-mono">{ref.version_number}</span>} />
            <SummaryRow label="Actor" value={<Uuid value={ref.actor_id} />} />
            <SummaryRow label="Occurred at" value={formatDateTime(ref.occurred_at)} />
          </dl>
        ) : (
          <p className="text-muted-foreground text-sm">Not available.</p>
        )}
      </CardContent>
    </Card>
  );
}

export function MasterRecordVersionDetailPage() {
  const { id, vid } = useParams<{ id: string; vid: string }>();
  const { version, diff, loading, error, refresh } = useVersionDetail(id, vid);

  if (!id || !vid) {
    return (
      <ErrorState
        title="Missing identifiers"
        message="A Master Record ID and Version ID are both required to view a version."
      />
    );
  }

  return (
    <PageContainer>
      <PageHeader
        title="Version"
        description={version ? `Version #${version.version_number}` : 'Master record version'}
        crumbs={[
          { label: 'Master Data' },
          { label: 'Master Records', href: '/master-records' },
          { label: 'Version History', href: `/master-records/${id}` },
          { label: 'Version' },
        ]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to={`/master-records/${id}`}>
              <ArrowLeft className="size-4" aria-hidden />
              Back to Version History
            </Link>
          </Button>
        }
      />

      {loading ? (
        <div className="grid gap-4 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <Skeleton className="h-6 w-40" />
            </CardHeader>
            <CardContent className="space-y-4">
              <Skeleton className="h-8 w-full" />
              <Skeleton className="h-8 w-2/3" />
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <Skeleton className="h-6 w-40" />
            </CardHeader>
            <CardContent className="space-y-4">
              <Skeleton className="h-8 w-full" />
              <Skeleton className="h-8 w-2/3" />
            </CardContent>
          </Card>
        </div>
      ) : error ? (
        <ErrorState title="Unable to load version" message={error} onRetry={refresh} />
      ) : version ? (
        <div className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle className="font-mono text-sm">{version.id}</CardTitle>
              <CardDescription>Version record</CardDescription>
            </CardHeader>
            <CardContent>
              <dl className="divide-y">
                <SummaryRow label="Master Record" value={<Uuid value={version.master_record_id} />} />
                <SummaryRow label="Version #" value={<span className="font-mono">{version.version_number}</span>} />
                <SummaryRow label="Actor" value={<Uuid value={version.actor_id} />} />
                <SummaryRow label="Created" value={formatDateTime(version.created_at)} />
                <SummaryRow label="Updated" value={formatDateTime(version.updated_at)} />
              </dl>
            </CardContent>
          </Card>

          <div className="grid gap-4 lg:grid-cols-2">
            <VersionRefBlock title="Current" ref={diff?.current ?? null} />
            <VersionRefBlock title="Previous" ref={diff?.previous ?? null} />
          </div>

          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Change Summary</CardTitle>
              <CardDescription>Field-level changes are not available from the current version API.</CardDescription>
            </CardHeader>
            <CardContent>
              {!diff ? (
                <p className="text-muted-foreground text-sm">
                  The diff envelope could not be loaded for this version.
                </p>
              ) : diff.delta.type === 'initial' ? (
                <div className="space-y-2 text-sm">
                  <p className="font-medium text-foreground">Initial version</p>
                  <p className="text-muted-foreground">
                    This is the first recorded version for the master record. There is no preceding
                    version to compare against, so no field-level changes are available.
                  </p>
                </div>
              ) : (
                <div className="space-y-2 text-sm">
                  <p className="font-medium text-foreground">Revision</p>
                  <p className="text-muted-foreground">
                    A change occurred moving from version <span className="font-mono">{diff.delta.from}</span>{' '}
                    to version <span className="font-mono">{diff.delta.to}</span>. The version API stores
                    metadata only (version number, actor, timestamps) and does not retain field-level
                    snapshot payloads, so before/after field values cannot be displayed.
                  </p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      ) : null}
    </PageContainer>
  );
}
