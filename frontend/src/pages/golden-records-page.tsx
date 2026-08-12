import { Link } from 'react-router-dom';
import { Crown, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Golden Records registry screen (Step 11).
 *
 * BACKEND REALITY: the backend defines a `golden_record` table (with related
 * `golden_record_link`, `golden_record_source`, `golden_record_audit`,
 * `merge_event`, and `merge_approval` tables), but exposes NO `/golden-records`
 * list, detail, create, update, lifecycle, or merge endpoint (confirmed via
 * `backend/routes/api.php`, the master-data Controllers/ directory, and the
 * Services/ directory). There is also no enumerated status set and no
 * create/merge/unmerge route.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, or actions), this screen does NOT invent a list, a status badge, a
 * creation form, or lifecycle actions. It explains the real backend shape and
 * points users to the existing surfaces that can still be used today (Master
 * Data Search and Master Record version history).
 */
export function GoldenRecordsPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Golden Records"
        description="Canonical golden records derived from mastered entities"
        crumbs={[{ label: 'Master Data' }, { label: 'Golden Records' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={Crown}
            title="Golden record list is not available from the API"
            description="The backend stores golden-record data (a canonical record plus its linked source records, audit trail, and merge events) but does not yet expose a golden-records list, detail, or creation endpoint. Until that API ships, golden records cannot be listed, opened, or created here."
          />
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
            <Button asChild>
              <Link to="/search">
                <Search className="size-4" aria-hidden />
                Search Master Data
              </Link>
            </Button>
            <Button variant="outline" asChild>
              <Link to="/master-records">
                <Library className="size-4" aria-hidden />
                Master Record Version History
              </Link>
            </Button>
          </div>
          <p className="text-muted-foreground mt-4 text-center text-xs">
            Tip: if you have a Master Record ID, open{' '}
            <code className="rounded bg-muted px-1 py-0.5 font-mono">/master-records/&lt;id&gt;</code> to
            inspect its version history.
          </p>
        </CardContent>
      </Card>
    </PageContainer>
  );
}
