import { Badge } from '@/components/ui/badge';

/**
 * Renders an entity status badge using the shared active/inactive/archived
 * presentation. The search API returns `status` as a string; we fall back to a
 * muted "unknown" badge for any value outside the canonical set rather than
 * fabricating a label.
 */
const STATUS_PRESENTATION: Record<
  string,
  { label: string; variant: 'success' | 'warning' | 'muted' }
> = {
  active: { label: 'Active', variant: 'success' },
  inactive: { label: 'Inactive', variant: 'warning' },
  archived: { label: 'Archived', variant: 'muted' },
};

export function SearchStatusBadge({ status }: { status: string }) {
  const presentation =
    STATUS_PRESENTATION[status] ?? { label: status || 'Unknown', variant: 'muted' as const };
  return (
    <Badge variant={presentation.variant}>
      <span className="capitalize">{presentation.label}</span>
    </Badge>
  );
}
