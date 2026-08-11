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
import { PatientStatusBadge } from '@/components/patients/patient-status-badge';
import { usePatients } from '@/hooks/use-patients';
import { formatDate } from '@/lib/utils';
import type { Patient, PatientStatus } from '@/lib/patient-types';

const FILTER_OPTIONS: { label: string; value: PatientStatus | '' }[] = [
  { label: 'All statuses', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
  { label: 'Archived', value: 'archived' },
];

export function PatientsPage() {
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState<PatientStatus | ''>('');
  const [debouncedQuery, setDebouncedQuery] = useState('');

  useEffect(() => {
    const handle = setTimeout(() => setDebouncedQuery(query.trim()), 300);
    return () => clearTimeout(handle);
  }, [query]);

  const { patients, loading, error, refresh, page, pageSize, total, setPage } = usePatients({
    status,
    query: debouncedQuery,
  });

  const columns: DataTableColumn<Patient>[] = [
    {
      header: 'Name',
      cell: (p) => <span className="font-medium">{p.name ?? '—'}</span>,
    },
    {
      header: 'Sex',
      cell: (p) => <span className="capitalize">{p.sex ?? '—'}</span>,
    },
    {
      header: 'Date of Birth',
      cell: (p) => formatDate(p.dob),
      hideBelow: 'sm',
    },
    {
      header: 'Status',
      cell: (p) => <PatientStatusBadge status={p.status} />,
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
          <Link to={`/patients/${p.id}`}>View</Link>
        </Button>
      ),
    },
  ];

  return (
    <PageContainer>
      <PageHeader
        title="Patients"
        description="Manage patient master data records"
        crumbs={[{ label: 'Master Data' }, { label: 'Patients' }]}
        actions={
          <Button asChild>
            <Link to="/patients/new">
              <Plus className="size-4" aria-hidden />
              Register Patient
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
            aria-label="Search patients by name"
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
                  setStatus(value as PatientStatus | '');
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
          <Button variant="outline" size="sm" onClick={refresh} disabled={loading} aria-label="Refresh patients">
            <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} aria-hidden />
            Refresh
          </Button>
        </div>
      </div>

      <DataTable
        columns={columns}
        data={patients}
        rowKey={(p) => p.id}
        loading={loading}
        error={error}
        emptyTitle="No patients found"
        emptyDescription={debouncedQuery ? `No patients match "${debouncedQuery}".` : 'No patient records exist yet.'}
        onRetry={refresh}
        footer={
          <Pagination page={page} pageSize={pageSize} total={total} onPageChange={setPage} />
        }
      />
    </PageContainer>
  );
}
