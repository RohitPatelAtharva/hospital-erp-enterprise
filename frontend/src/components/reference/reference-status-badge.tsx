import { Badge } from '@/components/ui/badge';
import type { ReferenceStatus } from '@/lib/reference-data-types';

const STATUS_PRESENTATION: Record<ReferenceStatus, { label: string; variant: 'success' | 'warning' }> = {
  active: { label: 'Active', variant: 'success' },
  inactive: { label: 'Inactive', variant: 'warning' },
};

export function ReferenceStatusBadge({ status }: { status: ReferenceStatus }) {
  const presentation = STATUS_PRESENTATION[status] ?? { label: status, variant: 'warning' as const };
  return (
    <Badge variant={presentation.variant}>
      <span className="capitalize">{presentation.label}</span>
    </Badge>
  );
}
