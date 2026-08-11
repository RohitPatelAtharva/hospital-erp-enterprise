import { useState } from 'react';
import { Bell, Menu, Moon, Search, Sun } from 'lucide-react';
import { useTheme } from '@/components/theme/theme-provider';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';

export function TopHeader({ onMenuClick }: { onMenuClick: () => void }) {
  const { resolvedTheme, toggleTheme } = useTheme();
  const [searchOpen, setSearchOpen] = useState(false);

  return (
    <header className="flex h-14 shrink-0 items-center gap-3 border-b px-4">
      {/* Mobile nav trigger */}
      <Button variant="ghost" size="icon" className="md:hidden" onClick={onMenuClick} aria-label="Open navigation">
        <Menu className="size-5" />
      </Button>

      {/* Global search placeholder */}
      <div className="relative hidden max-w-md flex-1 sm:block">
        <Search className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" aria-hidden />
        <Input
          className="bg-muted/50 pl-9"
          placeholder="Search master data…"
          aria-label="Global search"
          onFocus={() => setSearchOpen(true)}
          onBlur={() => setSearchOpen(false)}
        />
        {searchOpen && (
          <div className="bg-popover text-popover-foreground absolute top-full z-30 mt-2 w-full rounded-md border p-3 shadow-md">
            <p className="text-muted-foreground text-sm">Global search placeholder — wire to /search in a later step.</p>
          </div>
        )}
      </div>

      <div className="flex-1" />

      {/* Theme toggle */}
      <Button variant="ghost" size="icon" onClick={toggleTheme} aria-label={`Switch to ${resolvedTheme === 'dark' ? 'light' : 'dark'} mode`}>
        {resolvedTheme === 'dark' ? <Sun className="size-5" /> : <Moon className="size-5" />}
      </Button>

      {/* Notifications placeholder */}
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button variant="ghost" size="icon" aria-label="Notifications">
            <Bell className="size-5" />
            <Badge variant="destructive" className="absolute -top-0.5 -right-0.5 size-4 rounded-full p-0 text-[10px]">
              3
            </Badge>
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="w-72">
          <DropdownMenuLabel>Notifications</DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuItem disabled>Duplicate candidates awaiting review</DropdownMenuItem>
          <DropdownMenuItem disabled>Merge approval requested</DropdownMenuItem>
          <DropdownMenuItem disabled>Import batch completed</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>

      {/* User/profile menu placeholder */}
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button variant="ghost" className="gap-2 px-2">
            <Avatar className="size-8">
              <AvatarFallback className="bg-primary text-primary-foreground text-xs">RS</AvatarFallback>
            </Avatar>
            <span className="hidden text-sm font-medium sm:inline">Registrar</span>
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="w-48">
          <DropdownMenuLabel className="text-muted-foreground font-normal">signed in as</DropdownMenuLabel>
          <DropdownMenuLabel>Registry Admin</DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuItem>Profile</DropdownMenuItem>
          <DropdownMenuItem>Settings</DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem variant="destructive">Sign out</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </header>
  );
}
