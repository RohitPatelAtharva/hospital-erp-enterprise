import { Link } from 'react-router-dom';
import { Library, Search } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Master Records registry screen.
 *
 * IMPORTANT (Step 9 / 10-API §18): the backend exposes NO `GET /master-records`
 * list endpoint and NO `GET /master-records/{id}` overview endpoint. We must
 * not fabricate rows or invent an endpoint. This screen therefore explains the
 * real backend shape and points users to the surfaces that actually exist:
 * the Master Data Search (which does return master records) and the version
 * history that can be opened once a valid Master Record ID is known.
 */
export function MasterRecordsPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Master Records"
        description="Canonical master records and their version history"
        crumbs={[{ label: 'Master Data' }, { label: 'Master Records' }]}
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link to="/search">
              <Search className="size-4" aria-hidden />
              Search Master Data
            </Link>
          </Button>
        }
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={Library}
            title="Master Record list is not available from the API"
            description="The backend does not expose a master-records list or overview endpoint. You can still search master records by reference, and open version history for a known Master Record ID."
          />
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
            <Button asChild>
              <Link to="/search">
                <Search className="size-4" aria-hidden />
                Search Master Data
              </Link>
            </Button>
          </div>
          <p className="text-muted-foreground mt-4 text-center text-xs">
            Tip: if you have a Master Record ID, open{' '}
            <code className="rounded bg-muted px-1 py-0.5 font-mono">/master-records/&lt;id&gt;</code> to
            view its version history.
          </p>
        </CardContent>
      </Card>
    </PageContainer>
  );
}
