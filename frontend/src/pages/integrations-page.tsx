import { Link } from 'react-router-dom';
import { Plug, Search, Library } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';

/**
 * Integrations Management screen (Step 17).
 *
 * BACKEND REALITY: the backend stores integration-shaped data only as database
 * tables (`integration_endpoint`, `integration_map`, `mapping_field`), but
 * exposes NO `/integrations` endpoint of any kind — no integration list/detail,
 * endpoint management, mapping editor, webhook configuration, sync trigger or
 * history, test-connection, retry, enable/disable, or delete API (confirmed via
 * `backend/routes/api.php`, the Controllers/ and Services/ directories, and the
 * absence of any integration repository). `integration:manage` is a bare
 * permission constant that gates no route, so no integration authorization
 * contract is exposed.
 *
 * Per the project's data-safety rule (never fabricate endpoints, fields, rows,
 * statuses, credentials, webhooks, or actions), this screen does NOT invent
 * integration cards, connectors, endpoint tables, sync/test-connection/retry
 * buttons, webhook config, or a mapping editor. It explains the real backend
 * shape and points users to the existing surfaces that can still be used.
 */
export function IntegrationsPage() {
  return (
    <PageContainer>
      <PageHeader
        title="Integrations Management"
        description="Manage external system integrations and data exchange"
        crumbs={[{ label: 'Master Data' }, { label: 'Integrations Management' }]}
      />

      <Card>
        <CardContent className="pt-6">
          <EmptyState
            icon={Plug}
            title="Integration management is not currently exposed through the backend HTTP API."
            description="The backend records integration endpoints, maps, and mapping fields as database tables but does not yet expose an integration list, endpoint management, mapping editor, webhook configuration, or sync API. Until that API ships, external integrations cannot be managed through this interface."
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
