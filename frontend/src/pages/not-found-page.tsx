import { Link } from 'react-router-dom';
import { FileQuestion } from 'lucide-react';
import { PageContainer } from '@/components/layout/page-container';
import { Button } from '@/components/ui/button';

export function NotFoundPage() {
  return (
    <PageContainer>
      <div className="flex flex-col items-center justify-center gap-3 py-24 text-center">
        <div className="bg-muted flex size-14 items-center justify-center rounded-full">
          <FileQuestion className="text-muted-foreground size-7" aria-hidden />
        </div>
        <h1 className="text-2xl font-semibold">Page not found</h1>
        <p className="text-muted-foreground max-w-sm text-sm text-balance">
          The route you requested does not exist within the Master Data module.
        </p>
        <Button asChild className="mt-3">
          <Link to="/">Back to Dashboard</Link>
        </Button>
      </div>
    </PageContainer>
  );
}
