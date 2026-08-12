import { Link } from 'react-router-dom';
import { ShieldCheck, GitCompareArrows, Crown, CheckCheck } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Stewardship management screen (Step 14).
 *
 * BACKEND REALITY: the backend stores stewardship-shaped data only as database
 * tables (`steward_assignment`, `quality_issue`, `remediation_task`,
 * `stewardship_log`), but exposes NO `/stewardship` endpoint of any kind — no
 * steward list/detail, no assignment/reassignment, no task queue, no
 * claim/unclaim/resolve/escalate, and no data-quality issue list/detail/creation
 * API (confirmed via `backend/routes/api.php`, the master-data Controllers/ and
 * Services/ directories, and the absence of any stewardship/quality repository).
 * `severity` is a raw string with no enumerated values, and actor/staff IDs are
 * cross-module IAM references with no lookup endpoint.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, priorities, or actions), this screen does NOT invent a stewardship
 * queue, task list, assignee picker, or resolve/escalate buttons. It explains the
 * real backend shape and points users to the existing related overviews.
 */
export function StewardshipPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Stewardship"
        description="Data-quality stewardship, assignments, and issue remediation"
        crumbs={[{ label: 'Master Data' }, { label: 'Stewardship' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={ShieldCheck}
            title="Stewardship management is not available from the API"
            description="The backend records stewardship data (steward assignments, data-quality issues, remediation tasks, and a stewardship audit log) but does not yet expose a stewardship list, queue, or action endpoint. Until that API ships, issues cannot be listed, assigned, or resolved here."
          />
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
            <Button asChild>
              <Link to="/duplicates">
                <GitCompareArrows className="size-4" aria-hidden />
                Duplicate Management
              </Link>
            </Button>
            <Button variant="outline" asChild>
              <Link to="/golden-records">
                <Crown className="size-4" aria-hidden />
                Golden Records
              </Link>
            </Button>
            <Button variant="outline" asChild>
              <Link to="/approvals">
                <CheckCheck className="size-4" aria-hidden />
                Approvals
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </PageContainer>
  );
}
