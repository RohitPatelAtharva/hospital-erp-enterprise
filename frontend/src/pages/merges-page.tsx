import { Link } from 'react-router-dom';
import { Combine, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Merge / Unmerge management screen (Step 12).
 *
 * BACKEND REALITY: the backend defines `merge_event`, `merge_record`,
 * `merge_approval`, and `survivorship_decision` tables, but exposes NO `/merge`
 * or `/merges` endpoint of any kind — no create/merge, no unmerge, no preview,
 * no conflict-resolution, no survivor selection, and no merge-history read API
 * (confirmed via `backend/routes/api.php`, the master-data Controllers/
 * directory, and the Services/ directory). There is also no enumerated merge
 * status, and merge records carry no `status` column at all.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, or actions), this screen does NOT invent merge/unmerge buttons, a
 * merge status badge, a survivor-selection flow, or a merge-history table. It
 * explains the real backend shape and points users to the existing surfaces
 * that can still be used today (Master Data Search and Master Record version
 * history).
 */
export function MergesPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Merge Management"
        description="Merge duplicate master records into a canonical golden record"
        crumbs={[{ label: 'Master Data' }, { label: 'Merge Management' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={Combine}
            title="Merge operations are not available from the API"
            description="The backend stores merge events (which master records were combined, any approvals, and the survivorship rule applied) but does not yet expose a merge or unmerge endpoint. Until that API ships, source/survivor records cannot be selected and no merges or reversals can be performed here."
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
