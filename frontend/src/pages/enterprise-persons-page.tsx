import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { RefreshCw } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { Pagination } from '@/components/data-table/pagination';
import { Badge } from '@/components/ui/badge';
import { useEnterprisePersons } from '@/hooks/use-enterprise-persons';
import { formatDate } from '@/lib/utils';
import type { EnterprisePerson } from '@/lib/enterprise-person-types';

export function EnterprisePersonsPage() {
  const [status, setStatus] = useState('');
  const { persons, loading, error, refresh, page, pageSize, total, setPage } = useEnterprisePersons({
    status,
  });

  // Reset to the first page whenever the free-text status filter changes.
  useEffect(() => {
    setPage(1);
  }, [status, setPage]);

  const columns: DataTableColumn<EnterprisePerson>[] = [
    {
      header: 'Name',
      cell: (p) => <span className="font-medium">{p.name ?? '—'}</span>,
    },
    {
      header: 'Status',
      cell: (p) => <Badge variant="muted">{p.status}</Badge>,
    },
    {
      header: 'Date of Birth',
      cell: (p) => formatDate(p.dob),
      hideBelow: 'sm',
    },
    {
      header: 'Version',
      cell: (p) => <span className="font-mono text-xs">{p.version}</span>,
      hideBelow: 'sm',
    },
    {
      header: 'Created',
      cell: (p) => formatDate(p.created_at),
      hideBelow: 'md',
    },
    {
      header: 'Actions',
      cell: (p) => (
        <Button variant="outline" size="sm" asChild>
          <Link to={`/enterprise-persons/${p.id}`}>View</Link>
        </Button>
      ),
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Enterprise Persons"
        description="Cross-role identity hub for enterprise persons referenced by patient, staff, and provider records"
        crumbs={[{ label: 'Master Data' }, { label: 'Enterprise Persons' }]}
      />

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1 sm:max-w-sm">
          <input
            type="text"
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            placeholder="Filter by status…"
            aria-label="Filter enterprise persons by status"
            className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
          />
        </div>
        <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh enterprise persons">
          <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} aria-hidden />
          Refresh
        </Button>
      </div>

      <DataTable
        columns={columns}
        data={persons}
        rowKey={(p) => p.id}
        loading={loading}
        error={error}
        emptyTitle="No enterprise persons found"
        emptyDescription={
          status.trim()
            ? `No enterprise persons match status "${status.trim()}".`
            : 'No enterprise person records exist yet.'
        }
        onRetry={refresh}
        footer={<Pagination page={page} pageSize={pageSize} total={total} onPageChange={setPage} />}
      />
    </PageContainer>
  );
}
