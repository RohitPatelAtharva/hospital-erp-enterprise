import { Link } from 'react-router-dom';
import { GitCompareArrows, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Duplicate Management registry screen (Step 10).
 *
 * BACKEND REALITY: the backend defines `duplicate_candidate`, `match_score`, and
 * `duplicate_review` tables, but exposes NO `/duplicates` list, detail, or
 * action endpoint (confirmed via `backend/routes/api.php`, the master-data
 * Controllers/ directory, and the Services/ directory). There is also no
 * enumerated status set and no review/confirm/dismiss/merge route.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * or actions), this screen does NOT invent a list, a status badge, or lifecycle
 * actions. It explains the real backend shape and points users to the existing
 * surfaces that can still be used today (Master Data Search and Master Record
 * version history).
 */
export function DuplicatesPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Duplicate Management"
        description="Review and resolve possible duplicate master records"
        crumbs={[{ label: 'Master Data' }, { label: 'Duplicate Management' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={GitCompareArrows}
            title="Duplicate candidate list is not available from the API"
            description="The backend stores duplicate-detection data (candidate pairs, match scores, and steward reviews) but does not yet expose a duplicates list, detail, or review endpoint. Until that API ships, candidate records cannot be listed, opened, or resolved here."
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
