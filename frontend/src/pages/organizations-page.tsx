import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, RefreshCw, Search, Filter } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { Pagination } from '@/components/data-table/pagination';
import { OrganizationStatusBadge } from '@/components/organizations/organization-status-badge';
import { useOrganizations } from '@/hooks/use-organizations';
import { formatDate } from '@/lib/utils';
import type { Organization, OrganizationStatus } from '@/lib/organization-types';

const FILTER_OPTIONS: { label: string; value: OrganizationStatus | '' }[] = [
  { label: 'All statuses', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Archived', value: 'archived' },
];

export function OrganizationsPage() {
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState<OrganizationStatus | ''>('');
  const [debouncedQuery, setDebouncedQuery] = useState('');

  useEffect(() => {
    const handle = setTimeout(() => setDebouncedQuery(query.trim()), 300);
    return () => clearTimeout(handle);
  }, [query]);

  const { organizations, loading, error, refresh, page, pageSize, total, setPage } = useOrganizations({
    status,
    query: debouncedQuery,
  });

  const columns: DataTableColumn<Organization>[] = [
    {
      header: 'Name',
      cell: (o) => <span className="font-medium">{o.name ?? '—'}</span>,
    },
    {
      header: 'Type',
      cell: (o) =>
        o.organization_type_id ? (
          <span className="text-muted-foreground font-mono text-xs" title={o.organization_type_id}>
            {o.organization_type_id.slice(0, 8)}…
          </span>
        ) : (
          '—'
        ),
      hideBelow: 'sm',
    },
    {
      header: 'Status',
      cell: (o) => <OrganizationStatusBadge status={o.status} />,
    },
    {
      header: 'Created',
      cell: (o) => formatDate(o.created_at),
      hideBelow: 'md',
    },
    {
      header: 'Actions',
      cell: (o) => (
        <Button variant="outline" size="sm" asChild>
          <Link to={`/organizations/${o.id}`}>View</Link>
        </Button>
      ),
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Organizations"
        description="Manage organization master data records"
        crumbs={[{ label: 'Master Data' }, { label: 'Organizations' }]}
        actions={
          <Button asChild>
            <Link to="/organizations/new">
              <Plus className="size-4" aria-hidden />
              Add Organization
            </Link>
          </Button>
        }
      />

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1 sm:max-w-sm">
          <Search className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" aria-hidden />
          <Input
            className="pl-9"
            placeholder="Search by name…"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            aria-label="Search organizations by name"
          />
        </div>
        <div className="flex items-center gap-2">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="sm">
                <Filter className="size-4" aria-hidden />
                Status
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
              <DropdownMenuRadioGroup
                value={status}
                onValueChange={(value) => {
                  setStatus(value as OrganizationStatus | '');
                  setPage(1);
                }}
              >
                {FILTER_OPTIONS.map((option) => (
                  <DropdownMenuRadioItem key={option.value} value={option.value}>
                    {option.label}
                  </DropdownMenuRadioItem>
                ))}
              </DropdownMenuRadioGroup>
            </DropdownMenuContent>
          </DropdownMenu>
          <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh organizations">
            <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} aria-hidden />
            Refresh
          </Button>
        </div>
      </div>

      <DataTable
        columns={columns}
        data={organizations}
        rowKey={(o) => o.id}
        loading={loading}
        error={error}
        emptyTitle="No organizations found"
        emptyDescription={debouncedQuery ? `No organizations match "${debouncedQuery}".` : 'No organization records exist yet.'}
        onRetry={refresh}
        footer={
          <Pagination page={page} pageSize={pageSize} total={total} onPageChange={setPage} />
        }
      />
    </PageContainer>
  );
}
