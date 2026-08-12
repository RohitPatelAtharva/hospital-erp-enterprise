import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, RefreshCw, Filter, ListTree } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { Pagination } from '@/components/data-table/pagination';
import { ReferenceStatusBadge } from '@/components/reference/reference-status-badge';
import { useReferenceValues } from '@/hooks/use-reference-values';
import { formatDate } from '@/lib/utils';
import type { ReferenceStatus, ReferenceValue } from '@/lib/reference-data-types';

const FILTER_OPTIONS: { label: string; value: ReferenceStatus | '' }[] = [
  { label: 'All statuses', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
];

export function ReferenceValuesPage() {
  const [status, setStatus] = useState<ReferenceStatus | ''>('');

  const { values, loading, error, refresh, page, pageSize, total, setPage } = useReferenceValues({ status });

  const columns: DataTableColumn<ReferenceValue>[] = [
    {
      header: 'Code',
      cell: (v) => <span className="font-mono font-medium">{v.code}</span>,
    },
    {
      header: 'Category',
      cell: (v) => (
        <span className="text-muted-foreground font-mono text-xs" title={v.reference_category_id}>
          {v.reference_category_id.slice(0, 8)}…
        </span>
      ),
      hideBelow: 'md',
    },
    {
      header: 'Status',
      cell: (v) => <ReferenceStatusBadge status={v.status} />,
    },
    {
      header: 'Created',
      cell: (v) => formatDate(v.created_at),
      hideBelow: 'md',
    },
    {
      header: 'Actions',
      cell: (v) => (
        <Button variant="outline" size="sm" asChild>
          <Link to={`/reference-values/${v.id}`}>View</Link>
        </Button>
      ),
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Reference Values"
        description="Manage canonical reference values grouped under reference categories"
        crumbs={[{ label: 'Master Data' }, { label: 'Reference Data', href: '/reference-data' }, { label: 'Values' }]}
        actions={
          <Button asChild>
            <Link to="/reference-values/new">
              <Plus className="size-4" aria-hidden />
              Add Value
            </Link>
          </Button>
        }
      />

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="text-muted-foreground flex items-center gap-2 text-sm">
          <ListTree className="size-4" aria-hidden />
          Reference values
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
                  setStatus(value as ReferenceStatus | '');
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
          <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh reference values">
            <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} aria-hidden />
            Refresh
          </Button>
        </div>
      </div>

      <DataTable
        columns={columns}
        data={values}
        rowKey={(v) => v.id}
        loading={loading}
        error={error}
        emptyTitle="No reference values found"
        emptyDescription="No reference value records exist yet."
        onRetry={refresh}
        footer={<Pagination page={page} pageSize={pageSize} total={total} onPageChange={setPage} />}
      />
    </PageContainer>
  );
}
