import { Link } from 'react-router-dom';
import { Download, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Export Management screen (Step 16).
 *
 * BACKEND REALITY: the backend stores export-shaped data only as database
 * tables (`export_batch`, `export_queue_item`, `export_recipient`), but exposes
 * NO `/exports` endpoint of any kind — no export list/detail, no batch creation,
 * no queue management, no enqueue/run, and no download or cancel API (confirmed
 * via `backend/routes/api.php`, the master-data Controllers/ and Services/
 * directories, and the absence of any export repository). `status` is a raw
 * string with no enumerated values, and actor/recipient references are
 * cross-module IAM UUIDs with no lookup endpoint.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, or actions), this screen does NOT invent an export queue, batch
 * list, download buttons, or enqueue/cancel actions. It explains the real
 * backend shape and points users to the existing surfaces that can still be used.
 */
export function ExportsPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Export Management"
        description="Export master data to external formats"
        crumbs={[{ label: 'Master Data' }, { label: 'Export Management' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={Download}
            title="Export management is not currently exposed through the backend HTTP API."
            description="The backend records export batches, queue items, and recipients as database tables but does not yet expose an export list, batch creation, queue, run, or download endpoint. Until that API ships, master data cannot be exported through this interface."
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
