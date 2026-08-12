import { Link } from 'react-router-dom';
import { Upload, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Import Management screen (Step 15).
 *
 * BACKEND REALITY: the backend defines `import_batch`, `import_staging_row`, and
 * `import_validation` tables, but exposes NO `/imports` endpoint of any kind — no
 * list, detail, upload, preview, validate, commit, or rollback API (confirmed via
 * `backend/routes/api.php`, the master-data Controllers/ and Services/
 * directories, and the absence of any import repository). There is also no
 * enumerated status set (`status` is a bare string) and no file-upload behavior.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, or actions), this screen does NOT invent an upload button, import
 * jobs, progress, validation/preview tables, or commit/rollback actions. It
 * explains the real backend shape and points users to the existing surfaces that
 * can still be used today (Master Data Search and Master Record version history).
 */
export function ImportsPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Import Management"
        description="Import master data from external sources"
        crumbs={[{ label: 'Master Data' }, { label: 'Import Management' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={Upload}
            title="Import management is not currently available through the backend API."
            description="The backend stores import batches and staging rows (with validation records) but does not yet expose an import list, upload, preview, validation, or commit endpoint. Until that API ships, master data cannot be imported through this interface."
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
