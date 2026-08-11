import { useState } from 'react';
import { Archive, ArchiveRestore, Ban, Loader2, Power, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ConfirmationDialog } from '@/components/feedback/confirmation-dialog';
import type { ProviderLifecycleAction } from '@/hooks/use-provider-detail';
import type { Provider } from '@/lib/provider-types';

interface LifecycleActionDef {
  action: ProviderLifecycleAction;
  label: string;
  icon: typeof Ban;
  variant: 'destructive' | 'default';
  confirmTitle: string;
  confirmDescription: string;
  confirmLabel: string;
}

const ACTIONS: Record<ProviderLifecycleAction, Omit<LifecycleActionDef, 'action'>> = {
  deactivate: {
    label: 'Deactivate',
    icon: Ban,
    variant: 'destructive',
    confirmTitle: 'Deactivate provider?',
    confirmDescription: 'The provider will be marked inactive. The record remains available but cannot be modified as an active record.',
    confirmLabel: 'Deactivate',
  },
  reactivate: {
    label: 'Reactivate',
    icon: Power,
    variant: 'default',
    confirmTitle: 'Reactivate provider?',
    confirmDescription: 'The provider will be restored to an active status.',
    confirmLabel: 'Reactivate',
  },
  archive: {
    label: 'Archive',
    icon: Archive,
    variant: 'destructive',
    confirmTitle: 'Archive provider?',
    confirmDescription: 'The provider will be archived. Archived records are retained for compliance and can be restored later.',
    confirmLabel: 'Archive',
  },
  restore: {
    label: 'Restore',
    icon: ArchiveRestore,
    variant: 'default',
    confirmTitle: 'Restore provider?',
    confirmDescription: 'The provider will be restored from an archived state back to active.',
    confirmLabel: 'Restore',
  },
  purge: {
    label: 'Purge',
    icon: Trash2,
    variant: 'destructive',
    confirmTitle: 'Permanently purge provider?',
    confirmDescription: 'This permanently deletes the provider and all child records (identifiers, credentials, networks). This action cannot be undone and should only be used for governed data removal.',
    confirmLabel: 'Purge permanently',
  },
};

function lifecycleActionsFor(status: Provider['status']): ProviderLifecycleAction[] {
  switch (status) {
    case 'active':
      return ['deactivate', 'archive', 'purge'];
    case 'inactive':
      return ['reactivate', 'archive', 'purge'];
    case 'archived':
      return ['restore', 'purge'];
  }
}

export function ProviderLifecycleActions({
  provider,
  acting,
  actionError,
  onAction,
}: {
  provider: Provider;
  acting: boolean;
  actionError: string | null;
  onAction: (action: ProviderLifecycleAction) => void;
}) {
  const [pending, setPending] = useState<ProviderLifecycleAction | null>(null);
  const applicable = lifecycleActionsFor(provider.status);

  return (
    <div className="space-y-2">
      <div className="flex flex-wrap gap-2">
        {applicable.map((action) => {
          const def = ACTIONS[action];
          const Icon = def.icon;
          return (
            <Button
              key={action}
              variant={def.variant}
              size="sm"
              disabled={acting}
              onClick={() => setPending(action)}
            >
              <Icon className="size-4" aria-hidden />
              {def.label}
            </Button>
          );
        })}
      </div>

      {actionError && (
        <p role="alert" className="text-destructive text-xs">
          {actionError}
        </p>
      )}

      {pending && (
        <ConfirmationDialog
          open={Boolean(pending)}
          onOpenChange={(open) => !open && setPending(null)}
          title={ACTIONS[pending].confirmTitle}
          description={ACTIONS[pending].confirmDescription}
          confirmLabel={ACTIONS[pending].confirmLabel}
          variant={ACTIONS[pending].variant}
          icon={ACTIONS[pending].icon}
          onConfirm={() => {
            onAction(pending);
            setPending(null);
          }}
        />
      )}

      {acting && (
        <p className="text-muted-foreground flex items-center gap-2 text-xs">
          <Loader2 className="size-3.5 animate-spin" aria-hidden />
          Updating record…
        </p>
      )}
    </div>
  );
}
