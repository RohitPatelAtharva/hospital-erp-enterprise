import { History } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

/**
 * Recent activity panel.
 *
 * No read-only activity/audit endpoint exists in the current API surface
 * (audit read APIs are a later phase). We show a clean empty state rather than
 * a fabricated feed.
 */
export function RecentActivity() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Recent Activity</CardTitle>
        <CardDescription>Latest changes and audit events.</CardDescription>
      </CardHeader>
      <CardContent className="flex flex-col items-center justify-center gap-2 py-10 text-center">
        <div className="bg-muted flex size-11 items-center justify-center rounded-full">
          <History className="text-muted-foreground size-5" aria-hidden />
        </div>
        <p className="text-sm font-medium">No activity feed yet</p>
        <p className="text-muted-foreground max-w-sm text-xs text-balance">
          Recent audit/activity is unavailable until the activity API is implemented.
        </p>
      </CardContent>
    </Card>
  );
}
