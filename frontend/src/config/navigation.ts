import type { LucideIcon } from 'lucide-react';
import {
  LayoutDashboard,
  Users,
  UserRound,
  Stethoscope,
  Building2,
  BookMarked,
  Search,
  Library,
  GitBranch,
} from 'lucide-react';

export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon;
  /** Permission gate (mirrors 08-UI §3). Rendering is UI-only; the API is authoritative. */
  permission?: string;
  items?: NavItem[];
}

export interface NavSection {
  label: string;
  items: NavItem[];
}

export const masterDataNav: NavSection[] = [
  {
    label: 'Master Data',
    items: [
      { title: 'Dashboard', href: '/', icon: LayoutDashboard, permission: 'masterdata:read' },
      { title: 'Patients', href: '/patients', icon: Users, permission: 'patients:read' },
      { title: 'Staff', href: '/staff', icon: UserRound, permission: 'staff:read' },
      { title: 'Providers', href: '/providers', icon: Stethoscope, permission: 'providers:read' },
      { title: 'Organizations', href: '/organizations', icon: Building2, permission: 'organizations:read' },
      { title: 'Reference Data', href: '/reference-data', icon: BookMarked, permission: 'reference:manage' },
      { title: 'Search', href: '/search', icon: Search, permission: 'masterdata:read' },
      { title: 'Master Records', href: '/master-records', icon: Library, permission: 'masterdata:read' },
      { title: 'Versions', href: '/versions', icon: GitBranch, permission: 'masterdata:read' },
    ],
  },
];

export function flattenNavItems(sections: NavSection[]): NavItem[] {
  return sections.flatMap((section) => section.items);
}
