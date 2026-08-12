import type { LucideIcon } from 'lucide-react';
import {
  LayoutDashboard,
  Users,
  UserRound,
  Stethoscope,
  Building2,
  UserCog,
  BookMarked,
  ListTree,
  Search,
  Library,
  GitBranch,
  GitCompareArrows,
  Crown,
  Combine,
  CheckCheck,
  ShieldCheck,
  Upload,
  Download,
  Plug,
  ClipboardList,
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
      { title: 'Enterprise Persons', href: '/enterprise-persons', icon: UserCog, permission: 'masterdata:read' },
      { title: 'Reference Data', href: '/reference-data', icon: BookMarked, permission: 'reference:manage' },
      { title: 'Reference Values', href: '/reference-values', icon: ListTree, permission: 'reference:manage' },
      { title: 'Search', href: '/search', icon: Search, permission: 'masterdata:read' },
      { title: 'Master Records', href: '/master-records', icon: Library, permission: 'masterdata:read' },
      { title: 'Duplicate Management', href: '/duplicates', icon: GitCompareArrows, permission: 'masterdata:read' },
      { title: 'Golden Records', href: '/golden-records', icon: Crown, permission: 'masterdata:read' },
      { title: 'Merge Management', href: '/merges', icon: Combine, permission: 'masterdata:read' },
      { title: 'Approvals', href: '/approvals', icon: CheckCheck, permission: 'masterdata:read' },
      { title: 'Stewardship', href: '/stewardship', icon: ShieldCheck, permission: 'masterdata:read' },
      { title: 'Import Management', href: '/imports', icon: Upload, permission: 'masterdata:read' },
      { title: 'Export Management', href: '/exports', icon: Download, permission: 'masterdata:read' },
      { title: 'Integrations', href: '/integrations', icon: Plug, permission: 'masterdata:read' },
      { title: 'Audit Management', href: '/audit', icon: ClipboardList, permission: 'masterdata:read' },
      { title: 'Versions', href: '/versions', icon: GitBranch, permission: 'masterdata:read' },
    ],
  },
];

export function flattenNavItems(sections: NavSection[]): NavItem[] {
  return sections.flatMap((section) => section.items);
}
