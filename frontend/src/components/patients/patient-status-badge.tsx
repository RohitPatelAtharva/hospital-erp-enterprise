import { Badge } from '@/components/ui/badge';
import type { PatientStatus } from '@/lib/patient-types';

const STATUS_PRESENTATION: Record<
  PatientStatus,
  { label: string; variant: 'success' | 'warning' | 'muted' }
> = {
  active: { label: 'Active', variant: 'success' },
  inactive: { label: 'Inactive', variant: 'warning' },
  archived: { label: 'Archived', variant: 'muted' },
};

export function PatientStatusBadge({ status }: { status: PatientStatus }) {
  const presentation = STATUS_PRESENTATION[status] ?? { label: status, variant: 'muted' as const };
  return (
    <Badge variant={presentation.variant}>
      <span className="capitalize">{presentation.label}</span>
    </Badge>
  );
}
