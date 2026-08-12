import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, GitBranch } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { ErrorState } from '@/components/feedback/error-state';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { Pagination } from '@/components/data-table/pagination';
import { useVersions } from '@/hooks/use-versions';
import { formatDateTime } from '@/lib/utils';
import type { Version } from '@/lib/version-types';

function Uuid({ value }: { value: string | null }) {
  if (!value) return '—';
  return (
    <span className="font-mono text-xs" title={value}>
      {value.slice(0, 8)}…
    </span>
  );
}

export function MasterRecordDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { versions, loading, error, refresh, page, pageSize, total, setPage } = useVersions(id);

  if (!id) {
    return <ErrorState title="Missing Master Record ID" message="No master record identifier was provided." />;
  }

  const columns: DataTableColumn<Version>[] = [
    {
      header: 'Version #',
      cell: (v) => <span className="font-mono font-medium">{v.version_number}</span>,
    },
    {
      header: 'Actor',
      cell: (v) => <Uuid value={v.actor_id} />,
    },
    {
      header: 'Version ID',
      cell: (v) => <Uuid value={v.id} />,
    },
    {
      header: 'Created',
      cell: (v) => formatDateTime(v.created_at),
      hideBelow: 'md',
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Master Record"
        description={`Master Record ${id.slice(0, 8)}…`}
        crumbs={[
          { label: 'Master Data' },
          { label: 'Master Records', href: '/master-records' },
          { label: 'Version History' },
        ]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/master-records">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Master Records
            </Link>
          </Button>
        }
      />

      <Card>
        <CardHeader>
          <CardTitle className="font-mono text-sm">{id}</CardTitle>
          <CardDescription>Master Record identifier</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="text-muted-foreground flex flex-col gap-2 rounded-md border border-dashed p-4 text-sm">
            <div className="flex items-center gap-2 font-medium text-foreground">
              <GitBranch className="size-4" aria-hidden />
              Overview fields unavailable
            </div>
            <p>
              The backend does not expose a <code className="text-xs">GET /master-records/{id}</code>{' '}
              overview endpoint, so entity, status, and current-version fields cannot be shown here.
              Only version history is available for this record.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="flex-row items-center justify-between gap-4">
          <div className="space-y-1">
            <CardTitle>Version History</CardTitle>
            <CardDescription>Append-only point-in-time snapshots for this master record</CardDescription>
          </div>
          <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh versions">
            Refresh
          </Button>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="space-y-3" aria-busy="true">
              <Skeleton className="h-8 w-full" />
              {Array.from({ length: 4 }).map((_, i) => (
                <Skeleton key={i} className="h-6 w-full" />
              ))}
            </div>
          ) : error ? (
            <ErrorState title="Unable to load version history" message={error} onRetry={refresh} />
          ) : versions.length === 0 ? (
            <EmptyStateInline />
          ) : (
            <>
              <DataTable
                columns={columns}
                data={versions}
                rowKey={(v) => v.id}
                loading={false}
                emptyTitle="No versions found"
                emptyDescription="This master record has no version history yet."
              />
              {total > pageSize && (
                <div className="border-t px-4 py-3">
                  <Pagination
                    page={page}
                    pageSize={pageSize}
                    total={total}
                    onPageChange={setPage}
                  />
                </div>
              )}
            </>
          )}
        </CardContent>
      </Card>
    </PageContainer>
  );
}

function EmptyStateInline() {
  return (
    <div className="text-muted-foreground py-12 text-center text-sm">
      No versions found for this master record.
    </div>
  );
}
