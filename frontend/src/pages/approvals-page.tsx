import { Link } from 'react-router-dom';
import { CheckCheck, Search, GitCompareArrows, Crown } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Approvals management screen (Step 13).
 *
 * BACKEND REALITY: the backend stores approval-shaped data only as database
 * tables (`merge_approval` for merge events, `duplicate_review` for duplicate
 * candidates), but exposes NO `/approvals` endpoint of any kind — no list,
 * detail, pending, approve, reject, cancel, or history API (confirmed via
 * `backend/routes/api.php`, the master-data Controllers/ and Services/
 * directories, and the absence of any approval/workflow repository). There is no
 * approval status, no workflow state, and no approver lookup endpoint.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, or actions), this screen does NOT invent a pending-approvals list,
 * approve/reject buttons, an approval status badge, or a rejection-reason flow.
 * It explains the real backend shape and points users to the existing surfaces
 * that can still be used today (Merge / Golden Record / Duplicate Management
 * overviews and Master Data Search).
 */
export function ApprovalsPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Approvals"
        description="Review and approve pending master-data operations"
        crumbs={[{ label: 'Master Data' }, { label: 'Approvals' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={CheckCheck}
            title="Approval management is not available from the API"
            description="The backend records approval decisions only as internal audit tables (merge approvals and duplicate reviews). It does not expose an approvals list, a pending queue, or approve/reject endpoints. Until that API ships, approvals cannot be listed, reviewed, or actioned here."
          />
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
            <Button asChild>
              <Link to="/merges">
                <GitCompareArrows className="size-4" aria-hidden />
                Merge Management
              </Link>
            </Button>
            <Button variant="outline" asChild>
              <Link to="/golden-records">
                <Crown className="size-4" aria-hidden />
                Golden Records
              </Link>
            </Button>
            <Button variant="outline" asChild>
              <Link to="/search">
                <Search className="size-4" aria-hidden />
                Search Master Data
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </PageContainer>
  );
}
