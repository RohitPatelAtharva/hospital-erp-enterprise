import { Link } from 'react-router-dom';
import { ClipboardList, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Audit Management screen (Step 18).
 *
 * BACKEND REALITY: the backend records audit-shaped data only as database
 * tables (`audit_action`, `audit_actor`, `audit_reference`, `audit_retention`,
 * plus `version_audit` / `golden_record_audit` child tables) and has an internal
 * audit recorder (`App\Audit\*`), but exposes NO `/audit` endpoint of any kind —
 * no event list/detail, no actor/entity filter, no before/after diff, no export
 * or retention/purge API (confirmed via `backend/routes/api.php`, the
 * Controllers/ and Services/ directories, and the absence of any audit
 * repository). `audit:read` is a real permission constant but gates NO HTTP
 * route, so no audit authorization contract is exposed through the API.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, event
 * types, actor names, counts, timelines, filters, or actions), this screen does
 * NOT invent an audit timeline, event table, filters, actor selector,
 * before/after diff, export, or retention/purge controls. It explains the real
 * backend shape and points users to the existing surfaces that can still be used.
 */
export function AuditPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Audit Management"
        description="Review system activity and audit history"
        crumbs={[{ label: 'Master Data' }, { label: 'Audit Management' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={ClipboardList}
            title="Audit management is not currently exposed through the backend HTTP API."
            description="The backend records audit actions, actors, and references as database tables and writes events through an internal audit recorder, but does not yet expose an audit list, event detail, filter, export, or retention endpoint. Until that API ships, audit history cannot be reviewed through this interface."
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
        </CardContent>
      </Card>
    </PageContainer>
  );
}
