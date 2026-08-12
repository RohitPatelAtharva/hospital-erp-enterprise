import { useMemo, useState } from 'react';
import { Loader2, Search as SearchIcon, X } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/feedback/empty-state';
import { ErrorState } from '@/components/feedback/error-state';
import { Pagination } from '@/components/data-table/pagination';
import { SearchResultRow } from '@/components/search/search-result-row';
import { SearchSourceStatus } from '@/components/search/search-source-status';
import { useMasterSearch } from '@/hooks/use-master-search';
import type { SearchScope } from '@/lib/search-types';

const SCOPE_OPTIONS: { value: SearchScope; label: string }[] = [
  { value: 'all', label: 'All' },
  { value: 'patients', label: 'Patients' },
  { value: 'staff', label: 'Staff' },
  { value: 'providers', label: 'Providers' },
  { value: 'organizations', label: 'Organizations' },
  { value: 'master', label: 'Master Records' },
];

export function SearchPage() {
  const [inputValue, setInputValue] = useState('');
  const [scope, setScope] = useState<SearchScope>('all');
  const search = useMasterSearch({ initialQuery: '', initialScope: scope });

  // Bridge the local input state to the debounced hook query.
  function handleInputChange(next: string) {
    setInputValue(next);
    search.setQuery(next);
  }

  function handleClear() {
    setInputValue('');
    search.clear();
  }

  function handleScopeChange(next: SearchScope) {
    setScope(next);
    search.setScope(next);
  }

  const hasQuery = inputValue.trim().length > 0;
  const showSourceStrip = scope === 'all' && hasQuery;

  // Per-source "unavailable/error" messaging for All mode.
  const problemSources = useMemo(
    () => search.sources.filter((s) => s.status === 'error' || s.status === 'unavailable'),
    [search.sources],
  );

  return (
    <PageContainer>
      <PageHeader
        title="Master Data Search"
        description="Search patients, staff, providers, organizations, and master records"
        crumbs={[{ label: 'Master Data' }, { label: 'Search' }]}
      />

      <Card>
        <CardContent className="space-y-4 pt-6">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div className="relative flex-1">
              <SearchIcon className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" aria-hidden />
              <Input
                type="search"
                value={inputValue}
                onChange={(e) => handleInputChange(e.target.value)}
                placeholder="Search by name or reference…"
                aria-label="Search query"
                className="h-11 pl-9 pr-9"
                autoFocus
              />
              {hasQuery && (
                <button
                  type="button"
                  onClick={handleClear}
                  aria-label="Clear search"
                  className="text-muted-foreground hover:text-foreground absolute top-1/2 right-2 -translate-y-1/2 rounded-sm p-1"
                >
                  <X className="size-4" aria-hidden />
                </button>
              )}
            </div>
            <Button variant="outline" onClick={search.refresh} disabled={!hasQuery || search.loading}>
              <Loader2 className={`size-4 ${search.loading ? 'animate-spin' : ''}`} aria-hidden />
              Refresh
            </Button>
          </div>

          <div className="flex flex-wrap gap-2" role="group" aria-label="Search scope">
            {SCOPE_OPTIONS.map((option) => {
              const active = scope === option.value;
              return (
                <Button
                  key={option.value}
                  type="button"
                  size="sm"
                  variant={active ? 'default' : 'outline'}
                  aria-pressed={active}
                  onClick={() => handleScopeChange(option.value)}
                >
                  {option.label}
                </Button>
              );
            })}
          </div>
        </CardContent>
      </Card>

      {!hasQuery ? (
        <EmptyState
          icon={SearchIcon}
          title="Start searching"
          description="Enter a name or reference above and choose a scope to find master data records."
        />
      ) : search.loading ? (
        <Card>
          <CardContent className="flex items-center justify-center gap-2 py-16 text-sm text-muted-foreground">
            <Loader2 className="size-4 animate-spin" aria-hidden />
            Searching…
          </CardContent>
        </Card>
      ) : showSourceStrip && problemSources.length > 0 && search.results.length === 0 ? (
        <ErrorState
          title="Search unavailable"
          message={problemSources.map((s) => `${s.label}: ${s.error}`).join(' ')}
          onRetry={search.refresh}
        />
      ) : search.results.length === 0 ? (
        <EmptyState
          icon={SearchIcon}
          title="No results"
          description={`No matching records found in "${SCOPE_OPTIONS.find((o) => o.value === scope)?.label}" for your query.`}
        />
      ) : (
        <div className="space-y-4">
          {showSourceStrip && (
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <SearchSourceStatus sources={search.sources} />
              {problemSources.length > 0 && (
                <p className="text-destructive text-xs">
                  {problemSources.length} source{problemSources.length > 1 ? 's' : ''} unavailable — showing partial results.
                </p>
              )}
            </div>
          )}

          {search.error && !showSourceStrip && (
            <ErrorState title="Search failed" message={search.error} onRetry={search.refresh} />
          )}

          <Card>
            <CardContent className="divide-y p-0">
              {search.results.map((result) => (
                <SearchResultRow key={`${result.kind}-${result.id}`} result={result} />
              ))}
            </CardContent>
          </Card>

          {search.total > search.pageSize && (
            <Pagination
              page={search.page}
              pageSize={search.pageSize}
              total={search.total}
              onPageChange={search.setPage}
            />
          )}
        </div>
      )}
    </PageContainer>
  );
}
