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
import { ProviderStatusBadge } from '@/components/providers/provider-status-badge';
import { useProviders } from '@/hooks/use-providers';
import { formatDate } from '@/lib/utils';
import type { Provider, ProviderStatus } from '@/lib/provider-types';

const FILTER_OPTIONS: { label: string; value: ProviderStatus | '' }[] = [
  { label: 'All statuses', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Archived', value: 'archived' },
];

export function ProvidersPage() {
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState<ProviderStatus | ''>('');
  const [debouncedQuery, setDebouncedQuery] = useState('');

  useEffect(() => {
    const handle = setTimeout(() => setDebouncedQuery(query.trim()), 300);
    return () => clearTimeout(handle);
  }, [query]);

  const { providers, loading, error, refresh, page, pageSize, total, setPage } = useProviders({
    status,
    query: debouncedQuery,
  });

  const columns: DataTableColumn<Provider>[] = [
    {
      header: 'Name',
      cell: (p) => <span className="font-medium">{p.name ?? '—'}</span>,
    },
    {
      header: 'Type',
      cell: (p) => <span className="capitalize">{p.type ?? '—'}</span>,
    },
    {
      header: 'Status',
      cell: (p) => <ProviderStatusBadge status={p.status} />,
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
          <Link to={`/providers/${p.id}`}>View</Link>
        </Button>
      ),
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Providers"
        description="Manage provider master data records"
        crumbs={[{ label: 'Master Data' }, { label: 'Providers' }]}
        actions={
          <Button asChild>
            <Link to="/providers/new">
              <Plus className="size-4" aria-hidden />
              Add Provider
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
            aria-label="Search providers by name"
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
                  setStatus(value as ProviderStatus | '');
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
          <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh providers">
            <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} aria-hidden />
            Refresh
          </Button>
        </div>
      </div>

      <DataTable
        columns={columns}
        data={providers}
        rowKey={(p) => p.id}
        loading={loading}
        error={error}
        emptyTitle="No providers found"
        emptyDescription={debouncedQuery ? `No providers match "${debouncedQuery}".` : 'No provider records exist yet.'}
        onRetry={refresh}
        footer={
          <Pagination page={page} pageSize={pageSize} total={total} onPageChange={setPage} />
        }
      />
    </PageContainer>
  );
}
