import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, CircleSlash } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { ErrorState } from '@/components/feedback/error-state';
import { ReferenceStatusBadge } from '@/components/reference/reference-status-badge';
import { ReferenceLifecycleActions } from '@/components/reference/reference-lifecycle-actions';
import { useReferenceCategoryDetail } from '@/hooks/use-reference-category-detail';
import { formatDateTime } from '@/lib/utils';

function SummaryRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex items-center justify-between gap-4 border-b py-2.5 last:border-0">
      <dt className="text-muted-foreground text-sm">{label}</dt>
      <dd className="text-sm font-medium text-right">{value}</dd>
    </div>
  );
}

export function ReferenceCategoryDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { category, loading, error, refresh, acting, actionError, runLifecycle } = useReferenceCategoryDetail(id);

  if (!id) {
    return <ErrorState title="Missing category ID" message="No reference category identifier was provided." />;
  }

  return (
    <PageContainer>
      <PageHeader
        title="Reference Category"
        description={category ? `Category ${category.code}` : 'Reference category'}
        crumbs={[{ label: 'Master Data' }, { label: 'Reference Data', href: '/reference-data' }, { label: 'Category' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/reference-data">
              <ArrowLeft className="size-4" aria-hidden />
              Back to Reference Data
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
        <ErrorState title="Unable to load category" message={error} onRetry={refresh} />
      ) : category ? (
        <div className="grid gap-4 lg:grid-cols-3">
          {/* Summary */}
          <Card className="lg:col-span-2">
            <CardHeader className="flex-row items-start justify-between gap-4">
              <div className="space-y-1">
                <CardTitle className="font-mono">{category.code}</CardTitle>
                <CardDescription>Reference category</CardDescription>
              </div>
              <ReferenceStatusBadge status={category.status} />
            </CardHeader>
            <CardContent>
              <dl className="divide-y">
                <SummaryRow label="ID" value={<span className="font-mono text-xs">{category.id}</span>} />
                <SummaryRow label="Code" value={<span className="font-mono">{category.code}</span>} />
                <SummaryRow label="Version" value={category.version} />
                <SummaryRow label="Created" value={formatDateTime(category.created_at)} />
                <SummaryRow label="Updated" value={formatDateTime(category.updated_at)} />
              </dl>
            </CardContent>
          </Card>

          {/* Lifecycle */}
          <Card>
            <CardHeader>
              <CardTitle>Record Actions</CardTitle>
              <CardDescription>Manage the lifecycle state of this category.</CardDescription>
            </CardHeader>
            <CardContent>
              <ReferenceLifecycleActions
                status={category.status}
                acting={acting}
                actionError={actionError}
                onAction={runLifecycle}
              />
            </CardContent>
          </Card>

          {/* Values - unavailable (no category-scoped values endpoint) */}
          <Card className="lg:col-span-3">
            <CardHeader>
              <CardTitle>Reference Values</CardTitle>
              <CardDescription>Values belonging to this category</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="text-muted-foreground flex flex-col items-start gap-2 rounded-md border border-dashed p-6 text-sm">
                <div className="flex items-center gap-2 font-medium text-foreground">
                  <CircleSlash className="size-4" aria-hidden />
                  Values unavailable here
                </div>
                <p>
                  The API does not expose a category-scoped values endpoint
                  (no <code className="text-xs">GET /reference-categories/{'{id}'}/values</code>),
                  so this category&apos;s values cannot be listed on this screen.
                  Manage values on the{' '}
                  <Link to="/reference-values" className="text-primary font-medium underline underline-offset-4 hover:no-underline">
                    Reference Values
                  </Link>{' '}
                  screen.
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      ) : null}
    </PageContainer>
  );
}
