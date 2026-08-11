import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

/**
 * Scrollable content region for the current route. Applies consistent
 * horizontal + vertical padding and a max-width for wide enterprise tables.
 */
export function PageContainer({ children, className }: { children: ReactNode; className?: string }) {
  return (
    <main className={cn('flex-1 overflow-y-auto', className)}>
      <div className="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">{children}</div>
    </main>
  );
}
