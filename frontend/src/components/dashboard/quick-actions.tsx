import { Users, UserRound, Stethoscope, Building2, Search } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const ACTIONS = [
  { label: 'Register Patient', href: '/patients', icon: Users },
  { label: 'Add Staff', href: '/staff', icon: UserRound },
  { label: 'Add Provider', href: '/providers', icon: Stethoscope },
  { label: 'Add Organization', href: '/organizations', icon: Building2 },
  { label: 'Search Master Data', href: '/search', icon: Search },
];

export function QuickActions() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Quick Actions</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
          {ACTIONS.map(({ label, href, icon: Icon }) => (
            <Link
              key={href}
              to={href}
              className="flex items-center gap-3 rounded-md border px-3 py-2.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:ring-ring focus-visible:ring-2 focus-visible:outline-none"
            >
              <Icon className="text-muted-foreground size-4 shrink-0" aria-hidden />
              {label}
            </Link>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
