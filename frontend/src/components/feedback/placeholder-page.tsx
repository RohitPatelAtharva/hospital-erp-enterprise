import type { LucideIcon } from 'lucide-react';
import { Construction } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { PageHeader } from '@/components/layout/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

/**
 * Placeholder route page. Used for Master Data routes until their business
 * screens are implemented in a later step. Does not fabricate API data.
 */
export function PlaceholderPage({
  title,
  description,
  icon: Icon = Construction,
  badge = 'Foundation',
}: {
  title: string;
  description?: string;
  icon?: LucideIcon;
  badge?: string;
}) {
  return (
    <PageContainer>
      <PageHeader title={title} description={description} crumbs={[{ label: title }]} />
      <Card>
        <CardContent className="flex flex-col items-center justify-center gap-3 py-16 text-center">
          <div className="bg-muted flex size-12 items-center justify-center rounded-full">
            <Icon className="text-muted-foreground size-6" aria-hidden />
          </div>
          <h2 className="text-lg font-semibold">{title} screen</h2>
          <p className="text-muted-foreground max-w-md text-sm text-balance">
            This route is part of the Master Data module foundation. The business screen will be built in a later step.
          </p>
          <Badge variant="secondary">{badge}</Badge>
        </CardContent>
      </Card>
    </PageContainer>
  );
}
