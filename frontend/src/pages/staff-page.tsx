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
import { StaffStatusBadge } from '@/components/staff/staff-status-badge';
import { useStaff } from '@/hooks/use-staff';
import { formatDate } from '@/lib/utils';
import type { Staff, StaffStatus } from '@/lib/staff-types';

const FILTER_OPTIONS: { label: string; value: StaffStatus | '' }[] = [
  { label: 'All statuses', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Archived', value: 'archived' },
];

export function StaffPage() {
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState<StaffStatus | ''>('');
  const [debouncedQuery, setDebouncedQuery] = useState('');

  useEffect(() => {
    const handle = setTimeout(() => setDebouncedQuery(query.trim()), 300);
    return () => clearTimeout(handle);
  }, [query]);

  const { staff, loading, error, refresh, page, pageSize, total, setPage } = useStaff({
    status,
    query: debouncedQuery,
  });

  const columns: DataTableColumn<Staff>[] = [
    {
      header: 'Name',
      cell: (s) => <span className="font-medium">{s.name ?? '—'}</span>,
    },
    {
      header: 'Status',
      cell: (s) => <StaffStatusBadge status={s.status} />,
    },
    {
      header: 'Created',
      cell: (s) => formatDate(s.created_at),
      hideBelow: 'md',
    },
    {
      header: 'Actions',
      cell: (s) => (
        <Button variant="outline" size="sm" asChild>
          <Link to={`/staff/${s.id}`}>View</Link>
        </Button>
      ),
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Staff"
        description="Manage staff master data records"
        crumbs={[{ label: 'Master Data' }, { label: 'Staff' }]}
        actions={
          <Button asChild>
            <Link to="/staff/new">
              <Plus className="size-4" aria-hidden />
              Add Staff
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
            aria-label="Search staff by name"
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
                  setStatus(value as StaffStatus | '');
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
          <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh staff">
            <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} aria-hidden />
            Refresh
          </Button>
        </div>
      </div>

      <DataTable
        columns={columns}
        data={staff}
        rowKey={(s) => s.id}
        loading={loading}
        error={error}
        emptyTitle="No staff found"
        emptyDescription={debouncedQuery ? `No staff match "${debouncedQuery}".` : 'No staff records exist yet.'}
        onRetry={refresh}
        footer={
          <Pagination page={page} pageSize={pageSize} total={total} onPageChange={setPage} />
        }
      />
    </PageContainer>
  );
}
