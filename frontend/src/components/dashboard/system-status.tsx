import { Loader2, CheckCircle2, TriangleAlert, XCircle } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { DashboardHealth } from '@/hooks/use-dashboard';

export function SystemStatus({ health }: { health: DashboardHealth }) {
  const presentation = {
    loading: { label: 'Checking…', icon: Loader2, badge: 'muted' as const, spin: true },
    connected: { label: 'API Connected', icon: CheckCircle2, badge: 'success' as const, spin: false },
    degraded: { label: 'API Degraded', icon: TriangleAlert, badge: 'warning' as const, spin: false },
    unavailable: { label: 'API Unavailable', icon: XCircle, badge: 'destructive' as const, spin: false },
  }[health.status];

  const Icon = presentation.icon;

  return (
    <Card>
      <CardHeader>
        <CardTitle>System Status</CardTitle>
        <CardDescription>Frontend–API availability.</CardDescription>
      </CardHeader>
      <CardContent className="flex items-center gap-3">
        <Badge variant={presentation.badge} className="gap-1.5 px-2.5 py-1 text-xs">
          <Icon className={`size-3.5 ${presentation.spin ? 'animate-spin' : ''}`} aria-hidden />
          {presentation.label}
        </Badge>
        {health.service && (
          <span className="text-muted-foreground text-xs">{health.service}</span>
        )}
      </CardContent>
    </Card>
  );
}
