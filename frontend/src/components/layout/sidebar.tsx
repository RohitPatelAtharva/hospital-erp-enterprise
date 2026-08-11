import { NavLink } from 'react-router-dom';
import { Activity } from 'lucide-react';
import { cn } from '@/lib/utils';
import { masterDataNav } from '@/config/navigation';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';

export function Sidebar({ className }: { className?: string }) {
  return (
    <aside className={cn('flex h-full flex-col', className)}>
      {/* Brand */}
      <div className="flex h-14 shrink-0 items-center gap-2 border-b px-4">
        <div className="bg-primary flex size-8 items-center justify-center rounded-md">
          <Activity className="text-primary-foreground size-5" />
        </div>
        <div className="flex flex-col leading-tight">
          <span className="text-sm font-semibold">Hospital ERP</span>
          <span className="text-muted-foreground text-xs">Master Data</span>
        </div>
      </div>

      <ScrollArea className="flex-1">
        <nav className="flex flex-col gap-4 p-3" aria-label="Main navigation">
          {masterDataNav.map((section) => (
            <div key={section.label} className="space-y-1">
              <div className="text-muted-foreground px-3 pt-2 text-xs font-medium uppercase tracking-wider">
                {section.label}
              </div>
              {section.items.map((item) => {
                const Icon = item.icon;
                return (
                  <NavLink
                    key={item.href}
                    to={item.href}
                    end={item.href === '/'}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                        isActive && 'bg-accent text-accent-foreground',
                      )
                    }
                  >
                    {Icon && <Icon className="size-4 shrink-0" aria-hidden />}
                    <span className="truncate">{item.title}</span>
                  </NavLink>
                );
              })}
            </div>
          ))}
        </nav>
      </ScrollArea>

      <Separator />
      <div className="text-muted-foreground p-4 text-xs">
        <span>v0.1.0</span>
      </div>
    </aside>
  );
}
