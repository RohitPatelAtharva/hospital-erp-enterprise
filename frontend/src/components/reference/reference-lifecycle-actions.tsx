import { useState } from 'react';
import { Ban, Loader2, Power, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ConfirmationDialog } from '@/components/feedback/confirmation-dialog';
import type { ReferenceLifecycleAction } from '@/lib/reference-data-types';

/**
 * Lifecycle actions for reference categories and values.
 *
 * The backend exposes ONLY deactivate / reactivate / purge for reference data
 * (no archive/restore routes), so this component surfaces exactly those three
 * actions, status-aware.
 */

interface LifecycleActionDef {
  action: ReferenceLifecycleAction;
  label: string;
  icon: typeof Ban;
  variant: 'destructive' | 'default';
  confirmTitle: string;
  confirmDescription: string;
  confirmLabel: string;
}

const ACTIONS: Record<ReferenceLifecycleAction, Omit<LifecycleActionDef, 'action'>> = {
  deactivate: {
    label: 'Deactivate',
    icon: Ban,
    variant: 'destructive',
    confirmTitle: 'Deactivate?',
    confirmDescription: 'This item will be marked inactive and excluded from active reference lookups.',
    confirmLabel: 'Deactivate',
  },
  reactivate: {
    label: 'Reactivate',
    icon: Power,
    variant: 'default',
    confirmTitle: 'Reactivate?',
    confirmDescription: 'This item will be restored to an active status.',
    confirmLabel: 'Reactivate',
  },
  purge: {
    label: 'Purge',
    icon: Trash2,
    variant: 'destructive',
    confirmTitle: 'Permanently purge?',
    confirmDescription: 'This permanently deletes the item. This action cannot be undone and should only be used for governed data removal.',
    confirmLabel: 'Purge permanently',
  },
};

function lifecycleActionsFor(status: 'active' | 'inactive'): ReferenceLifecycleAction[] {
  return status === 'active' ? ['deactivate', 'purge'] : ['reactivate', 'purge'];
}

export function ReferenceLifecycleActions({
  status,
  acting,
  actionError,
  onAction,
}: {
  status: 'active' | 'inactive';
  acting: boolean;
  actionError: string | null;
  onAction: (action: ReferenceLifecycleAction) => void;
}) {
  const [pending, setPending] = useState<ReferenceLifecycleAction | null>(null);
  const applicable = lifecycleActionsFor(status);

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
          Updating…
        </p>
      )}
    </div>
  );
}
