import { Badge } from '@/components/ui/badge';
import type { StaffStatus } from '@/lib/staff-types';

const STATUS_PRESENTATION: Record<
  StaffStatus,
  { label: string; variant: 'success' | 'warning' | 'muted' }
> = {
  active: { label: 'Active', variant: 'success' },
  inactive: { label: 'Inactive', variant: 'warning' },
  archived: { label: 'Archived', variant: 'muted' },
};

export function StaffStatusBadge({ status }: { status: StaffStatus }) {
  const presentation = STATUS_PRESENTATION[status] ?? { label: status, variant: 'muted' as const };
  return (
    <Badge variant={presentation.variant}>
      <span className="capitalize">{presentation.label}</span>
    </Badge>
  );
}
