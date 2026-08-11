import { CircleHelp } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

/**
 * Lifecycle aggregate panel.
 *
 * The current API exposes per-entity list endpoints only; there is no endpoint
 * that returns active/inactive/archived aggregates across master data. Per the
 * no-fabrication rule we show an explicit unavailable state rather than
 * inventing percentages.
 */
export function LifecycleOverview() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Lifecycle Overview</CardTitle>
        <CardDescription>Active, inactive, and archived record aggregates.</CardDescription>
      </CardHeader>
      <CardContent className="flex flex-col items-center justify-center gap-2 py-10 text-center">
        <div className="bg-muted flex size-11 items-center justify-center rounded-full">
          <CircleHelp className="text-muted-foreground size-5" aria-hidden />
        </div>
        <p className="text-sm font-medium">Lifecycle data unavailable</p>
        <p className="text-muted-foreground max-w-sm text-xs text-balance">
          No endpoint currently exposes lifecycle aggregates. This section will populate when the backend provides one.
        </p>
      </CardContent>
    </Card>
  );
}
