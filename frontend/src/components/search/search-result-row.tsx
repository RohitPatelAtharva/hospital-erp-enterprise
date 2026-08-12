import { Link } from 'react-router-dom';
import { Building2, Stethoscope, UserRound, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { SearchResult } from '@/lib/search-types';
import { SearchStatusBadge } from './search-status-badge';

/**
 * Visual identity + detail route for each result kind.
 *
 * Patients, Staff, Providers, and Organizations all have real detail routes, so
 * the row links there. Master Records have NO detail route in the app, so the
 * row is intentionally rendered without a link (we do not invent one).
 */
const KIND_META: Record<
  SearchResult['kind'],
  { label: string; icon: LucideIcon; href: ((r: SearchResult) => string) | null }
> = {
  patient: { label: 'Patient', icon: UserRound, href: (r) => `/patients/${r.id}` },
  staff: { label: 'Staff', icon: Users, href: (r) => `/staff/${r.id}` },
  provider: { label: 'Provider', icon: Stethoscope, href: (r) => `/providers/${r.id}` },
  organization: { label: 'Organization', icon: Building2, href: (r) => `/organizations/${r.id}` },
  master: { label: 'Master Record', icon: Building2, href: null },
};

function subtitle(result: SearchResult): string {
  switch (result.kind) {
    case 'patient':
      return [result.dob, result.sex].filter(Boolean).join(' · ') || 'No details';
    case 'staff':
      return 'Staff member';
    case 'provider':
      return result.type ? `Provider · ${result.type}` : 'Provider';
    case 'organization':
      return 'Organization';
    case 'master':
      return result.external_ref ? `Ref ${result.external_ref}` : 'Master record';
  }
}

export function SearchResultRow({ result }: { result: SearchResult }) {
  const meta = KIND_META[result.kind];
  const Icon = meta.icon;
  const name =
    (result.kind === 'master' ? result.external_ref : result.name) ?? '(unnamed)';
  const href = meta.href?.(result);

  const inner = (
    <div className="flex items-center gap-3 py-3">
      <div className="bg-muted flex size-9 shrink-0 items-center justify-center rounded-md">
        <Icon className="text-muted-foreground size-4" aria-hidden />
      </div>
      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <span className="truncate font-medium">{name}</span>
          <span className="text-muted-foreground text-xs">{meta.label}</span>
        </div>
        <p className="text-muted-foreground truncate text-sm">{subtitle(result)}</p>
      </div>
      <SearchStatusBadge status={result.status} />
    </div>
  );

  if (href) {
    return (
      <Link
        to={href}
        className={cn(
          'block rounded-md px-3 transition-colors hover:bg-muted/60 focus-visible:bg-muted/60 focus-visible:outline-none',
        )}
      >
        {inner}
      </Link>
    );
  }

  // Master records: no detail route exists, so render as a non-interactive row.
  return <div className="px-3">{inner}</div>;
}
