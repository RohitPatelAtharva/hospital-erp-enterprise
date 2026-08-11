import { useState } from 'react';
import { Archive, ArchiveRestore, Ban, Loader2, Power, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ConfirmationDialog } from '@/components/feedback/confirmation-dialog';
import type { LifecycleAction } from '@/hooks/use-patient';
import type { Patient } from '@/lib/patient-types';

interface LifecycleActionDef {
  action: LifecycleAction;
  label: string;
  icon: typeof Ban;
  variant: 'destructive' | 'default';
  confirmTitle: string;
  confirmDescription: string;
  confirmLabel: string;
}

/**
 * Lifecycle action definitions, each mapped to the backend route it invokes
 * (POST /patients/{id}/{action}). Applicability by current status mirrors the
 * backend transition guards (02-Workflow §4).
 */
const ACTIONS: Record<LifecycleAction, Omit<LifecycleActionDef, 'action'>> = {
  deactivate: {
    label: 'Deactivate',
    icon: Ban,
    variant: 'destructive',
    confirmTitle: 'Deactivate patient?',
    confirmDescription: 'The patient will be marked inactive. The record remains available but cannot be modified as an active record.',
    confirmLabel: 'Deactivate',
  },
  reactivate: {
    label: 'Reactivate',
    icon: Power,
    variant: 'default',
    confirmTitle: 'Reactivate patient?',
    confirmDescription: 'The patient will be restored to an active status.',
    confirmLabel: 'Reactivate',
  },
  archive: {
    label: 'Archive',
    icon: Archive,
    variant: 'destructive',
    confirmTitle: 'Archive patient?',
    confirmDescription: 'The patient will be archived. Archived records are retained for compliance and can be restored later.',
    confirmLabel: 'Archive',
  },
  restore: {
    label: 'Restore',
    icon: ArchiveRestore,
    variant: 'default',
    confirmTitle: 'Restore patient?',
    confirmDescription: 'The patient will be restored from an archived state back to active.',
    confirmLabel: 'Restore',
  },
  purge: {
    label: 'Purge',
    icon: Trash2,
    variant: 'destructive',
    confirmTitle: 'Purge patient?',
    confirmDescription: 'This permanently deletes the patient and its child records. This action cannot be undone.',
    confirmLabel: 'Purge permanently',
  },
};

function lifecycleActionsFor(status: Patient['status']): LifecycleAction[] {
  switch (status) {
    case 'active':
      return ['deactivate', 'archive', 'purge'];
    case 'inactive':
      return ['reactivate', 'archive', 'purge'];
    case 'archived':
      return ['restore', 'purge'];
  }
}

export function PatientLifecycleActions({
  patient,
  acting,
  actionError,
  onAction,
}: {
  patient: Patient;
  acting: boolean;
  actionError: string | null;
  onAction: (action: LifecycleAction) => void;
}) {
  const [pending, setPending] = useState<LifecycleAction | null>(null);
  const applicable = lifecycleActionsFor(patient.status);

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
